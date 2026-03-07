<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-sections') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $classId = $this->input('class_id');

        return [
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sections', 'name')->where('class_id', $classId),
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'room_no' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
        ];
    }
}
