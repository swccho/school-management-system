<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('create-notices') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:notice_categories,id'],
            'publish_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:publish_date'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_featured' => ['nullable', 'boolean'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:5120', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png,image/gif'],
        ];
    }
}
