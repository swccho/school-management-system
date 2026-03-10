<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Homework;
use App\Models\MarkEntry;
use App\Models\MarkEntryItem;
use App\Models\Student;
use App\Models\StudentAcademicAssignment;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeacherAnalyticsService
{
    public function __construct(
        protected Teacher $teacher,
        protected ?int $academicSessionId,
        protected ?int $classId = null,
        protected ?int $sectionId = null,
        protected ?int $subjectId = null,
        protected ?string $dateFrom = null,
        protected ?string $dateTo = null
    ) {}

    protected function assignmentPairs(): Collection
    {
        $query = $this->teacher->subjectAssignments()
            ->where('academic_session_id', $this->academicSessionId)
            ->active();
        if ($this->classId) {
            $query->where('class_id', $this->classId);
        }
        if ($this->sectionId) {
            $query->where('section_id', $this->sectionId);
        }
        if ($this->subjectId) {
            $query->where('subject_id', $this->subjectId);
        }
        return $query->get()->map(fn ($a) => ['class_id' => $a->class_id, 'section_id' => $a->section_id])->unique(fn ($a) => $a['class_id'].'_'.$a['section_id'])->values();
    }

    public function attendanceTrends(): array
    {
        if (! $this->academicSessionId) {
            return ['labels' => [], 'present' => [], 'absent' => [], 'late' => []];
        }
        $pairs = $this->assignmentPairs();
        if ($pairs->isEmpty()) {
            return ['labels' => [], 'present' => [], 'absent' => [], 'late' => []];
        }
        $classIds = $pairs->pluck('class_id')->unique();
        $sectionIds = $pairs->pluck('section_id')->unique();
        $from = $this->dateFrom ? Carbon::parse($this->dateFrom) : Carbon::today()->subWeeks(4);
        $to = $this->dateTo ? Carbon::parse($this->dateTo) : Carbon::today();

        $query = AttendanceSession::query()
            ->where('academic_session_id', $this->academicSessionId)
            ->whereIn('class_id', $classIds)
            ->whereIn('section_id', $sectionIds)
            ->whereBetween('attendance_date', [$from, $to]);
        $sessions = $query->get();
        $sessionIds = $sessions->pluck('id');

        $records = AttendanceRecord::query()->whereIn('attendance_session_id', $sessionIds)->get();
        $byWeek = [];
        foreach ($sessions as $s) {
            $weekKey = Carbon::parse($s->attendance_date)->format('Y-W');
            if (! isset($byWeek[$weekKey])) {
                $byWeek[$weekKey] = ['present' => 0, 'absent' => 0, 'late' => 0];
            }
            $sessionRecords = $records->where('attendance_session_id', $s->id);
            foreach ($sessionRecords as $r) {
                if ($r->attendance_status === 'present') {
                    $byWeek[$weekKey]['present']++;
                } elseif ($r->attendance_status === 'absent') {
                    $byWeek[$weekKey]['absent']++;
                } elseif ($r->attendance_status === 'late') {
                    $byWeek[$weekKey]['late']++;
                }
            }
        }
        ksort($byWeek);
        $labels = array_keys($byWeek);
        return [
            'labels' => $labels,
            'present' => array_map(fn ($k) => $byWeek[$k]['present'], $labels),
            'absent' => array_map(fn ($k) => $byWeek[$k]['absent'], $labels),
            'late' => array_map(fn ($k) => $byWeek[$k]['late'], $labels),
        ];
    }

    public function homeworkTrends(): array
    {
        $query = Homework::query()
            ->where('teacher_id', $this->teacher->id);
        if ($this->academicSessionId) {
            $query->where('academic_session_id', $this->academicSessionId);
        }
        if ($this->classId) {
            $query->where('class_id', $this->classId);
        }
        if ($this->sectionId) {
            $query->where('section_id', $this->sectionId);
        }
        if ($this->subjectId) {
            $query->where('subject_id', $this->subjectId);
        }
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
        $items = $query->get();
        $today = Carbon::today()->toDateString();
        $byWeek = [];
        foreach ($items as $h) {
            $weekKey = Carbon::parse($h->created_at)->format('Y-W');
            if (! isset($byWeek[$weekKey])) {
                $byWeek[$weekKey] = ['count' => 0, 'overdue' => 0];
            }
            $byWeek[$weekKey]['count']++;
            if ($h->due_date && $h->due_date->toDateString() < $today) {
                $byWeek[$weekKey]['overdue']++;
            }
        }
        ksort($byWeek);
        $labels = array_keys($byWeek);
        return [
            'labels' => $labels,
            'count' => array_map(fn ($k) => $byWeek[$k]['count'], $labels),
            'overdue' => array_map(fn ($k) => $byWeek[$k]['overdue'], $labels),
        ];
    }

    public function marksTrends(): array
    {
        $subjectIds = $this->teacher->subjectAssignments()
            ->where('academic_session_id', $this->academicSessionId)
            ->active()
            ->when($this->subjectId, fn ($q) => $q->where('subject_id', $this->subjectId))
            ->pluck('subject_id')
            ->unique();
        if ($subjectIds->isEmpty()) {
            return ['labels' => [], 'averages' => []];
        }
        $query = MarkEntry::query()
            ->whereIn('subject_id', $subjectIds)
            ->where('status', 'submitted');
        if ($this->classId) {
            $query->where('class_id', $this->classId);
        }
        if ($this->sectionId) {
            $query->where('section_id', $this->sectionId);
        }
        $entries = $query->with('markEntryItems')->get();
        $byExam = [];
        foreach ($entries as $e) {
            $examKey = $e->exam_id;
            if (! isset($byExam[$examKey])) {
                $byExam[$examKey] = ['total' => 0, 'count' => 0];
            }
            $obtained = $e->markEntryItems->sum('obtained_marks');
            $byExam[$examKey]['total'] += $obtained;
            $byExam[$examKey]['count']++;
        }
        $labels = array_keys($byExam);
        $averages = array_map(fn ($k) => $byExam[$k]['count'] > 0 ? round($byExam[$k]['total'] / $byExam[$k]['count'], 1) : 0, $labels);
        return ['labels' => $labels, 'averages' => $averages];
    }

    public function studentsNeedingAttention(float $attendanceThreshold = 80.0, float $marksThreshold = 40.0): array
    {
        $pairs = $this->assignmentPairs();
        if ($pairs->isEmpty() || ! $this->academicSessionId) {
            return [];
        }
        $classIds = $pairs->pluck('class_id')->unique();
        $sectionIds = $pairs->pluck('section_id')->unique();
        $from = $this->dateFrom ? Carbon::parse($this->dateFrom) : Carbon::today()->subDays(30);
        $to = $this->dateTo ? Carbon::parse($this->dateTo) : Carbon::today();

        $sessions = AttendanceSession::query()
            ->where('academic_session_id', $this->academicSessionId)
            ->whereIn('class_id', $classIds)
            ->whereIn('section_id', $sectionIds)
            ->whereBetween('attendance_date', [$from, $to])
            ->get();
        $sessionIds = $sessions->pluck('id');
        $records = AttendanceRecord::query()->whereIn('attendance_session_id', $sessionIds)->get();
        $studentTotal = [];
        $studentPresent = [];
        foreach ($records as $r) {
            $sid = $r->student_id;
            $studentTotal[$sid] = ($studentTotal[$sid] ?? 0) + 1;
            if ($r->attendance_status === 'present' || $r->attendance_status === 'late') {
                $studentPresent[$sid] = ($studentPresent[$sid] ?? 0) + 1;
            }
        }
        $lowAttendance = [];
        foreach ($studentTotal as $studentId => $total) {
            $present = $studentPresent[$studentId] ?? 0;
            $rate = $total > 0 ? ($present / $total) * 100 : 0;
            if ($rate < $attendanceThreshold) {
                $lowAttendance[] = ['student_id' => $studentId, 'attendance_rate' => round($rate, 1), 'metric' => 'attendance'];
            }
        }
        $studentIds = array_unique(array_column($lowAttendance, 'student_id'));
        $students = Student::query()->whereIn('id', $studentIds)->with(['schoolClass', 'section'])->get()->keyBy('id');
        $result = [];
        foreach ($lowAttendance as $row) {
            $s = $students->get($row['student_id']);
            $result[] = [
                'student_id' => $row['student_id'],
                'student_name' => $s?->name ?? 'Unknown',
                'class_name' => $s?->schoolClass?->name,
                'section_name' => $s?->section?->name,
                'metric' => 'attendance',
                'value' => $row['attendance_rate'],
            ];
        }
        return array_slice($result, 0, 50);
    }
}
