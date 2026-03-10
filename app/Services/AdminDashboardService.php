<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\ActivityLog;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Exam;
use App\Models\Notice;
use App\Models\ResultSummary;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function getDashboardData(): array
    {
        $today = Carbon::today();

        return [
            'summary_cards' => $this->getSummaryCards(),
            'academic_overview' => $this->getAcademicOverview(),
            'attendance_overview' => $this->getAttendanceOverview($today),
            'exam_overview' => $this->getExamOverview(),
            'recent_activity' => $this->getRecentActivity(),
        ];
    }

    private function getSummaryCards(): array
    {
        return [
            'total_students' => Student::query()->count(),
            'total_teachers' => Teacher::query()->count(),
            'total_staff' => Staff::query()->count(),
            'total_classes' => SchoolClass::query()->count(),
            'total_exams' => Exam::query()->count(),
            'published_notices_count' => Notice::query()->where('status', 'published')->count(),
        ];
    }

    private function getAcademicOverview(): array
    {
        $currentSession = AcademicSession::query()
            ->where('is_current', true)
            ->first();

        $currentSessionData = null;
        if ($currentSession) {
            $currentSessionData = [
                'id' => $currentSession->id,
                'name' => $currentSession->name,
                'code' => $currentSession->code,
                'start_date' => $currentSession->start_date?->toDateString(),
                'end_date' => $currentSession->end_date?->toDateString(),
            ];
        }

        return [
            'current_session' => $currentSessionData,
            'total_sections' => Section::query()->count(),
            'total_subjects' => Subject::query()->count(),
            'teacher_assignments_count' => TeacherSubjectAssignment::query()->count(),
        ];
    }

    private function getAttendanceOverview(Carbon $date): array
    {
        $sessionIds = AttendanceSession::query()
            ->forDate($date)
            ->pluck('id');

        if ($sessionIds->isEmpty()) {
            return [
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'leave' => 0,
                'total_marked' => 0,
            ];
        }

        $counts = AttendanceRecord::query()
            ->whereIn('attendance_session_id', $sessionIds)
            ->select('attendance_status', DB::raw('count(*) as cnt'))
            ->groupBy('attendance_status')
            ->pluck('cnt', 'attendance_status')
            ->all();

        return [
            'present' => (int) ($counts[AttendanceRecord::STATUS_PRESENT] ?? 0),
            'absent' => (int) ($counts[AttendanceRecord::STATUS_ABSENT] ?? 0),
            'late' => (int) ($counts[AttendanceRecord::STATUS_LATE] ?? 0),
            'leave' => (int) ($counts[AttendanceRecord::STATUS_LEAVE] ?? 0),
            'total_marked' => (int) array_sum($counts),
        ];
    }

    private function getExamOverview(): array
    {
        $latestExam = Exam::query()
            ->orderByDesc('created_at')
            ->first();

        $latestExamData = null;
        if ($latestExam) {
            $latestExamData = [
                'id' => $latestExam->id,
                'name' => $latestExam->name,
                'created_at' => $latestExam->created_at->toIso8601String(),
            ];
        }

        return [
            'active_exams_count' => Exam::query()->where('status', 'active')->count(),
            'latest_exam' => $latestExamData,
            'results_generated_count' => ResultSummary::query()->count(),
        ];
    }

    private function getRecentActivity(): array
    {
        $latestStudents = Student::query()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'label' => $s->full_name,
                'created_at' => $s->created_at->toIso8601String(),
            ])
            ->values()
            ->all();

        $latestNotices = Notice::query()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'label' => $n->title,
                'created_at' => $n->created_at->toIso8601String(),
            ])
            ->values()
            ->all();

        $latestAttendanceSessions = AttendanceSession::query()
            ->with('academicSession', 'schoolClass', 'section')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'label' => ($s->schoolClass?->name ?? '') . ' - ' . ($s->section?->name ?? '') . ' (' . $s->attendance_date?->toDateString() . ')',
                'created_at' => $s->created_at->toIso8601String(),
            ])
            ->values()
            ->all();

        $latestExams = Exam::query()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'label' => $e->name,
                'created_at' => $e->created_at->toIso8601String(),
            ])
            ->values()
            ->all();

        $latestActivityLogs = ActivityLog::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'label' => $l->description ?? ($l->module . ' - ' . $l->action),
                'created_at' => $l->created_at->toIso8601String(),
                'user_name' => $l->user?->name,
            ])
            ->values()
            ->all();

        return [
            'latest_students' => $latestStudents,
            'latest_notices' => $latestNotices,
            'latest_attendance_sessions' => $latestAttendanceSessions,
            'latest_exams' => $latestExams,
            'latest_activity_logs' => $latestActivityLogs,
        ];
    }
}
