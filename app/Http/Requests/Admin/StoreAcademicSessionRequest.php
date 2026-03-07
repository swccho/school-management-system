<?php

namespace App\Http\Requests\Admin;

use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-academic-sessions') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolId = School::first()?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('academic_sessions', 'name')],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('academic_sessions', 'code')->where(function ($q) use ($schoolId) {
                    $schoolId === null ? $q->whereNull('school_id') : $q->where('school_id', $schoolId);
                }),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
