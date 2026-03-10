<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-gallery') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published', 'archived'])],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'gallery_type' => ['nullable', 'string', 'max:100'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
