<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('create-news-posts') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'category_id' => ['nullable', 'integer', 'exists:news_categories,id'],
            'summary' => ['nullable', 'string', 'max:500'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
