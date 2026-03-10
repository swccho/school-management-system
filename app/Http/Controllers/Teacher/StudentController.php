<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\StudentAcademicAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => ['required', 'exists:school_classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
        ]);

        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;
        $schoolId = $user->school_id;

        $currentSession = AcademicSession::where('school_id', $schoolId)->where('is_current', true)->first();
        if (! $currentSession) {
            return response()->json([]);
        }

        $allowed = $teacher->subjectAssignments()
            ->where('academic_session_id', $currentSession->id)
            ->active()
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->exists();

        if (! $allowed) {
            return response()->json(['message' => 'You are not assigned to this class/section.'], 403);
        }

        $query = StudentAcademicAssignment::query()
            ->where('academic_session_id', $currentSession->id)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->active()
            ->with(['student'])
            ->orderBy('roll_no');

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('roll_no', 'like', '%' . $term . '%')
                    ->orWhereHas('student', function ($q2) use ($term) {
                        $q2->where('admission_no', 'like', '%' . $term . '%')
                            ->orWhere('first_name', 'like', '%' . $term . '%')
                            ->orWhere('last_name', 'like', '%' . $term . '%');
                    });
            });
        }

        $assignments = $query->get();

        $data = $assignments->map(fn ($a) => [
            'id' => $a->student_id,
            'student_id' => $a->student_id,
            'full_name' => $a->student?->full_name,
            'admission_no' => $a->student?->admission_no,
            'roll_no' => $a->roll_no,
        ]);

        return response()->json($data);
    }
}
