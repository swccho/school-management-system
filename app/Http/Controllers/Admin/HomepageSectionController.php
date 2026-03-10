<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHomepageSectionRequest;
use App\Http\Requests\Admin\UpdateHomepageSectionRequest;
use App\Models\HomepageSection;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomepageSectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-homepage-sections')) {
            abort(403, 'Unauthorized.');
        }

        $sections = HomepageSection::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (HomepageSection $s) => [
                'id' => $s->id,
                'school_id' => $s->school_id,
                'section_key' => $s->section_key,
                'title' => $s->title,
                'subtitle' => $s->subtitle,
                'content' => $s->content,
                'sort_order' => $s->sort_order,
                'is_visible' => $s->is_visible,
                'status' => $s->status,
                'created_at' => $s->created_at->toIso8601String(),
                'updated_at' => $s->updated_at->toIso8601String(),
            ]);

        return response()->json($sections);
    }

    public function store(StoreHomepageSectionRequest $request): JsonResponse
    {
        $school = School::first();
        $validated = $request->validated();
        $validated['school_id'] = $school?->id;
        $validated['is_visible'] = (bool) ($validated['is_visible'] ?? true);

        $maxOrder = HomepageSection::max('sort_order') ?? 0;
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? $maxOrder + 1);

        $section = HomepageSection::create($validated);

        return response()->json([
            'message' => 'Section created.',
            'homepage_section' => $this->sectionToArray($section),
        ], 201);
    }

    public function show(Request $request, HomepageSection $homepage_section): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-homepage-sections')) {
            abort(403, 'Unauthorized.');
        }

        return response()->json($this->sectionToArray($homepage_section));
    }

    public function update(UpdateHomepageSectionRequest $request, HomepageSection $homepage_section): JsonResponse
    {
        $validated = $request->validated();
        if (array_key_exists('is_visible', $validated)) {
            $validated['is_visible'] = (bool) $validated['is_visible'];
        }

        $homepage_section->update($validated);

        return response()->json([
            'message' => 'Section updated.',
            'homepage_section' => $this->sectionToArray($homepage_section),
        ]);
    }

    public function destroy(Request $request, HomepageSection $homepage_section): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-homepage-sections')) {
            abort(403, 'Unauthorized.');
        }

        $homepage_section->delete();

        return response()->json(['message' => 'Section deleted.']);
    }

    public function reorder(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-homepage-sections')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'section_ids' => ['required', 'array'],
            'section_ids.*' => ['integer', 'exists:homepage_sections,id'],
        ]);

        $ids = $request->input('section_ids');
        foreach ($ids as $order => $id) {
            HomepageSection::where('id', $id)->update(['sort_order' => $order]);
        }

        return response()->json(['message' => 'Order updated.']);
    }

    public function toggleVisibility(Request $request, HomepageSection $homepage_section): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-homepage-sections')) {
            abort(403, 'Unauthorized.');
        }

        $homepage_section->update(['is_visible' => ! $homepage_section->is_visible]);

        return response()->json([
            'message' => 'Visibility toggled.',
            'homepage_section' => $this->sectionToArray($homepage_section),
        ]);
    }

    private function sectionToArray(HomepageSection $s): array
    {
        return [
            'id' => $s->id,
            'school_id' => $s->school_id,
            'section_key' => $s->section_key,
            'title' => $s->title,
            'subtitle' => $s->subtitle,
            'content' => $s->content,
            'sort_order' => $s->sort_order,
            'is_visible' => $s->is_visible,
            'status' => $s->status,
            'created_at' => $s->created_at->toIso8601String(),
            'updated_at' => $s->updated_at->toIso8601String(),
        ];
    }
}
