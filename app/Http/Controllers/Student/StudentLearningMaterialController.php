<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\LearningMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentLearningMaterialController extends Controller
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
            return response()->json(['data' => []]);
        }

        $assignment = $student->studentAcademicAssignments()
            ->where('academic_session_id', $currentSession->id)
            ->active()
            ->first();

        if (! $assignment) {
            return response()->json(['data' => []]);
        }

        $query = LearningMaterial::query()
            ->where('academic_session_id', $currentSession->id)
            ->where('class_id', $assignment->class_id)
            ->where('section_id', $assignment->section_id)
            ->with(['subject', 'teacher.staff.user'])
            ->orderBy('sort_order')
            ->orderByDesc('created_at');

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $items = $query->limit(100)->get()->map(fn ($m) => [
            'id' => $m->id,
            'title' => $m->title,
            'description' => $m->description,
            'file_name' => $m->file_name,
            'file_url' => $m->file_path ? Storage::disk('public')->url($m->file_path) : null,
            'subject_name' => $m->subject?->name,
            'teacher_name' => $m->teacher?->staff?->user?->name ?? '',
            'publish_date' => $m->created_at?->format('Y-m-d'),
        ]);

        return response()->json(['data' => $items->toArray()]);
    }
}
