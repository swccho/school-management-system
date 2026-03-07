<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicSessionRequest extends FormRequest
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
        $session = $this->route('academic_session');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('academic_sessions', 'name')->ignore($session->id)],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('academic_sessions', 'code')
                    ->where(function ($q) use ($session) {
                        $session->school_id === null
                            ? $q->whereNull('school_id')
                            : $q->where('school_id', $session->school_id);
                    })
                    ->ignore($session->id),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
