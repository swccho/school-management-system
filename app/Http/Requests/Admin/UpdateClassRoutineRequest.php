<?php

namespace App\Http\Requests\Admin;

use App\Models\ClassRoutineItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateClassRoutineRequest extends FormRequest
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
        $rules = [
            'title' => ['nullable', 'string', 'max:255'],
            'effective_from' => ['sometimes', 'date'],
            'effective_to' => ['nullable', 'date'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'remarks' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'exists:class_routine_items,id'],
            'items.*.day_of_week' => ['required', 'string', Rule::in(ClassRoutineItem::validDays())],
            'items.*.period_no' => ['required', 'integer', 'min:1', 'max:255'],
            'items.*.start_time' => ['required', 'date_format:H:i'],
            'items.*.end_time' => ['required', 'date_format:H:i'],
            'items.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'items.*.teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'items.*.room_label' => ['nullable', 'string', 'max:100'],
            'items.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
        if ($this->has('effective_from') && $this->filled('effective_to')) {
            $rules['effective_to'][] = 'after_or_equal:effective_from';
        }
        return $rules;
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
