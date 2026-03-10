<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;
use App\Models\School;
use App\Services\ActivityLogService;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter,
        private ActivityLogService $activityLog
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-pages')) {
            abort(403, 'Unauthorized.');
        }

        $query = Page::query();

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%'.$request->input('search').'%';
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('content', 'like', $term);
            });
        });
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')));
        $query->when($request->filled('page_type'), fn ($q) => $q->where('page_type', $request->input('page_type')));
        $query->when($request->filled('published_from'), fn ($q) => $q->whereDate('published_at', '>=', $request->input('published_from')));
        $query->when($request->filled('published_to'), fn ($q) => $q->whereDate('published_at', '<=', $request->input('published_to')));

        $pages = $query->orderByDesc('published_at')->orderBy('title')->get()
            ->map(fn (Page $p) => $this->pageToArray($p));

        return response()->json($pages);
    }

    public function store(StorePageRequest $request): JsonResponse
    {
        $school = School::first();
        $schoolId = $school?->id;
        $userId = $request->user()->id;

        $validated = $request->validated();
        $validated['school_id'] = $schoolId;
        $validated['created_by'] = $userId;
        $validated['updated_by'] = $userId;

        $featuredImage = $request->file('featured_image');
        if ($featuredImage) {
            $validated['featured_image'] = $featuredImage->store('pages', 'public');
        }

        $page = Page::create($validated);
        $this->activityLog->log('pages', 'create', Page::class, $page->id, "Page created: {$page->title}", [], $request);

        return response()->json([
            'message' => 'Page created.',
            'page' => $this->pageToArray($page, true),
        ], 201);
    }

    public function show(Request $request, Page $page): JsonResponse
    {
        if (! $request->user()->hasPermission('view-pages')) {
            abort(403, 'Unauthorized.');
        }

        return response()->json($this->pageToArray($page, true));
    }

    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        $validated = $request->validated();
        $validated['updated_by'] = $request->user()->id;

        $featuredImage = $request->file('featured_image');
        if ($featuredImage) {
            if ($page->featured_image) {
                Storage::disk('public')->delete($page->featured_image);
            }
            $validated['featured_image'] = $featuredImage->store('pages', 'public');
        }

        $page->update($validated);
        $this->activityLog->log('pages', 'update', Page::class, $page->id, "Page updated: {$page->title}", [], $request);

        return response()->json([
            'message' => 'Page updated.',
            'page' => $this->pageToArray($page, true),
        ]);
    }

    public function destroy(Request $request, Page $page): JsonResponse
    {
        if (! $request->user()->hasPermission('delete-pages')) {
            abort(403, 'Unauthorized.');
        }

        if ($page->featured_image) {
            Storage::disk('public')->delete($page->featured_image);
        }
        $pageId = $page->id;
        $title = $page->title;
        $page->delete();
        $this->activityLog->log('pages', 'delete', Page::class, $pageId, "Page deleted: {$title}", [], $request);

        return response()->json(['message' => 'Page deleted.']);
    }

    public function publish(Request $request, Page $page): JsonResponse
    {
        if (! $request->user()->hasPermission('publish-pages')) {
            abort(403, 'Unauthorized.');
        }

        $page->update([
            'status' => 'published',
            'published_at' => $page->published_at ?? now(),
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Page published.',
            'page' => $this->pageToArray($page, true),
        ]);
    }

    public function unpublish(Request $request, Page $page): JsonResponse
    {
        if (! $request->user()->hasPermission('publish-pages')) {
            abort(403, 'Unauthorized.');
        }

        $page->update([
            'status' => 'draft',
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Page unpublished.',
            'page' => $this->pageToArray($page, true),
        ]);
    }

    private function pageToArray(Page $p, bool $withImageUrl = false): array
    {
        $arr = [
            'id' => $p->id,
            'school_id' => $p->school_id,
            'title' => $p->title,
            'slug' => $p->slug,
            'page_type' => $p->page_type,
            'meta_title' => $p->meta_title,
            'meta_description' => $p->meta_description,
            'content' => $p->content,
            'featured_image' => $p->featured_image,
            'status' => $p->status,
            'published_at' => $p->published_at?->toIso8601String(),
            'published_at_formatted' => $this->dateTimeFormatter->formatDateTime($p->published_at),
            'created_at' => $p->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($p->created_at),
            'updated_at' => $p->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($p->updated_at),
        ];

        if ($withImageUrl && $p->featured_image) {
            $arr['featured_image_url'] = Storage::disk('public')->url($p->featured_image);
        }

        return $arr;
    }
}
