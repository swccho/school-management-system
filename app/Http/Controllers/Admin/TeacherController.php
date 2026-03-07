<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\School;
use App\Models\Teacher;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-teachers')) {
            abort(403, 'Unauthorized.');
        }

        $teachers = Teacher::query()
            ->with('staff.department', 'staff.designation')
            ->get()
            ->map(fn (Teacher $t) => $this->toArray($t));

        return response()->json($teachers);
    }

    public function store(StoreTeacherRequest $request): JsonResponse
    {
        $school = School::first();
        $teacher = Teacher::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
        ]));

        $teacher->load('staff.department', 'staff.designation');

        return response()->json([
            'message' => 'Teacher created.',
            'teacher' => $this->toArray($teacher),
        ], 201);
    }

    public function show(Request $request, Teacher $teacher): JsonResponse
    {
        if (! $request->user()->hasPermission('view-teachers')) {
            abort(403, 'Unauthorized.');
        }

        $teacher->load('staff.department', 'staff.designation');

        return response()->json($this->toArray($teacher));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): JsonResponse
    {
        $teacher->update($request->validated());
        $teacher->load('staff.department', 'staff.designation');

        return response()->json([
            'message' => 'Teacher updated.',
            'teacher' => $this->toArray($teacher->fresh()),
        ]);
    }

    private function toArray(Teacher $t): array
    {
        $staff = $t->staff;

        return [
            'id' => $t->id,
            'staff_id' => $t->staff_id,
            'employee_id' => $staff?->employee_id,
            'full_name' => $staff?->full_name,
            'teacher_code' => $t->teacher_code,
            'qualification' => $t->qualification,
            'specialization' => $t->specialization,
            'experience_years' => $t->experience_years,
            'is_class_teacher' => $t->is_class_teacher,
            'department_name' => $staff?->department?->name,
            'designation_name' => $staff?->designation?->name,
            'status' => $t->status,
            'created_at' => $t->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($t->created_at),
            'updated_at' => $t->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($t->updated_at),
        ];
    }
}
