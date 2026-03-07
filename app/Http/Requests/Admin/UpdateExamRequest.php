<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-exams') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'result_publish_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'active', 'completed', 'archived'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'class_configs' => ['required', 'array', 'min:1'],
            'class_configs.*.id' => ['nullable', 'integer', 'exists:exam_class_configs,id'],
            'class_configs.*.class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'class_configs.*.section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'class_configs.*.status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'subject_configs' => ['required', 'array', 'min:1'],
            'subject_configs.*.id' => ['nullable', 'integer', 'exists:exam_subject_configs,id'],
            'subject_configs.*.class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'subject_configs.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'subject_configs.*.full_marks' => ['required', 'integer', 'min:0'],
            'subject_configs.*.pass_marks' => ['required', 'integer', 'min:0'],
            'subject_configs.*.theory_marks' => ['nullable', 'integer', 'min:0'],
            'subject_configs.*.practical_marks' => ['nullable', 'integer', 'min:0'],
            'subject_configs.*.oral_marks' => ['nullable', 'integer', 'min:0'],
            'subject_configs.*.has_practical' => ['nullable', 'boolean'],
            'subject_configs.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'subject_configs.*.mark_components' => ['nullable', 'array'],
            'subject_configs.*.mark_components.*.id' => ['nullable', 'integer', 'exists:mark_components,id'],
            'subject_configs.*.mark_components.*.name' => ['required', 'string', 'max:100'],
            'subject_configs.*.mark_components.*.component_type' => ['nullable', 'string', Rule::in(['theory', 'practical', 'oral', 'mcq', 'written', 'other'])],
            'subject_configs.*.mark_components.*.marks' => ['required', 'integer', 'min:0'],
            'subject_configs.*.mark_components.*.pass_marks' => ['nullable', 'integer', 'min:0'],
            'subject_configs.*.mark_components.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $classConfigs = $this->input('class_configs', []);
            $seen = [];
            foreach ($classConfigs as $i => $c) {
                $key = ($c['class_id'] ?? '') . '-' . ($c['section_id'] ?? 'null');
                if (isset($seen[$key])) {
                    $validator->errors()->add("class_configs.{$i}.class_id", 'Duplicate class/section in target classes.');
                    break;
                }
                $seen[$key] = true;
                if (! empty($c['section_id']) && ! empty($c['class_id'])) {
                    $sectionBelongsToClass = \App\Models\Section::where('id', $c['section_id'])->where('class_id', $c['class_id'])->exists();
                    if (! $sectionBelongsToClass) {
                        $validator->errors()->add("class_configs.{$i}.section_id", 'Section does not belong to the selected class.');
                    }
                }
            }
            $subjectConfigs = $this->input('subject_configs', []);
            $subSeen = [];
            foreach ($subjectConfigs as $i => $s) {
                $key = ($s['class_id'] ?? '') . '-' . ($s['subject_id'] ?? '');
                if (isset($subSeen[$key])) {
                    $validator->errors()->add("subject_configs.{$i}.subject_id", 'Duplicate subject for same class.');
                    break;
                }
                $subSeen[$key] = true;
                $pass = (int) ($s['pass_marks'] ?? 0);
                $full = (int) ($s['full_marks'] ?? 0);
                if ($full > 0 && $pass > $full) {
                    $validator->errors()->add("subject_configs.{$i}.pass_marks", 'Pass marks cannot exceed full marks.');
                }
            }
        });
    }
}
