<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\ClassRoutine;
use App\Models\ClassRoutineItem;
use App\Models\Notice;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class StudentDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['student']);
        $student = $user->student;
        $schoolId = $user->school_id;

        $currentSession = AcademicSession::query()
            ->where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        $currentAssignment = null;
        $studentSummary = [
            'name' => $student->full_name,
            'student_id' => $student->admission_no ?? (string) $student->id,
            'class' => null,
            'section' => null,
            'roll_no' => $student->roll_no,
        ];

        if ($currentSession) {
            $assignment = $student->studentAcademicAssignments()
                ->where('academic_session_id', $currentSession->id)
                ->active()
                ->with(['schoolClass', 'section'])
                ->first();

            if ($assignment) {
                $currentAssignment = [
                    'class_id' => $assignment->class_id,
                    'section_id' => $assignment->section_id,
                    'class_name' => $assignment->schoolClass?->name,
                    'section_name' => $assignment->section?->name,
                    'academic_session_id' => $assignment->academic_session_id,
                    'roll_no' => $assignment->roll_no ?? $student->roll_no,
                ];
                $studentSummary['class'] = $assignment->schoolClass?->name;
                $studentSummary['section'] = $assignment->section?->name;
                $studentSummary['roll_no'] = $assignment->roll_no ?? $student->roll_no;
            }
        }

        $todayDay = strtolower(Carbon::now()->format('l'));
        $todayRoutine = [];

        if ($currentAssignment) {
            $routine = ClassRoutine::query()
                ->where('academic_session_id', $currentSession->id)
                ->where('class_id', $currentAssignment['class_id'])
                ->where('section_id', $currentAssignment['section_id'])
                ->active()
                ->first();

            if ($routine) {
                $todayRoutine = ClassRoutineItem::query()
                    ->where('class_routine_id', $routine->id)
                    ->where('day_of_week', $todayDay)
                    ->with(['subject', 'teacher.staff.user'])
                    ->orderBy('period_no')
                    ->get()
                    ->map(fn ($item) => [
                        'subject' => $item->subject?->name,
                        'teacher_name' => $item->teacher?->staff?->user?->name ?? '',
                        'start_time' => $item->start_time,
                        'end_time' => $item->end_time,
                        'room_label' => $item->room_label ?? '',
                        'period_no' => $item->period_no,
                    ])
                    ->toArray();
            }
        }

        $recentAnnouncements = Notice::query()
            ->where('school_id', $schoolId)
            ->published()
            ->orderByDesc('publish_date')
            ->limit(8)
            ->get(['id', 'title', 'content', 'publish_date'])
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'excerpt' => $n->content ? Str::limit(strip_tags($n->content), 120) : null,
                'publish_date' => $n->publish_date?->format('Y-m-d'),
            ])
            ->toArray();

        $school = School::find($schoolId);
        $schoolName = $school?->name ?? '';

        return response()->json([
            'student_summary' => $studentSummary,
            'today_routine' => $todayRoutine,
            'recent_announcements' => $recentAnnouncements,
            'school_name' => $schoolName,
            'welcome' => 'Welcome back, ' . $student->first_name . '!',
        ]);
    }
}
