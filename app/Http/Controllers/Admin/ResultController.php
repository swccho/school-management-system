<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateResultsRequest;
use App\Models\ResultSummary;
use App\Services\DateTimeFormatter;
use App\Services\ResultGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function __construct(
        private ResultGenerationService $resultGenerationService,
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-results')) {
            abort(403, 'Unauthorized.');
        }

        $query = ResultSummary::with(['exam', 'student']);
        if ($request->filled('exam_id')) {
            $query->where('exam_id', (int) $request->input('exam_id'));
        }
        if ($request->filled('pass_status')) {
            $query->where('pass_status', $request->input('pass_status'));
        }
        if ($request->filled('published_status')) {
            $query->where('published_status', $request->input('published_status') === '1');
        }

        $results = $query->orderBy('exam_id')->orderBy('merit_position')->orderBy('student_id')->get()
            ->map(fn (ResultSummary $r) => [
                'id' => $r->id,
                'exam_id' => $r->exam_id,
                'exam_name' => $r->exam?->name,
                'student_id' => $r->student_id,
                'student_name' => $r->student?->full_name,
                'admission_no' => $r->student?->admission_no,
                'roll_no' => $r->student?->roll_no,
                'total_marks' => $r->total_marks,
                'obtained_marks' => $r->obtained_marks,
                'gpa' => $r->gpa,
                'letter_grade' => $r->letter_grade,
                'merit_position' => $r->merit_position,
                'pass_status' => $r->pass_status,
                'published_status' => $r->published_status,
                'generated_at' => $r->generated_at?->toIso8601String(),
                'generated_at_formatted' => $this->dateTimeFormatter->formatDateTime($r->generated_at),
            ]);

        return response()->json($results);
    }

    public function generate(GenerateResultsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $examId = (int) $validated['exam_id'];
        $classId = isset($validated['class_id']) && $validated['class_id'] !== '' ? (int) $validated['class_id'] : null;
        $sectionId = isset($validated['section_id']) && $validated['section_id'] !== '' ? (int) $validated['section_id'] : null;
        $gradeScaleId = isset($validated['grade_scale_id']) && $validated['grade_scale_id'] !== '' ? (int) $validated['grade_scale_id'] : null;

        $result = $this->resultGenerationService->generate($examId, $classId, $sectionId, $gradeScaleId);

        return response()->json($result, 201);
    }

    public function show(Request $request, ResultSummary $result_summary): JsonResponse
    {
        if (! $request->user()->hasPermission('view-results')) {
            abort(403, 'Unauthorized.');
        }

        $result_summary->load(['exam.academicSession', 'exam.examType', 'student', 'resultSubjectDetails.subject']);

        return response()->json([
            'id' => $result_summary->id,
            'exam' => $result_summary->exam ? [
                'id' => $result_summary->exam->id,
                'name' => $result_summary->exam->name,
                'academic_session_name' => $result_summary->exam->academicSession?->name,
                'exam_type_name' => $result_summary->exam->examType?->name,
            ] : null,
            'student' => $result_summary->student ? [
                'id' => $result_summary->student->id,
                'full_name' => $result_summary->student->full_name,
                'admission_no' => $result_summary->student->admission_no,
                'roll_no' => $result_summary->student->roll_no,
            ] : null,
            'total_marks' => $result_summary->total_marks,
            'obtained_marks' => $result_summary->obtained_marks,
            'gpa' => $result_summary->gpa,
            'letter_grade' => $result_summary->letter_grade,
            'merit_position' => $result_summary->merit_position,
            'pass_status' => $result_summary->pass_status,
            'published_status' => $result_summary->published_status,
            'generated_at' => $result_summary->generated_at?->toIso8601String(),
            'generated_at_formatted' => $this->dateTimeFormatter->formatDateTime($result_summary->generated_at),
            'remarks' => $result_summary->remarks,
            'subject_details' => $result_summary->resultSubjectDetails->map(fn ($d) => [
                'subject_id' => $d->subject_id,
                'subject_name' => $d->subject?->name,
                'full_marks' => $d->full_marks,
                'obtained_marks' => $d->obtained_marks,
                'grade_letter' => $d->grade_letter,
                'grade_point' => $d->grade_point,
                'pass_status' => $d->pass_status,
            ])->values()->all(),
        ]);
    }
}
