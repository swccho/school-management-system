<?php

namespace App\Http\Requests\Admin;

use App\Models\StudentAcademicAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentAcademicAssignmentRequest extends FormRequest
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
        $classId = $this->input('class_id');

        return [
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => [
                'required',
                'integer',
                'exists:sections,id',
                Rule::exists('sections', 'id')->where('class_id', $classId),
            ],
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'roll_no' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $exists = StudentAcademicAssignment::query()
                ->where('academic_session_id', $this->input('academic_session_id'))
                ->where('class_id', $this->input('class_id'))
                ->where('section_id', $this->input('section_id'))
                ->where('student_id', $this->input('student_id'))
                ->exists();
            if ($exists) {
                $validator->errors()->add(
                    'student_id',
                    'This student is already assigned to this session, class and section.'
                );
            }
        });
    }
}
