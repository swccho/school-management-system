<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\ClassRoutine;
use App\Models\ClassRoutineItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentRoutineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['student']);
        $student = $user->student;

        $currentSession = AcademicSession::query()
            ->where('school_id', $user->school_id)
            ->where('is_current', true)
            ->first();

        if (! $currentSession) {
            return response()->json(['items' => [], 'class_name' => null, 'section_name' => null]);
        }

        $assignment = $student->studentAcademicAssignments()
            ->where('academic_session_id', $currentSession->id)
            ->active()
            ->with(['schoolClass', 'section'])
            ->first();

        if (! $assignment) {
            return response()->json([
                'items' => [],
                'class_name' => null,
                'section_name' => null,
            ]);
        }

        $routine = ClassRoutine::query()
            ->where('academic_session_id', $currentSession->id)
            ->where('class_id', $assignment->class_id)
            ->where('section_id', $assignment->section_id)
            ->active()
            ->first();

        if (! $routine) {
            return response()->json([
                'items' => [],
                'class_name' => $assignment->schoolClass?->name,
                'section_name' => $assignment->section?->name,
            ]);
        }

        $query = ClassRoutineItem::query()
            ->where('class_routine_id', $routine->id)
            ->with(['subject', 'teacher.staff.user']);

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->input('day_of_week'));
        }

        $items = $query->orderBy('day_of_week')->orderBy('period_no')->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'day_of_week' => $item->day_of_week,
                'period_no' => $item->period_no,
                'subject_name' => $item->subject?->name,
                'teacher_name' => $item->teacher?->staff?->user?->name ?? '',
                'start_time' => $item->start_time,
                'end_time' => $item->end_time,
                'room_label' => $item->room_label ?? '',
            ])
            ->toArray();

        return response()->json([
            'items' => $items,
            'class_name' => $assignment->schoolClass?->name,
            'section_name' => $assignment->section?->name,
        ]);
    }
}
