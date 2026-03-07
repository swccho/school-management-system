<?php

namespace App\Http\Requests\Admin;

use App\Models\ClassRoutineItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreClassRoutineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-routines') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $classId = $this->input('class_id');

        return [
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => [
                'required',
                'integer',
                'exists:sections,id',
                Rule::exists('sections', 'id')->where('class_id', $classId),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'remarks' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.day_of_week' => ['required', 'string', Rule::in(ClassRoutineItem::validDays())],
            'items.*.period_no' => ['required', 'integer', 'min:1', 'max:255'],
            'items.*.start_time' => ['required', 'date_format:H:i'],
            'items.*.end_time' => ['required', 'date_format:H:i'],
            'items.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'items.*.teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'items.*.room_label' => ['nullable', 'string', 'max:100'],
            'items.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $items = $this->input('items', []);
            $seen = [];
            foreach ($items as $i => $item) {
                $key = ($item['day_of_week'] ?? '') . '-' . (int) ($item['period_no'] ?? 0);
                if (isset($seen[$key])) {
                    $validator->errors()->add("items.{$i}.period_no", 'Duplicate day and period number in this routine.');
                }
                $seen[$key] = true;
                if (! empty($item['start_time']) && ! empty($item['end_time']) && $item['end_time'] <= $item['start_time']) {
                    $validator->errors()->add("items.{$i}.end_time", 'End time must be after start time.');
                }
            }
        });
    }
}
