<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('edit-events') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'start_datetime' => ['sometimes', 'date'],
            'end_datetime' => ['sometimes', 'date', 'after:start_datetime'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published', 'archived'])],
            'category_id' => ['nullable', 'integer', 'exists:event_categories,id'],
            'summary' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
