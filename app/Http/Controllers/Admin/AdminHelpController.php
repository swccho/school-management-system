<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminGuide;
use App\Models\AdminGuideCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminHelpController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-admin-guide')) {
            abort(403, 'Unauthorized.');
        }

        $categories = AdminGuideCategory::query()
            ->active()
            ->ordered()
            ->withCount(['guides' => fn ($q) => $q->published()])
            ->get()
            ->map(fn (AdminGuideCategory $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'icon' => $c->icon,
                'sort_order' => $c->sort_order,
                'guides_count' => $c->guides_count,
            ]);

        return response()->json($categories);
    }

    public function guides(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-admin-guide')) {
            abort(403, 'Unauthorized.');
        }

        $query = AdminGuide::query()
            ->published()
            ->ordered()
            ->with('category:id,name,slug');

        $query->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->input('category_id')));
        $query->when($request->filled('category_slug'), function ($q) use ($request) {
            $q->whereHas('category', fn ($sub) => $sub->where('slug', $request->input('category_slug')));
        });

        $guides = $query->get()->map(fn (AdminGuide $g) => [
            'id' => $g->id,
            'title' => $g->title,
            'slug' => $g->slug,
            'short_description' => $g->short_description,
            'category_id' => $g->category_id,
            'category' => $g->category ? ['id' => $g->category->id, 'name' => $g->category->name, 'slug' => $g->category->slug] : null,
        ]);

        return response()->json($guides);
    }

    public function guideBySlug(Request $request, string $slug): JsonResponse
    {
        if (! $request->user()->hasPermission('view-admin-guide')) {
            abort(403, 'Unauthorized.');
        }

        $guide = AdminGuide::query()
            ->published()
            ->where('slug', $slug)
            ->with('category')
            ->firstOrFail();

        return response()->json([
            'id' => $guide->id,
            'title' => $guide->title,
            'slug' => $guide->slug,
            'short_description' => $guide->short_description,
            'content' => $guide->content,
            'updated_at' => $guide->updated_at?->toIso8601String(),
            'category' => [
                'id' => $guide->category->id,
                'name' => $guide->category->name,
                'slug' => $guide->category->slug,
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-admin-guide')) {
            abort(403, 'Unauthorized.');
        }

        $q = $request->input('q', '');
        $q = trim($q);
        if ($q === '') {
            return response()->json([]);
        }

        $term = '%'.$q.'%';
        $guides = AdminGuide::query()
            ->published()
            ->with('category:id,name,slug')
            ->where(function ($query) use ($term) {
                $query->where('title', 'like', $term)
                    ->orWhere('short_description', 'like', $term)
                    ->orWhere('content', 'like', $term)
                    ->orWhereHas('category', fn ($q) => $q->where('name', 'like', $term));
            })
            ->ordered()
            ->limit(20)
            ->get()
            ->map(fn (AdminGuide $g) => [
                'id' => $g->id,
                'title' => $g->title,
                'slug' => $g->slug,
                'short_description' => $g->short_description,
                'category' => $g->category ? ['id' => $g->category->id, 'name' => $g->category->name, 'slug' => $g->category->slug] : null,
            ]);

        return response()->json($guides);
    }
}
