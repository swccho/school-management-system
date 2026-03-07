<?php

namespace App\Http\Requests\Admin;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-attendance') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(['draft', 'final'])],
            'remarks' => ['nullable', 'string', 'max:500'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.id' => ['nullable', 'integer', 'exists:attendance_records,id'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.student_academic_assignment_id' => ['nullable', 'integer', 'exists:student_academic_assignments,id'],
            'records.*.attendance_status' => ['required', 'string', Rule::in(AttendanceRecord::validStatuses())],
            'records.*.reason' => ['nullable', 'string', 'max:255'],
            'records.*.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            /** @var AttendanceSession|null $session */
            $session = $this->route('attendance_session');
            if (! $session instanceof AttendanceSession) {
                return;
            }
            $sessionId = $session->academic_session_id;
            $classId = $session->class_id;
            $sectionId = $session->section_id;

            $eligibleIds = \App\Models\StudentAcademicAssignment::query()
                ->where('academic_session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->active()
                ->pluck('student_id')
                ->all();

            $recordStudentIds = array_map(fn ($r) => (int) ($r['student_id'] ?? 0), $this->input('records', []));
            $invalid = array_diff($recordStudentIds, $eligibleIds);
            if (! empty($invalid)) {
                $validator->errors()->add(
                    'records',
                    'One or more students are not assigned to this academic session, class and section.'
                );
            }
        });
    }
}
