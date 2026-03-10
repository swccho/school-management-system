<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Models\Event;
use App\Models\School;
use App\Services\ActivityLogService;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter,
        private ActivityLogService $activityLog
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-events')) {
            abort(403, 'Unauthorized.');
        }

        $query = Event::query()->with(['category']);

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%'.$request->input('search').'%';
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)
                    ->orWhere('summary', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('location', 'like', $term);
            });
        });
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')));
        $query->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->input('category_id')));
        $query->when($request->filled('event_start_from'), fn ($q) => $q->whereDate('start_datetime', '>=', $request->input('event_start_from')));
        $query->when($request->filled('event_start_to'), fn ($q) => $q->whereDate('start_datetime', '<=', $request->input('event_start_to')));
        $query->when($request->filled('is_featured'), fn ($q) => $q->where('is_featured', filter_var($request->input('is_featured'), FILTER_VALIDATE_BOOLEAN)));

        $events = $query->orderBy('start_datetime')->orderBy('title')->get()
            ->map(fn (Event $e) => $this->eventToArray($e));

        return response()->json($events);
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $school = School::first();
        $schoolId = $school?->id;
        $userId = $request->user()->id;

        $validated = $request->validated();
        $validated['slug'] = $this->generateUniqueSlug($validated['title'], $schoolId, null);
        $validated['school_id'] = $schoolId;
        $validated['created_by'] = $userId;
        $validated['updated_by'] = $userId;
        $validated['is_featured'] = (bool) ($validated['is_featured'] ?? false);

        $featuredImage = $request->file('featured_image');
        if ($featuredImage) {
            $validated['featured_image'] = $featuredImage->store('events', 'public');
        }

        $event = Event::create($validated);
        $this->activityLog->log('events', 'create', Event::class, $event->id, "Event created: {$event->title}", [], $request);

        return response()->json([
            'message' => 'Event created.',
            'event' => $this->eventToArray($event->load('category'), true),
        ], 201);
    }

    public function show(Request $request, Event $event): JsonResponse
    {
        if (! $request->user()->hasPermission('view-events')) {
            abort(403, 'Unauthorized.');
        }

        $event->load('category');

        return response()->json($this->eventToArray($event, true));
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $validated = $request->validated();
        $validated['updated_by'] = $request->user()->id;

        if (isset($validated['title']) && $validated['title'] !== $event->title) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $event->school_id, $event->id);
        }
        if (array_key_exists('is_featured', $validated)) {
            $validated['is_featured'] = (bool) $validated['is_featured'];
        }

        $featuredImage = $request->file('featured_image');
        if ($featuredImage) {
            if ($event->featured_image) {
                Storage::disk('public')->delete($event->featured_image);
            }
            $validated['featured_image'] = $featuredImage->store('events', 'public');
        }

        $event->update($validated);
        $event->load('category');
        $this->activityLog->log('events', 'update', Event::class, $event->id, "Event updated: {$event->title}", [], $request);

        return response()->json([
            'message' => 'Event updated.',
            'event' => $this->eventToArray($event, true),
        ]);
    }

    public function destroy(Request $request, Event $event): JsonResponse
    {
        if (! $request->user()->hasPermission('delete-events')) {
            abort(403, 'Unauthorized.');
        }

        if ($event->featured_image) {
            Storage::disk('public')->delete($event->featured_image);
        }
        $eventId = $event->id;
        $title = $event->title;
        $event->delete();
        $this->activityLog->log('events', 'delete', Event::class, $eventId, "Event deleted: {$title}", [], $request);

        return response()->json(['message' => 'Event deleted.']);
    }

    public function publish(Request $request, Event $event): JsonResponse
    {
        if (! $request->user()->hasPermission('publish-events')) {
            abort(403, 'Unauthorized.');
        }

        $event->update([
            'status' => 'published',
            'updated_by' => $request->user()->id,
        ]);
        $event->load('category');

        return response()->json([
            'message' => 'Event published.',
            'event' => $this->eventToArray($event, true),
        ]);
    }

    public function unpublish(Request $request, Event $event): JsonResponse
    {
        if (! $request->user()->hasPermission('publish-events')) {
            abort(403, 'Unauthorized.');
        }

        $event->update([
            'status' => 'draft',
            'updated_by' => $request->user()->id,
        ]);
        $event->load('category');

        return response()->json([
            'message' => 'Event unpublished.',
            'event' => $this->eventToArray($event, true),
        ]);
    }

    private function generateUniqueSlug(string $title, ?int $schoolId, ?int $excludeId): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $n = 1;
        $query = Event::query()
            ->when($schoolId !== null, fn ($q) => $q->where('school_id', $schoolId))
            ->when($schoolId === null, fn ($q) => $q->whereNull('school_id'))
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId));

        while ($query->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$n);
        }

        return $slug;
    }

    private function eventToArray(Event $e, bool $withImageUrl = false): array
    {
        $arr = [
            'id' => $e->id,
            'school_id' => $e->school_id,
            'title' => $e->title,
            'slug' => $e->slug,
            'summary' => $e->summary,
            'description' => $e->description,
            'category_id' => $e->category_id,
            'category_name' => $e->category?->name,
            'start_datetime' => $e->start_datetime?->toIso8601String(),
            'start_datetime_formatted' => $this->dateTimeFormatter->formatDateTime($e->start_datetime),
            'end_datetime' => $e->end_datetime?->toIso8601String(),
            'end_datetime_formatted' => $this->dateTimeFormatter->formatDateTime($e->end_datetime),
            'location' => $e->location,
            'featured_image' => $e->featured_image,
            'status' => $e->status,
            'is_featured' => $e->is_featured,
            'created_at' => $e->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($e->created_at),
            'updated_at' => $e->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($e->updated_at),
        ];

        if ($withImageUrl && $e->featured_image) {
            $arr['featured_image_url'] = Storage::disk('public')->url($e->featured_image);
        }

        return $arr;
    }
}
