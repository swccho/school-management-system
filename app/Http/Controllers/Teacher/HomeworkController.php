<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Homework;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeworkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;
        $schoolId = $user->school_id;

        $query = Homework::query()
            ->where('teacher_id', $teacher->id)
            ->with(['schoolClass', 'section', 'subject'])
            ->orderByDesc('due_date');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $items = $query->limit(100)->get()->map(fn ($h) => [
            'id' => $h->id,
            'title' => $h->title,
            'description' => $h->description,
            'due_date' => $h->due_date?->format('Y-m-d'),
            'class_name' => $h->schoolClass?->name,
            'section_name' => $h->section?->name,
            'subject_name' => $h->subject?->name,
            'status' => $h->status,
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
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        $allowed = $teacher->subjectAssignments()
            ->active()
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if (! $allowed) {
            return response()->json(['message' => 'You are not assigned to this class/section/subject.'], 403);
        }

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('homework', 'public');
        }

        $homework = Homework::create([
            'school_id' => $schoolId,
            'teacher_id' => $teacher->id,
            'academic_session_id' => $currentSession?->id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'attachment_path' => $path,
            'status' => 'active',
        ]);

        $homework->load(['schoolClass', 'section', 'subject']);
        return response()->json([
            'message' => 'Homework created.',
            'homework' => $this->toArray($homework),
        ], 201);
    }

    public function show(Request $request, Homework $homework): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        if ($homework->teacher_id !== $user->staff->teacher->id) {
            abort(403);
        }
        $homework->load(['schoolClass', 'section', 'subject']);
        return response()->json($this->toArray($homework));
    }

    public function update(Request $request, Homework $homework): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        if ($homework->teacher_id !== $user->staff->teacher->id) {
            abort(403);
        }

        $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        $homework->update($request->only(['title', 'description', 'due_date']));
        if ($request->hasFile('attachment')) {
            if ($homework->attachment_path) {
                Storage::disk('public')->delete($homework->attachment_path);
            }
            $homework->update(['attachment_path' => $request->file('attachment')->store('homework', 'public')]);
        }

        $homework->load(['schoolClass', 'section', 'subject']);
        return response()->json(['message' => 'Homework updated.', 'homework' => $this->toArray($homework)]);
    }

    public function destroy(Request $request, Homework $homework): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        if ($homework->teacher_id !== $user->staff->teacher->id) {
            abort(403);
        }
        if ($homework->attachment_path) {
            Storage::disk('public')->delete($homework->attachment_path);
        }
        $homework->delete();
        return response()->json(['message' => 'Homework deleted.']);
    }

    private function toArray(Homework $h): array
    {
        return [
            'id' => $h->id,
            'title' => $h->title,
            'description' => $h->description,
            'due_date' => $h->due_date?->format('Y-m-d'),
            'class_id' => $h->class_id,
            'class_name' => $h->schoolClass?->name,
            'section_id' => $h->section_id,
            'section_name' => $h->section?->name,
            'subject_id' => $h->subject_id,
            'subject_name' => $h->subject?->name,
            'attachment_path' => $h->attachment_path,
            'status' => $h->status,
        ];
    }
}
