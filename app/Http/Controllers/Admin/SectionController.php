<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSectionRequest;
use App\Http\Requests\Admin\UpdateSectionRequest;
use App\Models\School;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-academic-setup')) {
            abort(403, 'Unauthorized.');
        }

        $sections = Section::query()
            ->with('schoolClass')
            ->ordered()
            ->get()
            ->map(fn (Section $s) => $this->sectionToArray($s));

        return response()->json($sections);
    }

    public function store(StoreSectionRequest $request): JsonResponse
    {
        $school = School::first();
        $section = Section::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
        ]));

        $section->load('schoolClass');

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
            'updated_at' => $s->updated_at->toIso8601String(),
        ];
    }
}
