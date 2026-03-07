<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherSubjectAssignmentRequest;
use App\Http\Requests\Admin\UpdateTeacherSubjectAssignmentRequest;
use App\Models\School;
use App\Models\TeacherSubjectAssignment;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherSubjectAssignmentController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-teacher-subject-assignments')) {
            abort(403, 'Unauthorized.');
        }

        $query = TeacherSubjectAssignment::query()
            ->with(['teacher.staff', 'academicSession', 'schoolClass', 'section', 'subject']);

        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->input('academic_session_id'));
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->input('teacher_id'));
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->input('section_id'));
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $assignments = $query->orderBy('academic_session_id')
            ->orderBy('class_id')
            ->orderBy('teacher_id')
            ->get()
            ->map(fn (TeacherSubjectAssignment $a) => $this->toArray($a));

        return response()->json($assignments);
    }

    public function store(StoreTeacherSubjectAssignmentRequest $request): JsonResponse
    {
        $school = School::first();
        $assignment = TeacherSubjectAssignment::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
            'status' => $request->input('status', 'active'),
        ]));

        $assignment->load(['teacher.staff', 'academicSession', 'schoolClass', 'section', 'subject']);

        return response()->json([
            'message' => 'Assignment created.',
            'assignment' => $this->toArray($assignment),
        ], 201);
    }

    public function show(Request $request, TeacherSubjectAssignment $teacher_subject_assignment): JsonResponse
    {
        if (! $request->user()->hasPermission('view-teacher-subject-assignments')) {
            abort(403, 'Unauthorized.');
        }

        $teacher_subject_assignment->load(['teacher.staff', 'academicSession', 'schoolClass', 'section', 'subject']);

        return response()->json($this->toArray($teacher_subject_assignment));
    }

    public function update(UpdateTeacherSubjectAssignmentRequest $request, TeacherSubjectAssignment $teacher_subject_assignment): JsonResponse
    {
        $teacher_subject_assignment->update($request->validated());
        $teacher_subject_assignment->load(['teacher.staff', 'academicSession', 'schoolClass', 'section', 'subject']);

        return response()->json([
            'message' => 'Assignment updated.',
            'assignment' => $this->toArray($teacher_subject_assignment->fresh()),
        ]);
    }

    private function toArray(TeacherSubjectAssignment $a): array
    {
        return [
            'id' => $a->id,
            'academic_session_id' => $a->academic_session_id,
            'academic_session_name' => $a->academicSession?->name,
            'teacher_id' => $a->teacher_id,
            'teacher_name' => $a->teacher?->staff?->full_name,
            'class_id' => $a->class_id,
            'class_name' => $a->schoolClass?->name,
            'section_id' => $a->section_id,
            'section_name' => $a->section?->name,
            'subject_id' => $a->subject_id,
            'subject_name' => $a->subject?->name,
            'status' => $a->status,
            'remarks' => $a->remarks,
            'created_at' => $a->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($a->created_at),
            'updated_at' => $a->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($a->updated_at),
        ];
    }
}
