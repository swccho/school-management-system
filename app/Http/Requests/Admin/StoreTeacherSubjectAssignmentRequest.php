<?php

namespace App\Http\Requests\Admin;

use App\Models\TeacherSubjectAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTeacherSubjectAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-teacher-subject-assignments') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $classId = $this->input('class_id');

        return [
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => [
                'nullable',
                'integer',
                'exists:sections,id',
                Rule::when($classId, [Rule::exists('sections', 'id')->where('class_id', $classId)]),
            ],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $sessionId = $this->input('academic_session_id');
            $teacherId = $this->input('teacher_id');
            $classId = $this->input('class_id');
            $subjectId = $this->input('subject_id');
            $sectionId = $this->input('section_id');

            $exists = TeacherSubjectAssignment::query()
                ->where('academic_session_id', $sessionId)
                ->where('teacher_id', $teacherId)
                ->where('class_id', $classId)
                ->where('subject_id', $subjectId);

            if ($sectionId) {
                $exists->where('section_id', $sectionId);
            } else {
                $exists->whereNull('section_id');
            }

            if ($exists->exists()) {
                $validator->errors()->add(
                    'academic_session_id',
                    'An assignment already exists for this session, teacher, class, section and subject.'
                );
            }
        });
    }
}
