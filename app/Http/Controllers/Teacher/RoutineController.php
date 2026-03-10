<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoutineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;

        $query = $teacher->classRoutineItems()->with(['subject', 'classRoutine.schoolClass', 'classRoutine.section']);

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->input('day_of_week'));
        }
        if ($request->filled('class_id')) {
            $query->whereHas('classRoutine', fn ($q) => $q->where('class_id', $request->input('class_id')));
        }
        if ($request->filled('section_id')) {
            $query->whereHas('classRoutine', fn ($q) => $q->where('section_id', $request->input('section_id')));
        }

        $items = $query->orderBy('day_of_week')->orderBy('period_no')->get();

        $data = $items->map(fn ($item) => [
            'id' => $item->id,
            'day_of_week' => $item->day_of_week,
            'period_no' => $item->period_no,
            'subject_name' => $item->subject?->name,
            'class_name' => $item->classRoutine?->schoolClass?->name,
            'section_name' => $item->classRoutine?->section?->name,
            'start_time' => $item->start_time,
            'end_time' => $item->end_time,
            'room_label' => $item->room_label,
        ]);

        return response()->json($data);
    }
}
