<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMarksEntryRequest;
use App\Services\MarksEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarksEntryController extends Controller
{
    public function __construct(
        private MarksEntryService $marksEntryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-marks-entry')) {
            abort(403, 'Unauthorized.');
        }

        $examId = $request->input('exam_id') ? (int) $request->input('exam_id') : null;
        $classId = $request->input('class_id') ? (int) $request->input('class_id') : null;
        $sectionId = $request->filled('section_id') ? (int) $request->input('section_id') : null;
        $subjectId = $request->input('subject_id') ? (int) $request->input('subject_id') : null;
        $status = $request->input('status');

        $list = $this->marksEntryService->listGrouped($examId, $classId, $sectionId, $subjectId, $status);

        return response()->json($list);
    }

    public function eligibleStudents(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-marks-entry')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
        ]);

        $sectionId = $request->filled('section_id') ? (int) $request->input('section_id') : null;
        $students = $this->marksEntryService->getEligibleStudents(
            (int) $request->input('exam_id'),
            (int) $request->input('class_id'),
            $sectionId
        );

        return response()->json($students);
    }

    public function subjectsForContext(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-marks-entry')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
        ]);

        $subjects = $this->marksEntryService->getSubjectsForExamClass(
            (int) $request->input('exam_id'),
            (int) $request->input('class_id')
        );

        return response()->json($subjects);
    }

    public function subjectConfig(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-marks-entry')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        $config = $this->marksEntryService->getSubjectConfig(
            (int) $request->input('exam_id'),
            (int) $request->input('class_id'),
            (int) $request->input('subject_id')
        );

        if (! $config) {
            return response()->json(['message' => 'Subject not configured for this exam and class.'], 404);
        }

        return response()->json($config);
    }

    public function store(StoreMarksEntryRequest $request): JsonResponse
    {
        $this->marksEntryService->saveMarks($request->validated());

        return response()->json(['message' => 'Marks saved.'], 201);
    }

    public function show(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-marks-entry')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        $sectionId = $request->filled('section_id') ? (int) $request->input('section_id') : null;
        $entries = $this->marksEntryService->getEntriesForContext(
            (int) $request->input('exam_id'),
            (int) $request->input('class_id'),
            $sectionId,
            (int) $request->input('subject_id')
        );

        $exam = \App\Models\Exam::with('academicSession', 'examType')->find($request->input('exam_id'));
        $class = \App\Models\SchoolClass::find($request->input('class_id'));
        $section = $request->filled('section_id') ? \App\Models\Section::find($request->input('section_id')) : null;
        $subject = \App\Models\Subject::find($request->input('subject_id'));

        return response()->json([
            'exam' => $exam ? ['id' => $exam->id, 'name' => $exam->name, 'academic_session_name' => $exam->academicSession?->name] : null,
            'class' => $class ? ['id' => $class->id, 'name' => $class->name] : null,
            'section' => $section ? ['id' => $section->id, 'name' => $section->name] : null,
            'subject' => $subject ? ['id' => $subject->id, 'name' => $subject->name] : null,
            'entries' => $entries,
        ]);
    }
}
