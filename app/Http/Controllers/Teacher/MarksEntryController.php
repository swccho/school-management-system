<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\UpdateMarksRequest;
use App\Models\Exam;
use App\Models\MarkEntry;
use App\Services\MarksEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarksEntryController extends Controller
{
    public function __construct(
        private MarksEntryService $marksEntryService
    ) {}

    /**
     * List class/section/subject contexts the teacher can enter marks for (for this exam).
     */
    public function contexts(Request $request, Exam $exam): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher.subjectAssignments' => fn ($q) => $q->active()->with(['schoolClass', 'section', 'subject'])]);
        $teacher = $user->staff->teacher;

        $assignments = $teacher->subjectAssignments
            ->where('academic_session_id', $exam->academic_session_id);

        $configs = $exam->examSubjectConfigs;
        $contexts = [];
        foreach ($assignments as $a) {
            $config = $configs->firstWhere(fn ($c) => $c->class_id === $a->class_id && $c->subject_id === $a->subject_id);
            if (! $config) {
                continue;
            }
            $contexts[] = [
                'class_id' => $a->class_id,
                'section_id' => $a->section_id,
                'subject_id' => $a->subject_id,
                'class_name' => $a->schoolClass?->name,
                'section_name' => $a->section?->name,
                'subject_name' => $a->subject?->name,
            ];
        }
        $contexts = array_values(array_unique($contexts, SORT_REGULAR));
        return response()->json($contexts);
    }

    /**
     * List contexts with draft/submitted counts and context_status for this exam.
     */
    public function contextsWithStatus(Request $request, Exam $exam): JsonResponse
    {
        $contexts = $this->contexts($request, $exam)->getData(true);
        if (! is_array($contexts)) {
            $contexts = [];
        }
        $result = [];
        foreach ($contexts as $ctx) {
            $classId = $ctx['class_id'];
            $sectionId = $ctx['section_id'] ?? null;
            $subjectId = $ctx['subject_id'];
            $query = MarkEntry::where('exam_id', $exam->id)
                ->where('class_id', $classId)
                ->where('subject_id', $subjectId);
            if ($sectionId !== null) {
                $query->where('section_id', $sectionId);
            } else {
                $query->whereNull('section_id');
            }
            $entries = $query->get();
            $studentsCount = $entries->count();
            $draftCount = $entries->where('status', 'draft')->count();
            $submittedCount = $entries->where('status', 'submitted')->count();
            $contextStatus = $submittedCount > 0 && $submittedCount === $studentsCount ? 'submitted' : 'draft';
            $result[] = array_merge($ctx, [
                'students_count' => $studentsCount,
                'draft_count' => $draftCount,
                'submitted_count' => $submittedCount,
                'context_status' => $contextStatus,
            ]);
        }
        return response()->json($result);
    }

    /**
     * Get students, subject config, and current mark entries for one context.
     */
    public function show(Request $request, Exam $exam): JsonResponse
    {
        $request->validate([
            'class_id' => ['required', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'subject_id' => ['required', 'integer'],
        ]);
        $user = $request->user();
        $user->load(['staff.teacher.subjectAssignments' => fn ($q) => $q->active()]);
        $teacher = $user->staff->teacher;

        $allowed = $teacher->subjectAssignments
            ->where('academic_session_id', $exam->academic_session_id)
            ->contains(fn ($a) => $a->class_id === (int) $request->class_id
                && $a->section_id === (int) $request->section_id
                && $a->subject_id === (int) $request->subject_id);

        if (! $allowed) {
            return response()->json(['message' => 'You are not assigned to this class/section/subject.'], 403);
        }

        $classId = (int) $request->class_id;
        $sectionId = $request->filled('section_id') ? (int) $request->section_id : null;
        $subjectId = (int) $request->subject_id;

        $students = $this->marksEntryService->getEligibleStudents($exam->id, $classId, $sectionId);
        $config = $this->marksEntryService->getSubjectConfig($exam->id, $classId, $subjectId);
        if (! $config) {
            return response()->json(['message' => 'Subject not configured for this exam/class.'], 404);
        }
        $entries = $this->marksEntryService->getEntriesForContext($exam->id, $classId, $sectionId, $subjectId);

        $marksEntryOpen = $this->marksEntryService->isMarksEntryOpen($exam->id);
        $hasSubmitted = $this->marksEntryService->hasSubmittedEntries($exam->id, $classId, $sectionId, $subjectId);
        $canEdit = $marksEntryOpen && ! $hasSubmitted;
        $contextStatus = $hasSubmitted ? 'submitted' : (count($entries) > 0 ? 'draft' : 'draft');

        return response()->json([
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
            ],
            'class_id' => $classId,
            'section_id' => $sectionId,
            'subject_id' => $subjectId,
            'subject_config' => $config,
            'students' => $students,
            'entries' => $entries,
            'can_edit' => $canEdit,
            'context_status' => $contextStatus,
            'marks_entry_open' => $marksEntryOpen,
        ]);
    }

    /**
     * Save marks (draft or submit).
     */
    public function update(UpdateMarksRequest $request, Exam $exam): JsonResponse
    {

        $user = $request->user();
        $user->load(['staff.teacher.subjectAssignments' => fn ($q) => $q->active()]);
        $teacher = $user->staff->teacher;

        $allowed = $teacher->subjectAssignments
            ->where('academic_session_id', $exam->academic_session_id)
            ->contains(fn ($a) => $a->class_id === (int) $request->class_id
                && $a->section_id === (int) $request->section_id
                && $a->subject_id === (int) $request->subject_id);

        if (! $allowed) {
            return response()->json(['message' => 'Not assigned to this class/section/subject.'], 403);
        }

        $classId = (int) $request->class_id;
        $sectionId = $request->filled('section_id') ? (int) $request->section_id : null;
        $subjectId = (int) $request->subject_id;

        if (! $this->marksEntryService->isMarksEntryOpen($exam->id)) {
            return response()->json(['message' => 'Marks entry is closed for this exam.'], 422);
        }

        if ($this->marksEntryService->hasSubmittedEntries($exam->id, $classId, $sectionId, $subjectId)) {
            return response()->json(['message' => 'Marks submitted for this context; editing is closed.'], 422);
        }

        $validated = $request->validated();
        $validated['exam_id'] = $exam->id;
        $validated['status'] = $validated['status'] ?? 'draft';

        $this->marksEntryService->saveMarks($validated);

        return response()->json(['message' => 'Marks saved.']);
    }

    /**
     * Submit marks for a context (set status to submitted).
     */
    public function submit(Request $request, Exam $exam): JsonResponse
    {
        $request->validate([
            'class_id' => ['required', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'subject_id' => ['required', 'integer'],
        ]);

        $user = $request->user();
        $user->load(['staff.teacher.subjectAssignments' => fn ($q) => $q->active()]);
        $teacher = $user->staff->teacher;

        $allowed = $teacher->subjectAssignments
            ->where('academic_session_id', $exam->academic_session_id)
            ->contains(fn ($a) => $a->class_id === (int) $request->class_id
                && $a->section_id === (int) $request->section_id
                && $a->subject_id === (int) $request->subject_id);

        if (! $allowed) {
            return response()->json(['message' => 'Not assigned to this class/section/subject.'], 403);
        }

        $updated = MarkEntry::where('exam_id', $exam->id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('section_id', $request->section_id ?? null)
            ->update(['status' => 'submitted']);

        return response()->json(['message' => 'Marks submitted.', 'updated_count' => $updated]);
    }
}
