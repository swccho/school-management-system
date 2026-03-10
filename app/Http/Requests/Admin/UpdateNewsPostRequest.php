<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('edit-news-posts') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published', 'archived'])],
            'category_id' => ['nullable', 'integer', 'exists:news_categories,id'],
            'summary' => ['nullable', 'string', 'max:500'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
