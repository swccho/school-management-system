<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExamRequest;
use App\Http\Requests\Admin\UpdateExamRequest;
use App\Models\Exam;
use App\Models\ExamSubjectConfig;
use App\Services\DateTimeFormatter;
use App\Services\ExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function __construct(
        private ExamService $examService,
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-exams')) {
            abort(403, 'Unauthorized.');
        }

        $query = Exam::query()->with(['academicSession', 'examType']);

        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->input('academic_session_id'));
        }
        if ($request->filled('exam_type_id')) {
            $query->where('exam_type_id', $request->input('exam_type_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $exams = $query->orderByDesc('start_date')
            ->orderBy('name')
            ->get()
            ->map(fn (Exam $e) => $this->examToArray($e));

        return response()->json($exams);
    }

    public function store(StoreExamRequest $request): JsonResponse
    {
        $exam = $this->examService->createExamWithConfigs($request->validated());

        return response()->json([
            'message' => 'Exam created.',
            'exam' => $this->examToArray($exam, true),
        ], 201);
    }

    public function show(Request $request, Exam $exam): JsonResponse
    {
        if (! $request->user()->hasPermission('view-exams')) {
            abort(403, 'Unauthorized.');
        }

        $exam->load([
            'examClassConfigs.schoolClass', 'examClassConfigs.section',
            'examSubjectConfigs.subject', 'examSubjectConfigs.schoolClass', 'examSubjectConfigs.markComponents',
            'academicSession', 'examType',
        ]);

        return response()->json($this->examToArray($exam, true));
    }

    public function update(UpdateExamRequest $request, Exam $exam): JsonResponse
    {
        $exam = $this->examService->updateExamWithConfigs($exam, $request->validated());

        return response()->json([
            'message' => 'Exam updated.',
            'exam' => $this->examToArray($exam, true),
        ]);
    }

    private function examToArray(Exam $e, bool $withConfigs = false): array
    {
        $arr = [
            'id' => $e->id,
            'academic_session_id' => $e->academic_session_id,
            'academic_session_name' => $e->academicSession?->name,
            'exam_type_id' => $e->exam_type_id,
            'exam_type_name' => $e->examType?->name,
            'name' => $e->name,
            'code' => $e->code,
            'start_date' => $e->start_date?->format('Y-m-d'),
            'start_date_formatted' => $this->dateTimeFormatter->formatDate($e->start_date),
            'end_date' => $e->end_date?->format('Y-m-d'),
            'end_date_formatted' => $this->dateTimeFormatter->formatDate($e->end_date),
            'result_publish_date' => $e->result_publish_date?->format('Y-m-d'),
            'result_publish_date_formatted' => $this->dateTimeFormatter->formatDate($e->result_publish_date),
            'status' => $e->status,
            'description' => $e->description,
            'created_at' => $e->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($e->created_at),
            'updated_at' => $e->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($e->updated_at),
        ];

        if ($withConfigs && $e->relationLoaded('examClassConfigs')) {
            $arr['class_configs'] = $e->examClassConfigs->map(fn ($c) => [
                'id' => $c->id,
                'class_id' => $c->class_id,
                'class_name' => $c->schoolClass?->name,
                'section_id' => $c->section_id,
                'section_name' => $c->section?->name,
                'status' => $c->status,
            ])->values()->all();
        }

        if ($withConfigs && $e->relationLoaded('examSubjectConfigs')) {
            $arr['subject_configs'] = $e->examSubjectConfigs->sortBy('sort_order')->values()->map(fn (ExamSubjectConfig $s) => [
                'id' => $s->id,
                'class_id' => $s->class_id,
                'class_name' => $s->schoolClass?->name,
                'subject_id' => $s->subject_id,
                'subject_name' => $s->subject?->name,
                'full_marks' => $s->full_marks,
                'pass_marks' => $s->pass_marks,
                'theory_marks' => $s->theory_marks,
                'practical_marks' => $s->practical_marks,
                'oral_marks' => $s->oral_marks,
                'has_practical' => $s->has_practical,
                'sort_order' => $s->sort_order,
                'mark_components' => $s->relationLoaded('markComponents')
                    ? $s->markComponents->sortBy('sort_order')->values()->map(fn ($m) => [
                        'id' => $m->id,
                        'name' => $m->name,
                        'component_type' => $m->component_type,
                        'marks' => $m->marks,
                        'pass_marks' => $m->pass_marks,
                        'sort_order' => $m->sort_order,
                    ])->all()
                    : [],
            ])->all();
        }

        return $arr;
    }
}
