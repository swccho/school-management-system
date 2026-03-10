<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNewsPostRequest;
use App\Http\Requests\Admin\UpdateNewsPostRequest;
use App\Models\NewsPost;
use App\Models\School;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsPostController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-news-posts')) {
            abort(403, 'Unauthorized.');
        }

        $query = NewsPost::query()->with(['category']);

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%'.$request->input('search').'%';
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)
                    ->orWhere('summary', 'like', $term)
                    ->orWhere('content', 'like', $term);
            });
        });
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')));
        $query->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->input('category_id')));
        $query->when($request->filled('published_from'), fn ($q) => $q->whereDate('published_at', '>=', $request->input('published_from')));
        $query->when($request->filled('published_to'), fn ($q) => $q->whereDate('published_at', '<=', $request->input('published_to')));
        $query->when($request->filled('is_featured'), fn ($q) => $q->where('is_featured', filter_var($request->input('is_featured'), FILTER_VALIDATE_BOOLEAN)));

        $posts = $query->orderByDesc('published_at')->orderBy('title')->get()
            ->map(fn (NewsPost $n) => $this->newsPostToArray($n));

        return response()->json($posts);
    }

    public function store(StoreNewsPostRequest $request): JsonResponse
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
            $validated['featured_image'] = $featuredImage->store('news', 'public');
        }

        $post = NewsPost::create($validated);

        return response()->json([
            'message' => 'News post created.',
            'news_post' => $this->newsPostToArray($post->load('category'), true),
        ], 201);
    }

    public function show(Request $request, NewsPost $newsPost): JsonResponse
    {
        if (! $request->user()->hasPermission('view-news-posts')) {
            abort(403, 'Unauthorized.');
        }

        $newsPost->load('category');

        return response()->json($this->newsPostToArray($newsPost, true));
    }

    public function update(UpdateNewsPostRequest $request, NewsPost $newsPost): JsonResponse
    {
        $validated = $request->validated();
        $validated['updated_by'] = $request->user()->id;

        if (isset($validated['title']) && $validated['title'] !== $newsPost->title) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $newsPost->school_id, $newsPost->id);
        }
        if (array_key_exists('is_featured', $validated)) {
            $validated['is_featured'] = (bool) $validated['is_featured'];
        }

        $featuredImage = $request->file('featured_image');
        if ($featuredImage) {
            if ($newsPost->featured_image) {
                Storage::disk('public')->delete($newsPost->featured_image);
            }
            $validated['featured_image'] = $featuredImage->store('news', 'public');
        }

        $newsPost->update($validated);
        $newsPost->load('category');

        return response()->json([
            'message' => 'News post updated.',
            'news_post' => $this->newsPostToArray($newsPost, true),
        ]);
    }

    public function destroy(Request $request, NewsPost $newsPost): JsonResponse
    {
        if (! $request->user()->hasPermission('delete-news-posts')) {
            abort(403, 'Unauthorized.');
        }

        if ($newsPost->featured_image) {
            Storage::disk('public')->delete($newsPost->featured_image);
        }
        $newsPost->delete();

        return response()->json(['message' => 'News post deleted.']);
    }

    public function publish(Request $request, NewsPost $newsPost): JsonResponse
    {
        if (! $request->user()->hasPermission('publish-news-posts')) {
            abort(403, 'Unauthorized.');
        }

        $newsPost->update([
            'status' => 'published',
            'published_at' => $newsPost->published_at ?? now(),
            'updated_by' => $request->user()->id,
        ]);
        $newsPost->load('category');

        return response()->json([
            'message' => 'News post published.',
            'news_post' => $this->newsPostToArray($newsPost, true),
        ]);
    }

    public function unpublish(Request $request, NewsPost $newsPost): JsonResponse
    {
        if (! $request->user()->hasPermission('publish-news-posts')) {
            abort(403, 'Unauthorized.');
        }

        $newsPost->update([
            'status' => 'draft',
            'updated_by' => $request->user()->id,
        ]);
        $newsPost->load('category');

        return response()->json([
            'message' => 'News post unpublished.',
            'news_post' => $this->newsPostToArray($newsPost, true),
        ]);
    }

    private function generateUniqueSlug(string $title, ?int $schoolId, ?int $excludeId): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $n = 1;
        $query = NewsPost::query()
            ->when($schoolId !== null, fn ($q) => $q->where('school_id', $schoolId))
            ->when($schoolId === null, fn ($q) => $q->whereNull('school_id'))
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId));

        while ($query->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$n);
        }

        return $slug;
    }

    private function newsPostToArray(NewsPost $n, bool $withImageUrl = false): array
    {
        $arr = [
            'id' => $n->id,
            'school_id' => $n->school_id,
            'title' => $n->title,
            'slug' => $n->slug,
            'summary' => $n->summary,
            'content' => $n->content,
            'category_id' => $n->category_id,
            'category_name' => $n->category?->name,
            'featured_image' => $n->featured_image,
            'published_at' => $n->published_at?->toIso8601String(),
            'published_at_formatted' => $this->dateTimeFormatter->formatDateTime($n->published_at),
            'status' => $n->status,
            'is_featured' => $n->is_featured,
            'created_at' => $n->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($n->created_at),
            'updated_at' => $n->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($n->updated_at),
        ];

        if ($withImageUrl && $n->featured_image) {
            $arr['featured_image_url'] = Storage::disk('public')->url($n->featured_image);
        }

        return $arr;
    }
}
