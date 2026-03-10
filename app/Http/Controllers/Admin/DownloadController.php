<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDownloadRequest;
use App\Http\Requests\Admin\UpdateDownloadRequest;
use App\Models\Download;
use App\Models\School;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-downloads')) {
            abort(403, 'Unauthorized.');
        }

        $query = Download::query()->with(['category']);

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%'.$request->input('search').'%';
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('file_name', 'like', $term);
            });
        });
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')));
        $query->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->input('category_id')));
        $query->when($request->filled('access_type'), fn ($q) => $q->where('access_type', $request->input('access_type')));
        $query->when($request->filled('published_from'), fn ($q) => $q->whereDate('published_at', '>=', $request->input('published_from')));
        $query->when($request->filled('published_to'), fn ($q) => $q->whereDate('published_at', '<=', $request->input('published_to')));

        $downloads = $query->orderByDesc('published_at')->orderBy('title')->get()
            ->map(fn (Download $d) => $this->downloadToArray($d, true));

        return response()->json($downloads);
    }

    public function store(StoreDownloadRequest $request): JsonResponse
    {
        $school = School::first();
        $schoolId = $school?->id;
        $userId = $request->user()->id;

        $file = $request->file('file');
        $path = $file->store('downloads', 'public');
        $originalName = $file->getClientOriginalName();

        $validated = $request->validated();
        unset($validated['file']);
        $validated['slug'] = $this->generateUniqueSlug($validated['title'], $schoolId, null);
        $validated['school_id'] = $schoolId;
        $validated['created_by'] = $userId;
        $validated['updated_by'] = $userId;
        $validated['file_path'] = $path;
        $validated['file_name'] = $originalName;
        $validated['file_type'] = $file->getMimeType();
        $validated['file_size'] = $file->getSize();
        $validated['access_type'] = $validated['access_type'] ?? 'public';

        $download = Download::create($validated);

        return response()->json([
            'message' => 'Download created.',
            'download' => $this->downloadToArray($download->load('category'), true),
        ], 201);
    }

    public function show(Request $request, Download $download): JsonResponse
    {
        if (! $request->user()->hasPermission('view-downloads')) {
            abort(403, 'Unauthorized.');
        }

        $download->load('category');

        return response()->json($this->downloadToArray($download, true));
    }

    public function update(UpdateDownloadRequest $request, Download $download): JsonResponse
    {
        $validated = $request->validated();
        $validated['updated_by'] = $request->user()->id;

        if (isset($validated['title']) && $validated['title'] !== $download->title) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $download->school_id, $download->id);
        }

        $file = $request->file('file');
        if ($file) {
            Storage::disk('public')->delete($download->file_path);
            $validated['file_path'] = $file->store('downloads', 'public');
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_type'] = $file->getMimeType();
            $validated['file_size'] = $file->getSize();
        }
        unset($validated['file']);

        $download->update($validated);
        $download->load('category');

        return response()->json([
            'message' => 'Download updated.',
            'download' => $this->downloadToArray($download, true),
        ]);
    }

    public function destroy(Request $request, Download $download): JsonResponse
    {
        if (! $request->user()->hasPermission('delete-downloads')) {
            abort(403, 'Unauthorized.');
        }

        Storage::disk('public')->delete($download->file_path);
        $download->delete();

        return response()->json(['message' => 'Download deleted.']);
    }

    public function publish(Request $request, Download $download): JsonResponse
    {
        if (! $request->user()->hasPermission('publish-downloads')) {
            abort(403, 'Unauthorized.');
        }

        $download->update([
            'status' => 'published',
            'published_at' => $download->published_at ?? now(),
            'updated_by' => $request->user()->id,
        ]);
        $download->load('category');

        return response()->json([
            'message' => 'Download published.',
            'download' => $this->downloadToArray($download, true),
        ]);
    }

    public function unpublish(Request $request, Download $download): JsonResponse
    {
        if (! $request->user()->hasPermission('publish-downloads')) {
            abort(403, 'Unauthorized.');
        }

        $download->update([
            'status' => 'draft',
            'updated_by' => $request->user()->id,
        ]);
        $download->load('category');

        return response()->json([
            'message' => 'Download unpublished.',
            'download' => $this->downloadToArray($download, true),
        ]);
    }

    private function generateUniqueSlug(string $title, ?int $schoolId, ?int $excludeId): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $n = 1;
        $query = Download::query()
            ->when($schoolId !== null, fn ($q) => $q->where('school_id', $schoolId))
            ->when($schoolId === null, fn ($q) => $q->whereNull('school_id'))
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId));

        while ($query->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$n);
        }

        return $slug;
    }

    private function formatFileSize(?int $bytes): ?string
    {
        if ($bytes === null) {
            return null;
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    private function downloadToArray(Download $d, bool $withUrl = false): array
    {
        $arr = [
            'id' => $d->id,
            'school_id' => $d->school_id,
            'title' => $d->title,
            'slug' => $d->slug,
            'category_id' => $d->category_id,
            'category_name' => $d->category?->name,
            'description' => $d->description,
            'file_path' => $d->file_path,
            'file_name' => $d->file_name,
            'file_type' => $d->file_type,
            'file_size' => $d->file_size,
            'file_size_formatted' => $this->formatFileSize($d->file_size),
            'access_type' => $d->access_type,
            'published_at' => $d->published_at?->toIso8601String(),
            'published_at_formatted' => $this->dateTimeFormatter->formatDateTime($d->published_at),
            'status' => $d->status,
            'created_at' => $d->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($d->created_at),
            'updated_at' => $d->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($d->updated_at),
        ];

        if ($withUrl) {
            $arr['file_url'] = Storage::disk('public')->url($d->file_path);
        }

        return $arr;
    }
}
