<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        $query = Event::query()
            ->where('school_id', $schoolId)
            ->where('status', 'published')
            ->with('category')
            ->orderBy('start_datetime');

        if ($request->filled('date_from')) {
            $query->where('start_datetime', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('start_datetime', '<=', $request->date_to . ' 23:59:59');
        }

        $events = $query->limit(100)->get()->map(fn ($e) => [
            'id' => $e->id,
            'title' => $e->title,
            'slug' => $e->slug,
            'summary' => $e->summary,
            'description' => $e->description,
            'category' => $e->category ? ['id' => $e->category->id, 'name' => $e->category->name] : null,
            'start_datetime' => $e->start_datetime?->toIso8601String(),
            'end_datetime' => $e->end_datetime?->toIso8601String(),
            'location' => $e->location,
        ]);

        return response()->json($events);
    }
}
