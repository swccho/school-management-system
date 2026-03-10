<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminGuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-admin-guides') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $guide = $this->route('admin_guide');

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', 'exists:admin_guide_categories,id'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('admin_guides', 'slug')->ignore($guide->id)],
            'short_description' => ['nullable', 'string'],
            'content' => ['sometimes', 'string'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
