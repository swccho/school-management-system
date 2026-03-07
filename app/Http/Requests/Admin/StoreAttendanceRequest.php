<?php

namespace App\Http\Requests\Admin;

use App\Models\AttendanceRecord;
use App\Models\StudentAcademicAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAttendanceRequest extends FormRequest
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
        $classId = $this->input('class_id');
        $sessionId = $this->input('academic_session_id');
        $sectionId = $this->input('section_id');

        return [
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => [
                'required',
                'integer',
                'exists:sections,id',
                Rule::exists('sections', 'id')->where('class_id', $classId),
            ],
            'attendance_date' => ['required', 'date'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'final'])],
            'remarks' => ['nullable', 'string', 'max:500'],
            'records' => ['required', 'array', 'min:1'],
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
            $sessionId = (int) $this->input('academic_session_id');
            $classId = (int) $this->input('class_id');
            $sectionId = (int) $this->input('section_id');
            $date = $this->input('attendance_date');

            $eligibleIds = StudentAcademicAssignment::query()
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

            $duplicate = \App\Models\AttendanceSession::query()
                ->where('academic_session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->whereDate('attendance_date', $date)
                ->exists();
            if ($duplicate) {
                $validator->errors()->add(
                    'attendance_date',
                    'Attendance for this session, class, section and date already exists.'
                );
            }
        });
    }
}
