<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('create-users') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $linkType = $this->input('link_type');
        $linkIdRules = $linkType ? ['required', 'integer'] : ['nullable', 'integer'];
        if ($linkType) {
            $table = match ($linkType) {
                'staff' => 'staffs',
                'student' => 'students',
                'guardian' => 'student_guardians',
                default => null,
            };
            if ($table) {
                $linkIdRules[] = Rule::exists($table, 'id')->whereNull('user_id');
            }
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'user_type' => ['nullable', 'string', Rule::in(['admin', 'teacher', 'staff', 'student', 'guardian'])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'link_type' => ['nullable', 'string', Rule::in(['staff', 'student', 'guardian'])],
            'link_id' => $linkIdRules,
        ];
    }
}
