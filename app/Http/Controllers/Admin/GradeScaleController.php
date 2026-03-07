<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGradeScaleRequest;
use App\Http\Requests\Admin\UpdateGradeScaleRequest;
use App\Models\GradeScale;
use App\Models\School;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeScaleController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-results')) {
            abort(403, 'Unauthorized.');
        }

        $scales = GradeScale::with('gradeScaleItems')
            ->orderBy('name')
            ->get()
            ->map(fn (GradeScale $g) => $this->gradeScaleToArray($g));

        return response()->json($scales);
    }

    public function store(StoreGradeScaleRequest $request): JsonResponse
    {
        $school = School::first();
        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $gradeScale = GradeScale::create(array_merge($validated, [
            'school_id' => $school?->id,
            'status' => $validated['status'] ?? 'active',
        ]));

        if ($gradeScale->is_default) {
            GradeScale::where('id', '!=', $gradeScale->id)->update(['is_default' => false]);
        }

        $this->syncItems($gradeScale, $items, $school);

        return response()->json([
            'message' => 'Grade scale created.',
            'grade_scale' => $this->gradeScaleToArray($gradeScale->load('gradeScaleItems')),
        ], 201);
    }

    public function show(Request $request, GradeScale $grade_scale): JsonResponse
    {
        if (! $request->user()->hasPermission('view-results')) {
            abort(403, 'Unauthorized.');
        }

        return response()->json($this->gradeScaleToArray($grade_scale->load('gradeScaleItems')));
    }

    public function update(UpdateGradeScaleRequest $request, GradeScale $grade_scale): JsonResponse
    {
        $school = School::first();
        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $grade_scale->update(array_merge($validated, [
            'status' => $validated['status'] ?? $grade_scale->status,
        ]));

        if ($grade_scale->is_default) {
            GradeScale::where('id', '!=', $grade_scale->id)->update(['is_default' => false]);
        }

        $this->syncItems($grade_scale, $items, $school);

        return response()->json([
            'message' => 'Grade scale updated.',
            'grade_scale' => $this->gradeScaleToArray($grade_scale->fresh()->load('gradeScaleItems')),
        ]);
    }

    private function syncItems(GradeScale $gradeScale, array $items, ?School $school): void
    {
        $gradeScale->gradeScaleItems()->delete();
        foreach ($items as $item) {
            $gradeScale->gradeScaleItems()->create([
                'school_id' => $school?->id,
                'min_mark' => $item['min_mark'],
                'max_mark' => $item['max_mark'],
                'letter_grade' => $item['letter_grade'],
                'grade_point' => $item['grade_point'],
                'remarks' => $item['remarks'] ?? null,
            ]);
        }
    }

    private function gradeScaleToArray(GradeScale $g): array
    {
        return [
            'id' => $g->id,
            'name' => $g->name,
            'is_default' => $g->is_default,
            'status' => $g->status,
            'items' => $g->gradeScaleItems->map(fn ($i) => [
                'id' => $i->id,
                'min_mark' => (float) $i->min_mark,
                'max_mark' => (float) $i->max_mark,
                'letter_grade' => $i->letter_grade,
                'grade_point' => (float) $i->grade_point,
                'remarks' => $i->remarks,
            ])->values()->all(),
            'created_at' => $g->created_at?->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($g->created_at),
            'updated_at' => $g->updated_at?->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($g->updated_at),
        ];
    }
}
