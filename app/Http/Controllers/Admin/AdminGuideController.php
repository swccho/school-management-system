<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminGuideRequest;
use App\Http\Requests\Admin\UpdateAdminGuideRequest;
use App\Models\AdminGuide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminGuideController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-admin-guides')) {
            abort(403, 'Unauthorized.');
        }

        $query = AdminGuide::query()->with('category');

        $query->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->input('category_id')));
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')));
        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%'.$request->input('search').'%';
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)
                    ->orWhere('short_description', 'like', $term)
                    ->orWhere('content', 'like', $term);
            });
        });

        $guides = $query->ordered()->get()->map(fn (AdminGuide $g) => $this->guideToArray($g));

        return response()->json($guides);
    }

    public function store(StoreAdminGuideRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? $this->generateUniqueSlug($validated['title'], null);
        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;
        $validated['status'] = $validated['status'] ?? 'draft';
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $guide = AdminGuide::create($validated);
        $guide->load('category');

        return response()->json([
            'message' => 'Guide created.',
            'guide' => $this->guideToArray($guide),
        ], 201);
    }

    public function show(Request $request, AdminGuide $admin_guide): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-admin-guides')) {
            abort(403, 'Unauthorized.');
        }

        $admin_guide->load('category');

        return response()->json($this->guideToArray($admin_guide));
    }

    public function update(UpdateAdminGuideRequest $request, AdminGuide $admin_guide): JsonResponse
    {
        $validated = $request->validated();
        $validated['updated_by'] = $request->user()->id;
        if (array_key_exists('title', $validated) && ! array_key_exists('slug', $validated)) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $admin_guide->id);
        }

        $admin_guide->update($validated);
        $admin_guide->load('category');

        return response()->json([
            'message' => 'Guide updated.',
            'guide' => $this->guideToArray($admin_guide->fresh()),
        ]);
    }

    public function destroy(Request $request, AdminGuide $admin_guide): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-admin-guides')) {
            abort(403, 'Unauthorized.');
        }

        $admin_guide->delete();

        return response()->json(['message' => 'Guide deleted.']);
    }

    private function generateUniqueSlug(string $title, ?int $excludeId): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $n = 1;
        while (AdminGuide::query()->where('slug', $slug)->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $base.'-'.(++$n);
        }

        return $slug;
    }

    private function guideToArray(AdminGuide $g): array
    {
        return [
            'id' => $g->id,
            'category_id' => $g->category_id,
            'category' => $g->relationLoaded('category') ? [
                'id' => $g->category->id,
                'name' => $g->category->name,
                'slug' => $g->category->slug,
            ] : null,
            'title' => $g->title,
            'slug' => $g->slug,
            'short_description' => $g->short_description,
            'content' => $g->content,
            'status' => $g->status,
            'sort_order' => $g->sort_order,
            'created_by' => $g->created_by,
            'updated_by' => $g->updated_by,
            'created_at' => $g->created_at?->toIso8601String(),
            'updated_at' => $g->updated_at?->toIso8601String(),
        ];
    }
}
