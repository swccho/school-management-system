<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDownloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('create-downloads') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'file' => ['required', 'file', 'max:51200'], // 50MB
            'category_id' => ['nullable', 'integer', 'exists:download_categories,id'],
            'description' => ['nullable', 'string'],
            'access_type' => ['nullable', 'string', Rule::in(['public', 'registered', 'private'])],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
