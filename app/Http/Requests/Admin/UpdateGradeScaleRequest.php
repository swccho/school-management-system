<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateGradeScaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-grade-scales') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.min_mark' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.max_mark' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.letter_grade' => ['required', 'string', 'max:10'],
            'items.*.grade_point' => ['required', 'numeric', 'min:0', 'max:5'],
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
            foreach ($items as $i => $item) {
                $min = (float) ($item['min_mark'] ?? 0);
                $max = (float) ($item['max_mark'] ?? 0);
                if ($min > $max) {
                    $validator->errors()->add("items.{$i}.min_mark", 'Min mark must be less than or equal to max mark.');
                }
            }
            $ranges = collect($items)->map(fn ($i) => [(float) $i['min_mark'], (float) $i['max_mark']])->all();
            for ($a = 0; $a < count($ranges); $a++) {
                for ($b = $a + 1; $b < count($ranges); $b++) {
                    [$aMin, $aMax] = $ranges[$a];
                    [$bMin, $bMax] = $ranges[$b];
                    if ($aMin <= $bMax && $bMin <= $aMax) {
                        $validator->errors()->add('items', 'Grade ranges must not overlap.');
                        return;
                    }
                }
            }
        });
    }
}
