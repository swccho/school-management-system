<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\LessonPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;

        $query = LessonPlan::query()
            ->where('teacher_id', $teacher->id)
            ->with(['schoolClass', 'section', 'subject'])
            ->orderByDesc('plan_date');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('plan_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('plan_date', '<=', $request->date_to);
        }

        $items = $query->limit(100)->get()->map(fn ($l) => [
            'id' => $l->id,
            'title' => $l->title,
            'content' => $l->content,
            'plan_date' => $l->plan_date->format('Y-m-d'),
            'class_id' => $l->class_id,
            'section_id' => $l->section_id,
            'subject_id' => $l->subject_id,
            'class_name' => $l->schoolClass?->name,
            'section_name' => $l->section?->name,
            'subject_name' => $l->subject?->name,
            'status' => $l->status,
        ]);

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;
        $schoolId = $user->school_id;
        $currentSession = AcademicSession::where('school_id', $schoolId)->where('is_current', true)->first();

        $request->validate([
            'class_id' => ['required', 'exists:school_classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'plan_date' => ['required', 'date'],
            'status' => ['nullable', 'in:draft,completed'],
        ]);

        $allowed = $teacher->subjectAssignments()->active()
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if (! $allowed) {
            return response()->json(['message' => 'Not assigned to this class/section/subject.'], 403);
        }

        $lessonPlan = LessonPlan::create([
            'school_id' => $schoolId,
            'teacher_id' => $teacher->id,
            'academic_session_id' => $currentSession?->id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'content' => $request->content,
            'plan_date' => $request->plan_date,
            'status' => $request->input('status', 'draft'),
        ]);

        $lessonPlan->load(['schoolClass', 'section', 'subject']);
        return response()->json([
            'message' => 'Lesson plan created.',
            'lesson_plan' => $this->toArray($lessonPlan),
        ], 201);
    }

    public function show(Request $request, LessonPlan $lesson_plan): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        if ($lesson_plan->teacher_id !== $user->staff->teacher->id) {
            abort(403);
        }
        $lesson_plan->load(['schoolClass', 'section', 'subject']);
        return response()->json($this->toArray($lesson_plan));
    }

    public function update(Request $request, LessonPlan $lesson_plan): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        if ($lesson_plan->teacher_id !== $user->staff->teacher->id) {
            abort(403);
        }
        $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'plan_date' => ['sometimes', 'date'],
            'status' => ['nullable', 'in:draft,completed'],
        ]);
        $lesson_plan->update($request->only(['title', 'content', 'plan_date', 'status']));
        $lesson_plan->load(['schoolClass', 'section', 'subject']);
        return response()->json(['message' => 'Lesson plan updated.', 'lesson_plan' => $this->toArray($lesson_plan)]);
    }

    public function destroy(Request $request, LessonPlan $lesson_plan): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        if ($lesson_plan->teacher_id !== $user->staff->teacher->id) {
            abort(403);
        }
        $lesson_plan->delete();
        return response()->json(['message' => 'Lesson plan deleted.']);
    }

    private function toArray(LessonPlan $l): array
    {
        return [
            'id' => $l->id,
            'title' => $l->title,
            'content' => $l->content,
            'plan_date' => $l->plan_date->format('Y-m-d'),
            'class_id' => $l->class_id,
            'class_name' => $l->schoolClass?->name,
            'section_id' => $l->section_id,
            'section_name' => $l->section?->name,
            'subject_id' => $l->subject_id,
            'subject_name' => $l->subject?->name,
            'status' => $l->status,
        ];
    }
}
