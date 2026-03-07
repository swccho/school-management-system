<?php

namespace App\Http\Requests\Admin;

use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-subjects') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolId = School::first()?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects', 'name')->where('school_id', $schoolId),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')->where('school_id', $schoolId),
            ],
            'short_name' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:50', Rule::in(['general', 'elective', 'practical'])],
            'is_optional' => ['nullable', 'boolean'],
            'has_practical' => ['nullable', 'boolean'],
            'full_marks' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'pass_marks' => [
                'nullable',
                'integer',
                'min:0',
                'max:32767',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value !== null && $this->input('full_marks') !== null && (int) $value > (int) $this->input('full_marks')) {
                        $fail('Pass marks cannot be greater than full marks.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
        ];
    }
}
