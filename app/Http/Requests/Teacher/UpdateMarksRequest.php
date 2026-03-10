<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMarksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'submitted'])],
            'entries' => ['required', 'array'],
            'entries.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'entries.*.items' => ['required', 'array'],
            'entries.*.items.*.mark_component_id' => ['nullable', 'integer', 'exists:mark_components,id'],
            'entries.*.items.*.obtained_marks' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
