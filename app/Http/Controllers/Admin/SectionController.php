<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSectionRequest;
use App\Http\Requests\Admin\UpdateSectionRequest;
use App\Models\School;
use App\Models\Section;
use App\Services\ActivityLogService;
use App\Services\DateTimeFormatter;
use App\Services\SectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter,
        private SectionService $sectionService,
        private ActivityLogService $activityLog
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-academic-setup')) {
            abort(403, 'Unauthorized.');
        }

        $query = Section::query()->with('schoolClass');
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->input('search');
            $q->where(function ($sub) use ($search) {
                $sub->where('sections.name', 'like', "%{$search}%")
                    ->orWhere('sections.code', 'like', "%{$search}%");
            });
        });
        $query->when(
            $request->filled('status') && in_array($request->input('status'), ['active', 'inactive', 'archived'], true),
            fn ($q) => $q->where('sections.status', $request->input('status'))
        );
        $sections = $query->ordered()
            ->get()
            ->map(fn (Section $s) => $this->sectionToArray($s));

        return response()->json($sections);
    }

    public function generateCode(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-sections')) {
            abort(403, 'Unauthorized.');
        }

        $schoolId = $request->input('school_id') ? (int) $request->input('school_id') : School::first()?->id;
        $excludeId = $request->input('exclude_id') ? (int) $request->input('exclude_id') : null;

        $code = $this->sectionService->generateCode($schoolId, $excludeId);

        return response()->json([
            'success' => true,
            'data' => ['code' => $code],
        ]);
    }

    public function store(StoreSectionRequest $request): JsonResponse
    {
        $school = School::first();
        $section = Section::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
        ]));

        $section->load('schoolClass');
        $this->activityLog->log('sections', 'create', Section::class, $section->id, "Section created: {$section->name}", [], $request);

        return response()->json([
            'message' => 'Section created.',
            'section' => $this->sectionToArray($section),
        ], 201);
    }

    public function show(Request $request, Section $section): JsonResponse
    {
        if (! $request->user()->hasPermission('view-academic-setup')) {
            abort(403, 'Unauthorized.');
        }

        $section->load('schoolClass');

        return response()->json($this->sectionToArray($section));
    }

    public function update(UpdateSectionRequest $request, Section $section): JsonResponse
    {
        $section->update($request->validated());
        $section->load('schoolClass');
        $this->activityLog->log('sections', 'update', Section::class, $section->id, "Section updated: {$section->name}", [], $request);

        return response()->json([
            'message' => 'Section updated.',
            'section' => $this->sectionToArray($section->fresh()),
        ]);
    }

    private function sectionToArray(Section $s): array
    {
        return [
            'id' => $s->id,
            'class_id' => $s->class_id,
            'class_name' => $s->schoolClass?->name,
            'name' => $s->name,
            'code' => $s->code,
            'room_no' => $s->room_no,
            'capacity' => $s->capacity,
            'description' => $s->description,
            'status' => $s->status,
            'created_at' => $s->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($s->created_at),
            'updated_at' => $s->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($s->updated_at),
        ];
    }
}
