<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-news-posts')) {
            abort(403, 'Unauthorized.');
        }

        $query = NewsCategory::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('name');

        $categories = $query->get()->map(fn (NewsCategory $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'status' => $c->status,
        ]);

        return response()->json($categories);
    }
}
