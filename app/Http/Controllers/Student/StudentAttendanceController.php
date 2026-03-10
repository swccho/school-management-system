<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentAttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['student']);
        $student = $user->student;

        $currentSession = AcademicSession::query()
            ->where('school_id', $user->school_id)
            ->where('is_current', true)
            ->first();

        if (! $currentSession) {
            return response()->json([
                'summary' => ['present' => 0, 'absent' => 0, 'late' => 0, 'leave' => 0, 'total' => 0, 'percentage' => null],
                'records' => [],
            ]);
        }

        $assignment = $student->studentAcademicAssignments()
            ->where('academic_session_id', $currentSession->id)
            ->active()
            ->first();

        if (! $assignment) {
            return response()->json([
                'summary' => ['present' => 0, 'absent' => 0, 'late' => 0, 'leave' => 0, 'total' => 0, 'percentage' => null],
                'records' => [],
            ]);
        }

        $baseQuery = AttendanceRecord::query()
            ->where('student_id', $student->id)
            ->where('student_academic_assignment_id', $assignment->id);

        if ($request->filled('month') && $request->filled('year')) {
            $start = Carbon::createFromDate((int) $request->year, (int) $request->month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $baseQuery->whereHas('attendanceSession', fn ($q) => $q->whereBetween('attendance_date', [$start, $end]));
        }

        $present = (clone $baseQuery)->where('attendance_status', 'present')->count();
        $absent = (clone $baseQuery)->where('attendance_status', 'absent')->count();
        $late = (clone $baseQuery)->where(function ($q) {
            $q->where('attendance_status', 'late')->orWhere('is_late', true);
        })->count();
        $leave = (clone $baseQuery)->where('attendance_status', 'leave')->count();
        $total = (clone $baseQuery)->count();
        $percentage = $total > 0 ? round(($present + $late) / $total * 100, 1) : null;

        $perPage = min((int) $request->input('per_page', 30), 100);
        $recordsPaginated = (clone $baseQuery)->with('attendanceSession')->orderByDesc('id')->paginate($perPage);
        $records = $recordsPaginated->getCollection();

        $recordsData = $records->map(fn ($r) => [
            'id' => $r->id,
            'attendance_date' => $r->attendanceSession?->attendance_date?->format('Y-m-d'),
            'attendance_status' => $r->attendance_status,
            'is_late' => $r->is_late,
            'in_time' => $r->in_time,
            'out_time' => $r->out_time,
        ])->values()->toArray();

        $response = [
            'summary' => [
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'leave' => $leave,
                'total' => $total,
                'percentage' => $percentage,
            ],
            'records' => $recordsData,
        ];

        $response['meta'] = [
            'current_page' => $recordsPaginated->currentPage(),
            'last_page' => $recordsPaginated->lastPage(),
            'per_page' => $recordsPaginated->perPage(),
            'total' => $recordsPaginated->total(),
        ];

        return response()->json($response);
    }
}
