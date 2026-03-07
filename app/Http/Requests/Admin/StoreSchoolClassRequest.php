<?php

namespace App\Http\Requests\Admin;

use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolClassRequest extends FormRequest
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
        $schoolId = School::first()?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('school_classes', 'name')->where('school_id', $schoolId),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('school_classes', 'code')->where('school_id', $schoolId),
            ],
            'numeric_level' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
        ];
    }
}
