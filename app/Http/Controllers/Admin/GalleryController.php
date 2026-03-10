<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGalleryRequest;
use App\Http\Requests\Admin\UpdateGalleryRequest;
use App\Models\Gallery;
use App\Models\School;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-gallery')) {
            abort(403, 'Unauthorized.');
        }

        $query = Gallery::query()->withCount('items');

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%'.$request->input('search').'%';
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)->orWhere('description', 'like', $term);
            });
        });
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')));
        $query->when($request->filled('published_from'), fn ($q) => $q->whereDate('published_at', '>=', $request->input('published_from')));
        $query->when($request->filled('published_to'), fn ($q) => $q->whereDate('published_at', '<=', $request->input('published_to')));
        $query->when($request->filled('gallery_type'), fn ($q) => $q->where('gallery_type', $request->input('gallery_type')));

        $galleries = $query->orderByDesc('published_at')->orderBy('title')->get()
            ->map(fn (Gallery $g) => $this->galleryToArray($g));

        return response()->json($galleries);
    }

    public function store(StoreGalleryRequest $request): JsonResponse
    {
        $school = School::first();
        $schoolId = $school?->id;
        $userId = $request->user()->id;

        $validated = $request->validated();
        $validated['slug'] = $this->generateUniqueSlug($validated['title'], $schoolId, null);
        $validated['school_id'] = $schoolId;
        $validated['created_by'] = $userId;
        $validated['updated_by'] = $userId;

        $coverImage = $request->file('cover_image');
        if ($coverImage) {
            $validated['cover_image'] = $coverImage->store('galleries/covers', 'public');
        }

        $gallery = Gallery::create($validated);

        return response()->json([
            'message' => 'Gallery created.',
            'gallery' => $this->galleryToArray($gallery, true),
        ], 201);
    }

    public function show(Request $request, Gallery $gallery): JsonResponse
    {
        if (! $request->user()->hasPermission('view-gallery')) {
            abort(403, 'Unauthorized.');
        }

        $gallery->load('items');

        return response()->json($this->galleryToArray($gallery, true));
    }

    public function update(UpdateGalleryRequest $request, Gallery $gallery): JsonResponse
    {
        $validated = $request->validated();
        $validated['updated_by'] = $request->user()->id;

        if (isset($validated['title']) && $validated['title'] !== $gallery->title) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $gallery->school_id, $gallery->id);
        }

        $coverImage = $request->file('cover_image');
        if ($coverImage) {
            if ($gallery->cover_image) {
                Storage::disk('public')->delete($gallery->cover_image);
            }
            $validated['cover_image'] = $coverImage->store('galleries/covers', 'public');
        }

        $gallery->update($validated);
        $gallery->load('items');

        return response()->json([
            'message' => 'Gallery updated.',
            'gallery' => $this->galleryToArray($gallery, true),
        ]);
    }

    public function destroy(Request $request, Gallery $gallery): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-gallery')) {
            abort(403, 'Unauthorized.');
        }

        foreach ($gallery->items as $item) {
            Storage::disk('public')->delete($item->file_path);
        }
        if ($gallery->cover_image) {
            Storage::disk('public')->delete($gallery->cover_image);
        }
        $gallery->delete();

        return response()->json(['message' => 'Gallery deleted.']);
    }

    public function publish(Request $request, Gallery $gallery): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-gallery')) {
            abort(403, 'Unauthorized.');
        }

        $gallery->update([
            'status' => 'published',
            'published_at' => $gallery->published_at ?? now(),
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Gallery published.',
            'gallery' => $this->galleryToArray($gallery->load('items'), true),
        ]);
    }

    public function unpublish(Request $request, Gallery $gallery): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-gallery')) {
            abort(403, 'Unauthorized.');
        }

        $gallery->update([
            'status' => 'draft',
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Gallery unpublished.',
            'gallery' => $this->galleryToArray($gallery->load('items'), true),
        ]);
    }

    private function generateUniqueSlug(string $title, ?int $schoolId, ?int $excludeId): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $n = 1;
        $query = Gallery::query()
            ->when($schoolId !== null, fn ($q) => $q->where('school_id', $schoolId))
            ->when($schoolId === null, fn ($q) => $q->whereNull('school_id'))
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId));

        while ($query->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$n);
        }

        return $slug;
    }

    private function galleryToArray(Gallery $g, bool $withItems = false): array
    {
        $arr = [
            'id' => $g->id,
            'school_id' => $g->school_id,
            'title' => $g->title,
            'slug' => $g->slug,
            'description' => $g->description,
            'cover_image' => $g->cover_image,
            'gallery_type' => $g->gallery_type,
            'published_at' => $g->published_at?->toIso8601String(),
            'published_at_formatted' => $this->dateTimeFormatter->formatDateTime($g->published_at),
            'status' => $g->status,
            'items_count' => $g->items_count ?? $g->items()->count(),
            'created_at' => $g->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($g->created_at),
            'updated_at' => $g->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($g->updated_at),
        ];

        if ($g->cover_image) {
            $arr['cover_image_url'] = Storage::disk('public')->url($g->cover_image);
        }

        if ($withItems && $g->relationLoaded('items')) {
            $arr['items'] = $g->items->map(fn ($item) => [
                'id' => $item->id,
                'gallery_id' => $item->gallery_id,
                'media_type' => $item->media_type,
                'file_path' => $item->file_path,
                'url' => Storage::disk('public')->url($item->file_path),
                'caption' => $item->caption,
                'sort_order' => $item->sort_order,
                'status' => $item->status,
            ])->values()->all();
        }

        return $arr;
    }
}
