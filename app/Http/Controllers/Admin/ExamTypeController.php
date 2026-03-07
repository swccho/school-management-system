<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExamTypeRequest;
use App\Http\Requests\Admin\UpdateExamTypeRequest;
use App\Models\ExamType;
use App\Models\School;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamTypeController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-exams')) {
            abort(403, 'Unauthorized.');
        }

        $types = ExamType::query()
            ->orderBy('name')
            ->get()
            ->map(fn (ExamType $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'code' => $t->code,
                'description' => $t->description,
                'status' => $t->status,
                'created_at' => $t->created_at->toIso8601String(),
                'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($t->created_at),
                'updated_at' => $t->updated_at->toIso8601String(),
                'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($t->updated_at),
            ]);

        return response()->json($types);
    }

    public function store(StoreExamTypeRequest $request): JsonResponse
    {
        $school = School::first();
        $type = ExamType::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
            'status' => $request->input('status', 'active'),
        ]));

        return response()->json([
            'message' => 'Exam type created.',
            'exam_type' => [
                'id' => $type->id,
                'name' => $type->name,
                'code' => $type->code,
                'description' => $type->description,
                'status' => $type->status,
            ],
        ], 201);
    }

    public function update(UpdateExamTypeRequest $request, ExamType $exam_type): JsonResponse
    {
        $exam_type->update($request->validated());

        return response()->json([
            'message' => 'Exam type updated.',
            'exam_type' => [
                'id' => $exam_type->id,
                'name' => $exam_type->name,
                'code' => $exam_type->code,
                'description' => $exam_type->description,
                'status' => $exam_type->status,
            ],
        ]);
    }
}
