<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NoticeCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoticeCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-notices')) {
            abort(403, 'Unauthorized.');
        }

        $query = NoticeCategory::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('name');

        $categories = $query->get()->map(fn (NoticeCategory $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'status' => $c->status,
        ]);

        return response()->json($categories);
    }
}
