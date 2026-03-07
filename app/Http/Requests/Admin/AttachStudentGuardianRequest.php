<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachStudentGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-students') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'guardian_id' => ['required', 'integer', 'exists:student_guardians,id'],
            'relationship_label' => ['nullable', 'string', 'max:50'],
            'is_primary' => ['nullable', 'boolean'],
            'can_receive_sms' => ['nullable', 'boolean'],
            'can_receive_email' => ['nullable', 'boolean'],
            'can_login' => ['nullable', 'boolean'],
        ];
    }
}
