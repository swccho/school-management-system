<?php

namespace App\Http\Requests\Admin;

use App\Models\ExamSubjectConfig;
use App\Models\StudentAcademicAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMarksEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-marks-entry') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'submitted'])],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'entries.*.items' => ['required', 'array', 'min:1'],
            'entries.*.items.*.mark_component_id' => ['nullable', 'integer', 'exists:mark_components,id'],
            'entries.*.items.*.obtained_marks' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $examId = (int) $this->input('exam_id');
            $classId = (int) $this->input('class_id');
            $sectionId = $this->input('section_id');
            $subjectId = (int) $this->input('subject_id');

            $exam = \App\Models\Exam::find($examId);
            if (! $exam) {
                return;
            }

            if ($sectionId !== null && $sectionId !== '') {
                $sectionBelongsToClass = \App\Models\Section::where('id', $sectionId)->where('class_id', $classId)->exists();
                if (! $sectionBelongsToClass) {
                    $validator->errors()->add('section_id', 'Section does not belong to the selected class.');
                    return;
                }
            }

            $subjectConfig = ExamSubjectConfig::where('exam_id', $examId)
                ->where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->with('markComponents')
                ->first();
            if (! $subjectConfig) {
                $validator->errors()->add('subject_id', 'Subject is not configured for this exam and class.');
                return;
            }

            $sessionId = $exam->academic_session_id;
            $eligibleQuery = StudentAcademicAssignment::where('academic_session_id', $sessionId)
                ->where('class_id', $classId)
                ->active();
            if ($sectionId !== null && $sectionId !== '') {
                $eligibleQuery->where('section_id', $sectionId);
            } else {
                $eligibleQuery->whereNull('section_id');
            }
            $eligibleStudentIds = $eligibleQuery->pluck('student_id')->all();

            $componentMax = [];
            foreach ($subjectConfig->markComponents as $c) {
                $componentMax[$c->id] = $c->marks;
            }
            $fullMarks = $subjectConfig->full_marks;

            foreach ($this->input('entries', []) as $i => $entry) {
                $studentId = (int) ($entry['student_id'] ?? 0);
                if (! in_array($studentId, $eligibleStudentIds, true)) {
                    $validator->errors()->add("entries.{$i}.student_id", 'Student is not in the selected class/section for this exam.');
                    continue;
                }
                $totalObtained = 0;
                foreach ($entry['items'] ?? [] as $j => $item) {
                    $compId = $item['mark_component_id'] ?? null;
                    $obtained = (int) ($item['obtained_marks'] ?? 0);
                    $totalObtained += $obtained;
                    if ($compId) {
                        $max = $componentMax[$compId] ?? null;
                        if ($max !== null && $obtained > $max) {
                            $validator->errors()->add("entries.{$i}.items.{$j}.obtained_marks", "Obtained marks cannot exceed {$max} for this component.");
                        }
                    } else {
                        if ($obtained > $fullMarks) {
                            $validator->errors()->add("entries.{$i}.items.{$j}.obtained_marks", "Obtained marks cannot exceed {$fullMarks}.");
                        }
                    }
                }
            }
        });
    }
}
