<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDownloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('edit-downloads') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published', 'archived'])],
            'file' => ['nullable', 'file', 'max:51200'],
            'category_id' => ['nullable', 'integer', 'exists:download_categories,id'],
            'description' => ['nullable', 'string'],
            'access_type' => ['nullable', 'string', Rule::in(['public', 'registered', 'private'])],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
