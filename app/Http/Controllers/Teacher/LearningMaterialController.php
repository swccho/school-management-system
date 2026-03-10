<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\LearningMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LearningMaterialController extends Controller
{
    public function assignmentOptions(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;
        $schoolId = $user->school_id;
        $currentSession = AcademicSession::where('school_id', $schoolId)->where('is_current', true)->first();

        $options = [];
        if ($currentSession) {
            $options = $teacher->subjectAssignments()
                ->where('academic_session_id', $currentSession->id)
                ->active()
                ->with(['schoolClass', 'section', 'subject'])
                ->get()
                ->map(fn ($a) => [
                    'class_id' => $a->class_id,
                    'class_name' => $a->schoolClass?->name,
                    'section_id' => $a->section_id,
                    'section_name' => $a->section?->name,
                    'subject_id' => $a->subject_id,
                    'subject_name' => $a->subject?->name,
                ])
                ->values()
                ->toArray();
        }

        return response()->json($options);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        $teacher = $user->staff->teacher;

        $query = LearningMaterial::query()
            ->where('teacher_id', $teacher->id)
            ->with(['schoolClass', 'section', 'subject'])
            ->orderBy('sort_order')
            ->orderByDesc('created_at');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $items = $query->limit(100)->get()->map(fn ($m) => [
            'id' => $m->id,
            'title' => $m->title,
            'description' => $m->description,
            'file_name' => $m->file_name,
            'file_path' => $m->file_path,
            'file_url' => $m->file_path ? Storage::disk('public')->url($m->file_path) : null,
            'class_id' => $m->class_id,
            'class_name' => $m->schoolClass?->name,
            'section_id' => $m->section_id,
            'section_name' => $m->section?->name,
            'subject_id' => $m->subject_id,
            'subject_name' => $m->subject?->name,
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
            'file' => ['required', 'file'],
        ]);

        $allowed = $teacher->subjectAssignments()
            ->active()
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if (! $allowed) {
            return response()->json(['message' => 'Not assigned to this class/section/subject.'], 403);
        }

        $file = $request->file('file');
        $path = $file->store('learning-materials', 'public');
        $fileName = $file->getClientOriginalName();

        $material = LearningMaterial::create([
            'school_id' => $schoolId,
            'teacher_id' => $teacher->id,
            'academic_session_id' => $currentSession?->id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'file_name' => $fileName,
            'sort_order' => 0,
        ]);

        $material->load(['schoolClass', 'section', 'subject']);
        return response()->json([
            'message' => 'Learning material uploaded.',
            'learning_material' => $this->toArray($material),
        ], 201);
    }

    public function show(Request $request, LearningMaterial $learning_material): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        if ($learning_material->teacher_id !== $user->staff->teacher->id) {
            abort(403);
        }
        $learning_material->load(['schoolClass', 'section', 'subject']);
        return response()->json($this->toArray($learning_material));
    }

    public function update(Request $request, LearningMaterial $learning_material): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        if ($learning_material->teacher_id !== $user->staff->teacher->id) {
            abort(403);
        }
        $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $learning_material->update($request->only(['title', 'description']));
        if ($request->hasFile('file')) {
            if ($learning_material->file_path) {
                Storage::disk('public')->delete($learning_material->file_path);
            }
            $file = $request->file('file');
            $learning_material->update([
                'file_path' => $file->store('learning-materials', 'public'),
                'file_name' => $file->getClientOriginalName(),
            ]);
        }
        $learning_material->load(['schoolClass', 'section', 'subject']);
        return response()->json(['message' => 'Learning material updated.', 'learning_material' => $this->toArray($learning_material)]);
    }

    public function destroy(Request $request, LearningMaterial $learning_material): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        if ($learning_material->teacher_id !== $user->staff->teacher->id) {
            abort(403);
        }
        if ($learning_material->file_path) {
            Storage::disk('public')->delete($learning_material->file_path);
        }
        $learning_material->delete();
        return response()->json(['message' => 'Learning material deleted.']);
    }

    private function toArray(LearningMaterial $m): array
    {
        return [
            'id' => $m->id,
            'title' => $m->title,
            'description' => $m->description,
            'file_name' => $m->file_name,
            'file_path' => $m->file_path,
            'file_url' => $m->file_path ? Storage::disk('public')->url($m->file_path) : null,
            'class_id' => $m->class_id,
            'class_name' => $m->schoolClass?->name,
            'section_id' => $m->section_id,
            'section_name' => $m->section?->name,
            'subject_id' => $m->subject_id,
            'subject_name' => $m->subject?->name,
        ];
    }
}
