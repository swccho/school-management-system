<?php

namespace App\Http\Requests\Admin;

use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-classes') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolClass = $this->route('school_class');
        $schoolId = School::first()?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('school_classes', 'name')->where('school_id', $schoolId)->ignore($schoolClass->id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('school_classes', 'code')->where('school_id', $schoolId)->ignore($schoolClass->id),
            ],
            'numeric_level' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
        ];
    }
}
