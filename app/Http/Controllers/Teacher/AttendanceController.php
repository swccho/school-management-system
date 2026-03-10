<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\StudentAcademicAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;
        $schoolId = $user->school_id;

        $currentSession = AcademicSession::where('school_id', $schoolId)->where('is_current', true)->first();
        if (! $currentSession) {
            return response()->json([]);
        }

        $pairIds = $teacher->subjectAssignments()
            ->where('academic_session_id', $currentSession->id)
            ->active()
            ->get()
            ->map(fn ($a) => ['class_id' => $a->class_id, 'section_id' => $a->section_id])
            ->unique(fn ($a) => $a['class_id'].'_'.$a['section_id'])
            ->values();

        $classIds = collect($pairIds)->pluck('class_id')->unique()->values();
        $sectionIds = collect($pairIds)->pluck('section_id')->unique()->values();

        $query = AttendanceSession::query()
            ->where('academic_session_id', $currentSession->id)
            ->whereIn('class_id', $classIds)
            ->whereIn('section_id', $sectionIds)
            ->with(['schoolClass', 'section'])
            ->orderByDesc('attendance_date');

        if ($request->filled('date_from')) {
            $query->whereDate('attendance_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('attendance_date', '<=', $request->input('date_to'));
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $sessions = $query->limit(100)->get()->map(fn ($s) => [
            'id' => $s->id,
            'attendance_date' => $s->attendance_date->format('Y-m-d'),
            'class_id' => $s->class_id,
            'class_name' => $s->schoolClass?->name,
            'section_id' => $s->section_id,
            'section_name' => $s->section?->name,
            'status' => $s->status,
        ]);

        return response()->json($sessions);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => ['required', 'exists:school_classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'attendance_date' => ['required', 'date'],
        ]);

        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;
        $schoolId = $user->school_id;

        $currentSession = AcademicSession::where('school_id', $schoolId)->where('is_current', true)->first();
        if (! $currentSession) {
            return response()->json(['message' => 'No current academic session.'], 422);
        }

        $allowed = $teacher->subjectAssignments()
            ->where('academic_session_id', $currentSession->id)
            ->active()
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->exists();

        if (! $allowed) {
            return response()->json(['message' => 'You are not assigned to this class/section.'], 403);
        }

        $exists = AttendanceSession::query()
            ->where('academic_session_id', $currentSession->id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Attendance for this date already exists.'], 422);
        }

        $session = AttendanceSession::create([
            'school_id' => $schoolId,
            'academic_session_id' => $currentSession->id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'attendance_date' => $request->attendance_date,
            'taken_by' => $user->id,
            'status' => 'draft',
        ]);

        $students = StudentAcademicAssignment::query()
            ->where('academic_session_id', $currentSession->id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->active()
            ->with('student')
            ->orderBy('roll_no')
            ->get();

        foreach ($students as $ass) {
            AttendanceRecord::create([
                'school_id' => $schoolId,
                'attendance_session_id' => $session->id,
                'student_id' => $ass->student_id,
                'student_academic_assignment_id' => $ass->id,
                'attendance_status' => 'present',
            ]);
        }

        $session->load(['schoolClass', 'section']);
        return response()->json([
            'message' => 'Attendance session created.',
            'session' => [
                'id' => $session->id,
                'attendance_date' => $session->attendance_date->format('Y-m-d'),
                'class_name' => $session->schoolClass?->name,
                'section_name' => $session->section?->name,
                'status' => $session->status,
            ],
        ], 201);
    }

    public function show(Request $request, AttendanceSession $attendance_session): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;

        $allowed = $teacher->subjectAssignments()
            ->active()
            ->where('class_id', $attendance_session->class_id)
            ->where('section_id', $attendance_session->section_id)
            ->exists();

        if (! $allowed) {
            abort(403, 'Not allowed to view this attendance session.');
        }

        $attendance_session->load(['schoolClass', 'section', 'attendanceRecords.student', 'attendanceRecords.studentAcademicAssignment']);
        $records = $attendance_session->attendanceRecords->map(fn ($r) => [
            'id' => $r->id,
            'student_id' => $r->student_id,
            'student_name' => $r->student?->full_name,
            'roll_no' => $r->studentAcademicAssignment?->roll_no,
            'attendance_status' => $r->attendance_status,
        ]);

        return response()->json([
            'id' => $attendance_session->id,
            'attendance_date' => $attendance_session->attendance_date->format('Y-m-d'),
            'class_name' => $attendance_session->schoolClass?->name,
            'section_name' => $attendance_session->section?->name,
            'status' => $attendance_session->status,
            'records' => $records,
        ]);
    }

    public function update(Request $request, AttendanceSession $attendance_session): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;

        $allowed = $teacher->subjectAssignments()
            ->active()
            ->where('class_id', $attendance_session->class_id)
            ->where('section_id', $attendance_session->section_id)
            ->exists();

        if (! $allowed) {
            abort(403, 'Not allowed to update this attendance session.');
        }

        if ($attendance_session->status === 'final') {
            return response()->json(['message' => 'Cannot edit submitted attendance.'], 422);
        }

        $request->validate(['records' => ['required', 'array'], 'records.*.student_id' => ['required'], 'records.*.attendance_status' => ['required', 'in:present,absent,late,leave']]);

        foreach ($request->input('records') as $row) {
            AttendanceRecord::query()
                ->where('attendance_session_id', $attendance_session->id)
                ->where('student_id', $row['student_id'])
                ->update(['attendance_status' => $row['attendance_status']]);
        }

        return response()->json(['message' => 'Attendance updated.']);
    }

    public function submit(Request $request, AttendanceSession $attendance_session): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;

        $allowed = $teacher->subjectAssignments()
            ->active()
            ->where('class_id', $attendance_session->class_id)
            ->where('section_id', $attendance_session->section_id)
            ->exists();

        if (! $allowed) {
            abort(403, 'Not allowed to submit this attendance session.');
        }

        $attendance_session->update(['status' => 'final']);
        return response()->json(['message' => 'Attendance submitted.']);
    }
}
