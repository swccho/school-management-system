<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAttendanceRequest;
use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Models\AttendanceSession;
use App\Services\AttendanceService;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceSessionController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-attendance')) {
            abort(403, 'Unauthorized.');
        }

        $query = AttendanceSession::query()
            ->with(['academicSession', 'schoolClass', 'section', 'takenByUser', 'attendanceRecords']);

        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->input('academic_session_id'));
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->input('section_id'));
        }
        if ($request->filled('attendance_date')) {
            $query->whereDate('attendance_date', $request->input('attendance_date'));
        }

        $sessions = $query->orderByDesc('attendance_date')
            ->orderBy('class_id')
            ->orderBy('section_id')
            ->get()
            ->map(fn (AttendanceSession $s) => $this->sessionToArray($s, $this->attendanceService->summaryCounts($s)));

        return response()->json($sessions);
    }

    public function eligibleStudents(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-attendance')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
        ]);

        $assignments = $this->attendanceService->getEligibleStudents(
            (int) $request->input('academic_session_id'),
            (int) $request->input('class_id'),
            (int) $request->input('section_id')
        );

        $data = $assignments->map(function ($a) {
            $student = $a->student;
            return [
                'id' => $a->id,
                'student_id' => $a->student_id,
                'student_academic_assignment_id' => $a->id,
                'roll_no' => $a->roll_no ?? $student?->roll_no,
                'admission_no' => $student?->admission_no,
                'full_name' => $student?->full_name,
            ];
        })->values()->all();

        return response()->json($data);
    }

    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $session = $this->attendanceService->createSessionWithRecords($request->validated());
        $summary = $this->attendanceService->summaryCounts($session);

        return response()->json([
            'message' => 'Attendance saved.',
            'attendance_session' => $this->sessionToArray($session, $summary),
        ], 201);
    }

    public function show(Request $request, AttendanceSession $attendance_session): JsonResponse
    {
        if (! $request->user()->hasPermission('view-attendance')) {
            abort(403, 'Unauthorized.');
        }

        $attendance_session->load(['attendanceRecords.student', 'academicSession', 'schoolClass', 'section', 'takenByUser']);
        $summary = $this->attendanceService->summaryCounts($attendance_session);

        return response()->json($this->sessionToArray($attendance_session, $summary, true));
    }

    public function update(UpdateAttendanceRequest $request, AttendanceSession $attendance_session): JsonResponse
    {
        $session = $this->attendanceService->updateSessionRecords($attendance_session, $request->validated());
        $summary = $this->attendanceService->summaryCounts($session);

        return response()->json([
            'message' => 'Attendance updated.',
            'attendance_session' => $this->sessionToArray($session, $summary, true),
        ]);
    }

    /**
     * @param  array{total: int, present: int, absent: int, late: int, leave: int}|null  $summary
     */
    private function sessionToArray(AttendanceSession $s, ?array $summary = null, bool $withRecords = false): array
    {
        $arr = [
            'id' => $s->id,
            'academic_session_id' => $s->academic_session_id,
            'academic_session_name' => $s->academicSession?->name,
            'class_id' => $s->class_id,
            'class_name' => $s->schoolClass?->name,
            'section_id' => $s->section_id,
            'section_name' => $s->section?->name,
            'attendance_date' => $s->attendance_date->format('Y-m-d'),
            'attendance_date_formatted' => $this->dateTimeFormatter->formatDate($s->attendance_date),
            'taken_by' => $s->taken_by,
            'taken_by_name' => $s->takenByUser?->name ?? null,
            'status' => $s->status,
            'remarks' => $s->remarks,
            'created_at' => $s->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($s->created_at),
            'updated_at' => $s->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($s->updated_at),
        ];

        if ($summary !== null) {
            $arr['summary'] = $summary;
        }

        if ($withRecords && $s->relationLoaded('attendanceRecords')) {
            $arr['records'] = $s->attendanceRecords->map(fn ($r) => [
                'id' => $r->id,
                'student_id' => $r->student_id,
                'student_name' => $r->student?->full_name,
                'admission_no' => $r->student?->admission_no,
                'roll_no' => $r->studentAcademicAssignment?->roll_no ?? $r->student?->roll_no,
                'attendance_status' => $r->attendance_status,
                'reason' => $r->reason,
                'remarks' => $r->remarks,
            ])->values()->all();
        }

        return $arr;
    }
}
