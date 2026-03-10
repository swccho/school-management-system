<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('create-events') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'category_id' => ['nullable', 'integer', 'exists:event_categories,id'],
            'summary' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }
}
