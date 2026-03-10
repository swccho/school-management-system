<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DownloadCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DownloadCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-downloads')) {
            abort(403, 'Unauthorized.');
        }

        $query = DownloadCategory::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('name');

        $categories = $query->get()->map(fn (DownloadCategory $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'status' => $c->status,
        ]);

        return response()->json($categories);
    }
}
