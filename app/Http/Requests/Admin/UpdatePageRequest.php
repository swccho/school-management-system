<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('edit-pages') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $page = $this->route('page');

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pages')->where('school_id', $page?->school_id)->ignore($page?->id),
            ],
            'content' => ['sometimes', 'string'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published', 'archived'])],
            'page_type' => ['nullable', 'string', 'max:100'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
