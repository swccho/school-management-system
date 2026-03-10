<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['student.guardians']);
        $student = $user->student;

        $currentSession = AcademicSession::query()
            ->where('school_id', $user->school_id)
            ->where('is_current', true)
            ->first();

        $currentAssignment = null;
        if ($currentSession) {
            $assignment = $student->studentAcademicAssignments()
                ->where('academic_session_id', $currentSession->id)
                ->active()
                ->with(['schoolClass', 'section'])
                ->first();
            if ($assignment) {
                $currentAssignment = [
                    'class_id' => $assignment->class_id,
                    'section_id' => $assignment->section_id,
                    'class_name' => $assignment->schoolClass?->name,
                    'section_name' => $assignment->section?->name,
                    'academic_session_id' => $assignment->academic_session_id,
                    'academic_session_name' => $currentSession->name,
                    'roll_no' => $assignment->roll_no ?? $student->roll_no,
                ];
            }
        }

        $guardiansList = $student->guardians()
            ->orderByPivot('is_primary', 'desc')
            ->get()
            ->map(fn ($g) => [
                'name' => $g->name,
                'relation' => $g->pivot->relationship_label ?? $g->relation_type ?? null,
                'phone' => $g->phone,
                'email' => $g->email,
                'address' => $g->address,
            ])
            ->toArray();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'full_name' => $student->full_name,
                'admission_no' => $student->admission_no,
                'roll_no' => $student->roll_no,
                'gender' => $student->gender,
                'date_of_birth' => $student->date_of_birth?->format('Y-m-d'),
                'blood_group' => $student->blood_group,
                'email' => $student->email ?? $user->email,
                'phone' => $student->phone ?? $user->phone,
                'present_address' => $student->present_address,
                'permanent_address' => $student->permanent_address,
                'photo_path' => $student->photo_path,
            ],
            'current_assignment' => $currentAssignment,
            'guardians' => $guardiansList,
        ]);
    }
}
