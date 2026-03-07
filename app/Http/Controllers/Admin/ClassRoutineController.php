<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassRoutineRequest;
use App\Http\Requests\Admin\UpdateClassRoutineRequest;
use App\Models\ClassRoutine;
use App\Models\ClassRoutineItem;
use App\Services\ClassRoutineService;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassRoutineController extends Controller
{
    public function __construct(
        private ClassRoutineService $classRoutineService,
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-routines')) {
            abort(403, 'Unauthorized.');
        }

        $query = ClassRoutine::query()
            ->with(['academicSession', 'schoolClass', 'section']);

        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->input('academic_session_id'));
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->input('section_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $routines = $query->orderByDesc('effective_from')
            ->orderBy('class_id')
            ->orderBy('section_id')
            ->get()
            ->map(fn (ClassRoutine $r) => $this->routineToArray($r));

        return response()->json($routines);
    }

    public function store(StoreClassRoutineRequest $request): JsonResponse
    {
        $routine = $this->classRoutineService->createRoutineWithItems($request->validated());

        return response()->json([
            'message' => 'Routine created.',
            'class_routine' => $this->routineToArray($routine, true),
        ], 201);
    }

    public function show(Request $request, ClassRoutine $class_routine): JsonResponse
    {
        if (! $request->user()->hasPermission('view-routines')) {
            abort(403, 'Unauthorized.');
        }

        $class_routine->load(['classRoutineItems.subject', 'classRoutineItems.teacher.staff', 'academicSession', 'schoolClass', 'section']);

        return response()->json($this->routineToArray($class_routine, true));
    }

    public function update(UpdateClassRoutineRequest $request, ClassRoutine $class_routine): JsonResponse
    {
        $routine = $this->classRoutineService->updateRoutineWithItems($class_routine, $request->validated());

        return response()->json([
            'message' => 'Routine updated.',
            'class_routine' => $this->routineToArray($routine, true),
        ]);
    }

    private function routineToArray(ClassRoutine $r, bool $withItems = false): array
    {
        $arr = [
            'id' => $r->id,
            'academic_session_id' => $r->academic_session_id,
            'academic_session_name' => $r->academicSession?->name,
            'class_id' => $r->class_id,
            'class_name' => $r->schoolClass?->name,
            'section_id' => $r->section_id,
            'section_name' => $r->section?->name,
            'title' => $r->title,
            'effective_from' => $r->effective_from?->format('Y-m-d'),
            'effective_from_formatted' => $this->dateTimeFormatter->formatDate($r->effective_from),
            'effective_to' => $r->effective_to?->format('Y-m-d'),
            'effective_to_formatted' => $this->dateTimeFormatter->formatDate($r->effective_to),
            'status' => $r->status,
            'remarks' => $r->remarks,
            'created_at' => $r->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($r->created_at),
            'updated_at' => $r->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($r->updated_at),
        ];

        if ($withItems && $r->relationLoaded('classRoutineItems')) {
            $arr['items'] = $r->classRoutineItems->sortBy(['day_of_week', 'period_no'])->values()->map(fn (ClassRoutineItem $i) => [
                'id' => $i->id,
                'day_of_week' => $i->day_of_week,
                'period_no' => $i->period_no,
                'start_time' => substr($i->start_time, 0, 5),
                'end_time' => substr($i->end_time, 0, 5),
                'subject_id' => $i->subject_id,
                'subject_name' => $i->subject?->name,
                'teacher_id' => $i->teacher_id,
                'teacher_name' => $i->teacher?->staff?->full_name,
                'room_label' => $i->room_label,
                'remarks' => $i->remarks,
            ])->all();
        }

        return $arr;
    }
}
