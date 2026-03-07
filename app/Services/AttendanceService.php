<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\School;
use App\Models\StudentAcademicAssignment;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Get students eligible for attendance (assigned to the given session, class, section).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, StudentAcademicAssignment>
     */
    public function getEligibleStudents(int $academicSessionId, int $classId, int $sectionId)
    {
        return StudentAcademicAssignment::query()
            ->where('academic_session_id', $academicSessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->active()
            ->with('student')
            ->orderBy('roll_no')
            ->orderBy('student_id')
            ->get();
    }

    /**
     * Create an attendance session with records in a transaction.
     */
    public function createSessionWithRecords(array $validated): AttendanceSession
    {
        return DB::transaction(function () use ($validated) {
            $school = School::first();
            $session = AttendanceSession::create([
                'school_id' => $school?->id,
                'academic_session_id' => $validated['academic_session_id'],
                'class_id' => $validated['class_id'],
                'section_id' => $validated['section_id'],
                'attendance_date' => $validated['attendance_date'],
                'taken_by' => auth()->id(),
                'status' => $validated['status'] ?? 'final',
                'remarks' => $validated['remarks'] ?? null,
            ]);

            foreach ($validated['records'] as $row) {
                AttendanceRecord::create([
                    'school_id' => $school?->id,
                    'attendance_session_id' => $session->id,
                    'student_id' => $row['student_id'],
                    'student_academic_assignment_id' => $row['student_academic_assignment_id'] ?? null,
                    'attendance_status' => $row['attendance_status'],
                    'reason' => $row['reason'] ?? null,
                    'remarks' => $row['remarks'] ?? null,
                ]);
            }

            return $session->load(['attendanceRecords.student', 'academicSession', 'schoolClass', 'section', 'takenByUser']);
        });
    }

    /**
     * Update an attendance session's records.
     */
    public function updateSessionRecords(AttendanceSession $session, array $validated): AttendanceSession
    {
        return DB::transaction(function () use ($session, $validated) {
            if (isset($validated['status'])) {
                $session->update(['status' => $validated['status']]);
            }
            if (array_key_exists('remarks', $validated)) {
                $session->update(['remarks' => $validated['remarks']]);
            }

            $records = $validated['records'] ?? [];
            $existingByStudent = $session->attendanceRecords()->get()->keyBy('student_id');

            foreach ($records as $row) {
                $studentId = (int) $row['student_id'];
                $record = $existingByStudent->get($studentId);
                $payload = [
                    'attendance_status' => $row['attendance_status'],
                    'student_academic_assignment_id' => $row['student_academic_assignment_id'] ?? null,
                    'reason' => $row['reason'] ?? null,
                    'remarks' => $row['remarks'] ?? null,
                ];
                if ($record) {
                    $record->update($payload);
                } else {
                    AttendanceRecord::create([
                        'school_id' => $session->school_id,
                        'attendance_session_id' => $session->id,
                        'student_id' => $studentId,
                        'student_academic_assignment_id' => $payload['student_academic_assignment_id'],
                        'attendance_status' => $payload['attendance_status'],
                        'reason' => $payload['reason'],
                        'remarks' => $payload['remarks'],
                    ]);
                }
            }

            return $session->fresh(['attendanceRecords.student', 'academicSession', 'schoolClass', 'section', 'takenByUser']);
        });
    }

    /**
     * Summary counts for an attendance session.
     *
     * @return array{total: int, present: int, absent: int, late: int, leave: int}
     */
    public function summaryCounts(AttendanceSession $session): array
    {
        $records = $session->attendanceRecords;
        $total = $records->count();
        $present = $records->where('attendance_status', AttendanceRecord::STATUS_PRESENT)->count();
        $absent = $records->where('attendance_status', AttendanceRecord::STATUS_ABSENT)->count();
        $late = $records->where('attendance_status', AttendanceRecord::STATUS_LATE)->count();
        $leave = $records->where('attendance_status', AttendanceRecord::STATUS_LEAVE)->count();

        return compact('total', 'present', 'absent', 'late', 'leave');
    }
}
