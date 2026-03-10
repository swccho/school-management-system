<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('edit-roles') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');
        $slugRule = ['nullable', 'string', 'max:255'];
        if ($role && $role->is_system) {
            $slugRule[] = Rule::in([$role->slug]);
        } else {
            $slugRule[] = Rule::unique('roles', 'slug')->ignore($role?->id);
        }

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role?->id)],
            'slug' => $slugRule,
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }
}
