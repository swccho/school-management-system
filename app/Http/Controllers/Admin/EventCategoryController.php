<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-events')) {
            abort(403, 'Unauthorized.');
        }

        $query = EventCategory::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('name');

        $categories = $query->get()->map(fn (EventCategory $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'status' => $c->status,
        ]);

        return response()->json($categories);
    }
}
