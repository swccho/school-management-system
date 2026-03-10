<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('edit-users') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;
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
                $linkIdRules[] = Rule::exists($table, 'id')->where(function ($query) use ($userId) {
                    $query->whereNull('user_id')->orWhere('user_id', $userId);
                });
            }
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
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
