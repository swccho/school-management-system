<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentAcademicAssignmentRequest;
use App\Models\School;
use App\Models\StudentAcademicAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentAcademicAssignmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-attendance')) {
            abort(403, 'Unauthorized.');
        }

        $query = StudentAcademicAssignment::query()->with(['student', 'academicSession', 'schoolClass', 'section']);

        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->input('academic_session_id'));
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->input('section_id'));
        }

        $assignments = $query->orderBy('roll_no')->orderBy('student_id')->get()
            ->map(fn (StudentAcademicAssignment $a) => [
                'id' => $a->id,
                'academic_session_id' => $a->academic_session_id,
                'academic_session_name' => $a->academicSession?->name,
                'class_id' => $a->class_id,
                'class_name' => $a->schoolClass?->name,
                'section_id' => $a->section_id,
                'section_name' => $a->section?->name,
                'student_id' => $a->student_id,
                'student_name' => $a->student?->full_name,
                'admission_no' => $a->student?->admission_no,
                'roll_no' => $a->roll_no,
                'status' => $a->status,
            ]);

        return response()->json($assignments);
    }

    public function store(StoreStudentAcademicAssignmentRequest $request): JsonResponse
    {
        $school = School::first();
        $assignment = StudentAcademicAssignment::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
            'status' => $request->input('status', 'active'),
        ]));
        $assignment->load(['student', 'academicSession', 'schoolClass', 'section']);

        return response()->json([
            'message' => 'Student assigned.',
            'assignment' => [
                'id' => $assignment->id,
                'academic_session_id' => $assignment->academic_session_id,
                'class_id' => $assignment->class_id,
                'section_id' => $assignment->section_id,
                'student_id' => $assignment->student_id,
                'student_name' => $assignment->student?->full_name,
                'roll_no' => $assignment->roll_no,
                'status' => $assignment->status,
            ],
        ], 201);
    }
}
