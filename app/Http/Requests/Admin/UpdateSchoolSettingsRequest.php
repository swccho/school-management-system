<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-settings') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'default_language' => ['nullable', 'string', 'max:20'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'date_format' => ['nullable', 'string', 'max:30'],
            'time_format' => ['nullable', 'string', 'max:30'],
            'default_currency' => ['nullable', 'string', 'max:10'],
            'attendance_mode' => ['nullable', 'string', 'max:50'],
            'result_publish_policy' => ['nullable', 'string', 'max:50'],
            'theme' => ['nullable', 'string', 'max:50'],
            'maintenance_mode' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('maintenance_mode')) {
            $this->merge(['maintenance_mode' => filter_var($this->maintenance_mode, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
