<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitHomeworkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,txt,jpg,jpeg,png'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
