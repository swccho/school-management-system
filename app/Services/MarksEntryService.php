<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamSubjectConfig;
use App\Models\MarkEntry;
use App\Models\MarkEntryItem;
use App\Models\School;
use App\Models\StudentAcademicAssignment;
use Illuminate\Support\Facades\DB;

class MarksEntryService
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    /**
     * Get eligible student IDs for marks entry (assigned to exam's session + class + section).
     *
     * @return array<int, array{student_id: int, student_academic_assignment_id: int, roll_no: string|null, admission_no: string|null, full_name: string}>
     */
    public function getEligibleStudents(int $examId, int $classId, ?int $sectionId): array
    {
        $exam = Exam::find($examId);
        if (! $exam) {
            return [];
        }
        $query = StudentAcademicAssignment::query()
            ->where('academic_session_id', $exam->academic_session_id)
            ->where('class_id', $classId)
            ->active()
            ->with('student');
        if ($sectionId !== null) {
            $query->where('section_id', $sectionId);
        } else {
            $query->whereNull('section_id');
        }
        return $query->orderBy('roll_no')->orderBy('student_id')->get()->map(function ($a) {
            $s = $a->student;
            return [
                'student_id' => $a->student_id,
                'student_academic_assignment_id' => $a->id,
                'roll_no' => $a->roll_no ?? $s?->roll_no,
                'admission_no' => $s?->admission_no,
                'full_name' => $s?->full_name,
            ];
        })->values()->all();
    }

    /**
     * Get subjects configured for exam + class (for selector).
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function getSubjectsForExamClass(int $examId, int $classId): array
    {
        return ExamSubjectConfig::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->with('subject')
            ->get()
            ->map(fn ($c) => ['id' => $c->subject_id, 'name' => $c->subject?->name])
            ->values()
            ->all();
    }

    /**
     * Get subject config and mark components for exam/class/subject.
     */
    public function getSubjectConfig(int $examId, int $classId, int $subjectId): ?array
    {
        $config = ExamSubjectConfig::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->with(['subject', 'markComponents' => fn ($q) => $q->orderBy('sort_order')])
            ->first();
        if (! $config) {
            return null;
        }
        $components = $config->markComponents->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'component_type' => $c->component_type,
            'marks' => $c->marks,
            'pass_marks' => $c->pass_marks,
        ])->values()->all();
        return [
            'id' => $config->id,
            'full_marks' => $config->full_marks,
            'pass_marks' => $config->pass_marks,
            'subject_name' => $config->subject?->name,
            'mark_components' => $components,
        ];
    }

    /**
     * Save marks entries (create or update) in a transaction.
     */
    public function saveMarks(array $validated): void
    {
        DB::transaction(function () use ($validated) {
            $school = School::first();
            $examId = (int) $validated['exam_id'];
            $classId = (int) $validated['class_id'];
            $sectionId = isset($validated['section_id']) && $validated['section_id'] !== '' ? (int) $validated['section_id'] : null;
            $subjectId = (int) $validated['subject_id'];
            $status = $validated['status'] ?? 'draft';
            $userId = auth()->id();

            foreach ($validated['entries'] as $entry) {
                $studentId = (int) $entry['student_id'];
                $markEntry = MarkEntry::firstOrNew([
                    'exam_id' => $examId,
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                ]);
                $markEntry->fill([
                    'school_id' => $school?->id,
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'entered_by' => $userId,
                    'status' => $status,
                ]);
                $markEntry->save();

                $markEntry->markEntryItems()->delete();
                foreach ($entry['items'] as $item) {
                    $componentId = isset($item['mark_component_id']) && $item['mark_component_id'] !== '' ? (int) $item['mark_component_id'] : null;
                    $obtained = (int) ($item['obtained_marks'] ?? 0);
                    MarkEntryItem::create([
                        'school_id' => $school?->id,
                        'mark_entry_id' => $markEntry->id,
                        'mark_component_id' => $componentId,
                        'obtained_marks' => $obtained,
                    ]);
                }
            }
        });
    }

    /**
     * List mark entries grouped by exam/class/section/subject for history view.
     *
     * @return array<int, array{exam_id: int, exam_name: string, class_id: int, class_name: string, section_id: int|null, section_name: string|null, subject_id: int, subject_name: string, students_count: int, status: string, updated_at: string, updated_at_formatted: string|null}>
     */
    public function listGrouped(?int $examId, ?int $classId, ?int $sectionId, ?int $subjectId, ?string $status): array
    {
        $query = MarkEntry::query()->with(['exam', 'schoolClass', 'section', 'subject']);
        if ($examId) {
            $query->where('exam_id', $examId);
        }
        if ($classId) {
            $query->where('class_id', $classId);
        }
        if ($sectionId !== null) {
            $query->where('section_id', $sectionId);
        }
        if ($subjectId !== null) {
            $query->where('subject_id', $subjectId);
        }
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }
        $entries = $query->get();
        $grouped = $entries->groupBy(fn ($e) => $e->exam_id . '-' . $e->class_id . '-' . ($e->section_id ?? 'null') . '-' . $e->subject_id);

        return $grouped->map(function ($group) {
            $first = $group->first();
            $updatedAt = $group->max('updated_at');
            return [
                'exam_id' => $first->exam_id,
                'exam_name' => $first->exam?->name,
                'class_id' => $first->class_id,
                'class_name' => $first->schoolClass?->name,
                'section_id' => $first->section_id,
                'section_name' => $first->section?->name,
                'subject_id' => $first->subject_id,
                'subject_name' => $first->subject?->name,
                'students_count' => $group->count(),
                'status' => $first->status,
                'updated_at' => $updatedAt?->toIso8601String(),
                'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($updatedAt),
            ];
        })->values()->all();
    }

    /**
     * Get mark entries for a given context (exam, class, section, subject) for detail/review.
     *
     * @return array<int, array{id: int, student_id: int, student_name: string, admission_no: string|null, roll_no: string|null, status: string, items: array}>
     */
    public function getEntriesForContext(int $examId, int $classId, ?int $sectionId, int $subjectId): array
    {
        $query = MarkEntry::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->with(['student', 'markEntryItems.markComponent']);
        if ($sectionId !== null) {
            $query->where('section_id', $sectionId);
        } else {
            $query->whereNull('section_id');
        }
        return $query->get()->map(function ($e) {
            $total = $e->markEntryItems->sum('obtained_marks');
            return [
                'id' => $e->id,
                'student_id' => $e->student_id,
                'student_name' => $e->student?->full_name,
                'admission_no' => $e->student?->admission_no,
                'roll_no' => $e->student?->roll_no,
                'status' => $e->status,
                'total_obtained' => $total,
                'items' => $e->markEntryItems->map(fn ($i) => [
                    'id' => $i->id,
                    'mark_component_id' => $i->mark_component_id,
                    'component_name' => $i->markComponent?->name,
                    'obtained_marks' => $i->obtained_marks,
                ])->values()->all(),
            ];
        })->values()->all();
    }
}
