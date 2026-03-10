<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminGuideCategoryRequest;
use App\Http\Requests\Admin\UpdateAdminGuideCategoryRequest;
use App\Models\AdminGuideCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminGuideCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-admin-guide') && ! $request->user()->hasPermission('manage-admin-guide-categories')) {
            abort(403, 'Unauthorized.');
        }

        $categories = AdminGuideCategory::query()
            ->ordered()
            ->get()
            ->map(fn (AdminGuideCategory $c) => $this->categoryToArray($c));

        return response()->json($categories);
    }

    public function store(StoreAdminGuideCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? AdminGuideCategory::max('sort_order') ?? 0) + 1;
        $validated['status'] = $validated['status'] ?? 'active';

        $category = AdminGuideCategory::create($validated);

        return response()->json([
            'message' => 'Category created.',
            'category' => $this->categoryToArray($category),
        ], 201);
    }

    public function show(Request $request, AdminGuideCategory $admin_guide_category): JsonResponse
    {
        if (! $request->user()->hasPermission('view-admin-guide') && ! $request->user()->hasPermission('manage-admin-guide-categories')) {
            abort(403, 'Unauthorized.');
        }

        $admin_guide_category->loadCount('guides');

        return response()->json($this->categoryToArray($admin_guide_category));
    }

    public function update(UpdateAdminGuideCategoryRequest $request, AdminGuideCategory $admin_guide_category): JsonResponse
    {
        $validated = $request->validated();
        if (array_key_exists('name', $validated) && ! array_key_exists('slug', $validated)) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $admin_guide_category->update($validated);

        return response()->json([
            'message' => 'Category updated.',
            'category' => $this->categoryToArray($admin_guide_category->fresh()),
        ]);
    }

    public function destroy(Request $request, AdminGuideCategory $admin_guide_category): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-admin-guide-categories')) {
            abort(403, 'Unauthorized.');
        }

        $admin_guide_category->delete();

        return response()->json(['message' => 'Category deleted.']);
    }

    public function reorder(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-admin-guide-categories')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'category_ids' => ['required', 'array'],
            'category_ids.*' => ['integer', 'exists:admin_guide_categories,id'],
        ]);

        $ids = $request->input('category_ids');
        foreach ($ids as $order => $id) {
            AdminGuideCategory::where('id', $id)->update(['sort_order' => $order]);
        }

        return response()->json(['message' => 'Order updated.']);
    }

    private function categoryToArray(AdminGuideCategory $c): array
    {
        $arr = [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'description' => $c->description,
            'icon' => $c->icon,
            'sort_order' => $c->sort_order,
            'status' => $c->status,
        ];
        if (isset($c->guides_count)) {
            $arr['guides_count'] = $c->guides_count;
        }

        return $arr;
    }
}
