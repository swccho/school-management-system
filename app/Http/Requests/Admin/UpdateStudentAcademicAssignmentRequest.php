<?php

namespace App\Http\Requests\Admin;

use App\Models\StudentAcademicAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentAcademicAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-attendance') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $assignment = $this->route('student_academic_assignment');
        $classId = $this->input('class_id') ?? $assignment?->class_id;

        $sectionRule = [
            'nullable',
            'integer',
            'exists:sections,id',
        ];
        if ($classId) {
            $sectionRule[] = Rule::exists('sections', 'id')->where('class_id', $classId);
        }

        return [
            'academic_session_id' => ['sometimes', 'integer', 'exists:academic_sessions,id'],
            'class_id' => ['sometimes', 'integer', 'exists:school_classes,id'],
            'section_id' => $sectionRule,
            'roll_no' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $sessionId = $this->input('academic_session_id');
            $classId = $this->input('class_id');
            $sectionId = $this->input('section_id');
            if ($sessionId === null && $classId === null && $sectionId === null) {
                return;
            }
            $assignment = $this->route('student_academic_assignment');
            $query = StudentAcademicAssignment::query()
                ->where('academic_session_id', $sessionId ?? $assignment->academic_session_id)
                ->where('class_id', $classId ?? $assignment->class_id)
                ->where('section_id', $sectionId ?? $assignment->section_id)
                ->where('student_id', $assignment->student_id)
                ->where('id', '!=', $assignment->id);
            if ($query->exists()) {
                $validator->errors()->add(
                    'section_id',
                    'This student is already assigned to this session, class and section.'
                );
            }
        });
    }
}
