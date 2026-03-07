<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Http\Requests\Admin\UpdateSubjectRequest;
use App\Models\School;
use App\Models\Subject;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-academic-setup')) {
            abort(403, 'Unauthorized.');
        }

        $subjects = Subject::query()
            ->ordered()
            ->get()
            ->map(fn (Subject $s) => $this->subjectToArray($s));

        return response()->json($subjects);
    }

    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $school = School::first();
        $subject = Subject::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
        ]));

        return response()->json([
            'message' => 'Subject created.',
            'subject' => $this->subjectToArray($subject),
        ], 201);
    }

    public function show(Request $request, Subject $subject): JsonResponse
    {
        if (! $request->user()->hasPermission('view-academic-setup')) {
            abort(403, 'Unauthorized.');
        }

        return response()->json($this->subjectToArray($subject));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): JsonResponse
    {
        $subject->update($request->validated());

        return response()->json([
            'message' => 'Subject updated.',
            'subject' => $this->subjectToArray($subject->fresh()),
        ]);
    }

    private function subjectToArray(Subject $s): array
    {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'code' => $s->code,
            'short_name' => $s->short_name,
            'type' => $s->type,
            'is_optional' => $s->is_optional,
            'has_practical' => $s->has_practical,
            'full_marks' => $s->full_marks,
            'pass_marks' => $s->pass_marks,
            'description' => $s->description,
            'status' => $s->status,
            'created_at' => $s->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($s->created_at),
            'updated_at' => $s->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($s->updated_at),
        ];
    }
}
