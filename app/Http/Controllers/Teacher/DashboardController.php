<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AttendanceSession;
use App\Models\ClassRoutineItem;
use App\Models\Event;
use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;
        $schoolId = $user->school_id;

        $academicSessionId = $request->input('academic_session_id');
        $currentSession = $academicSessionId
            ? AcademicSession::where('school_id', $schoolId)->find($academicSessionId)
            : AcademicSession::where('school_id', $schoolId)->where('is_current', true)->first();

        $todayDay = strtolower(Carbon::now()->format('l'));

        $todaysClasses = ClassRoutineItem::query()
            ->where('teacher_id', $teacher->id)
            ->where('day_of_week', $todayDay)
            ->with(['subject', 'classRoutine.schoolClass', 'classRoutine.section'])
            ->orderBy('period_no')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'subject_name' => $item->subject?->name,
                'class_name' => $item->classRoutine?->schoolClass?->name,
                'section_name' => $item->classRoutine?->section?->name,
                'room_label' => $item->room_label,
                'period_no' => $item->period_no,
                'start_time' => $item->start_time,
                'end_time' => $item->end_time,
            ]);

        $assignedSubjects = $currentSession
            ? $teacher->subjectAssignments()
                ->where('academic_session_id', $currentSession->id)
                ->active()
                ->with('subject')
                ->get()
                ->pluck('subject.name')
                ->unique()
                ->values()
                ->toArray()
            : [];

        $assignedClassSectionPairs = $currentSession
            ? $teacher->subjectAssignments()
                ->where('academic_session_id', $currentSession->id)
                ->active()
                ->get()
                ->map(fn ($a) => ['class_id' => $a->class_id, 'section_id' => $a->section_id])
                ->unique(fn ($a) => $a['class_id'].'_'.$a['section_id'])
                ->values()
                ->toArray()
            : [];

        $today = Carbon::today()->toDateString();
        $pendingAttendance = [];
        if ($currentSession) {
            foreach ($assignedClassSectionPairs as $pair) {
                $session = AttendanceSession::query()
                    ->where('academic_session_id', $currentSession->id)
                    ->where('class_id', $pair['class_id'])
                    ->where('section_id', $pair['section_id'])
                    ->where('attendance_date', $today)
                    ->first();
                $pendingAttendance[] = [
                    'class_id' => $pair['class_id'],
                    'section_id' => $pair['section_id'],
                    'session_id' => $session?->id,
                    'status' => $session?->status ?? 'not_taken',
                ];
            }
        }

        $pendingMarksCount = 0;
        if ($currentSession) {
            $teacherSubjectIds = $teacher->subjectAssignments()
                ->where('academic_session_id', $currentSession->id)
                ->active()
                ->pluck('subject_id');
            $pendingMarksCount = \App\Models\MarkEntry::query()
                ->whereHas('exam', fn ($q) => $q->where('academic_session_id', $currentSession->id))
                ->whereIn('subject_id', $teacherSubjectIds)
                ->where('status', 'draft')
                ->where('entered_by', $user->id)
                ->distinct('exam_id')
                ->count('exam_id');
        }

        $recentNotices = Notice::query()
            ->where('school_id', $schoolId)
            ->published()
            ->orderByDesc('publish_date')
            ->limit(5)
            ->get(['id', 'title', 'publish_date', 'slug'])
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'publish_date' => $n->publish_date?->format('Y-m-d'),
                'slug' => $n->slug,
            ]);

        $upcomingEvents = Event::query()
            ->where('school_id', $schoolId)
            ->where('status', 'published')
            ->where('start_datetime', '>=', now())
            ->orderBy('start_datetime')
            ->limit(5)
            ->get(['id', 'title', 'start_datetime', 'location'])
            ->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'start_datetime' => $e->start_datetime?->toIso8601String(),
                'location' => $e->location,
            ]);

        return response()->json([
            'todays_classes' => $todaysClasses,
            'assigned_subjects' => $assignedSubjects,
            'pending_attendance' => $pendingAttendance,
            'pending_marks_count' => $pendingMarksCount,
            'recent_notices' => $recentNotices,
            'upcoming_events' => $upcomingEvents,
            'current_session' => $currentSession ? [
                'id' => $currentSession->id,
                'name' => $currentSession->name,
                'code' => $currentSession->code,
            ] : null,
        ]);
    }
}
