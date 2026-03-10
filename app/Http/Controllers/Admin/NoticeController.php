<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNoticeRequest;
use App\Http\Requests\Admin\UpdateNoticeRequest;
use App\Models\Notice;
use App\Models\NoticeAttachment;
use App\Models\School;
use App\Services\ActivityLogService;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NoticeController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter,
        private ActivityLogService $activityLog
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-notices')) {
            abort(403, 'Unauthorized.');
        }

        $query = Notice::query()->with(['category']);

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%'.$request->input('search').'%';
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)->orWhere('content', 'like', $term);
            });
        });
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')));
        $query->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->input('category_id')));
        $query->when($request->filled('publish_date_from'), fn ($q) => $q->whereDate('publish_date', '>=', $request->input('publish_date_from')));
        $query->when($request->filled('publish_date_to'), fn ($q) => $q->whereDate('publish_date', '<=', $request->input('publish_date_to')));

        $notices = $query->orderByDesc('publish_date')->orderBy('title')->get()
            ->map(fn (Notice $n) => $this->noticeToArray($n));

        return response()->json($notices);
    }

    public function store(StoreNoticeRequest $request): JsonResponse
    {
        $school = School::first();
        $schoolId = $school?->id;
        $userId = $request->user()->id;

        $validated = $request->validated();
        $attachments = $validated['attachments'] ?? [];
        unset($validated['attachments']);

        $slug = $this->generateUniqueSlug($validated['title'], $schoolId, null);
        $validated['slug'] = $slug;
        $validated['school_id'] = $schoolId;
        $validated['created_by'] = $userId;
        $validated['updated_by'] = $userId;
        $validated['status'] = $validated['status'] ?? 'draft';
        $validated['is_featured'] = (bool) ($validated['is_featured'] ?? false);

        $notice = Notice::create($validated);

        $this->storeAttachments($notice, is_array($attachments) ? $attachments : []);
        $this->activityLog->log('notices', 'create', Notice::class, $notice->id, "Notice created: {$notice->title}", [], $request);

        return response()->json([
            'message' => 'Notice created.',
            'notice' => $this->noticeToArray($notice->load('category', 'attachments'), true),
        ], 201);
    }

    public function show(Request $request, Notice $notice): JsonResponse
    {
        if (! $request->user()->hasPermission('view-notices')) {
            abort(403, 'Unauthorized.');
        }

        $notice->load('category', 'attachments');

        return response()->json($this->noticeToArray($notice, true));
    }

    public function update(UpdateNoticeRequest $request, Notice $notice): JsonResponse
    {
        $validated = collect($request->validated())->except(['attachments'])->all();
        $validated['updated_by'] = $request->user()->id;

        if (isset($validated['title']) && $validated['title'] !== $notice->title) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $notice->school_id, $notice->id);
        }
        if (array_key_exists('is_featured', $validated)) {
            $validated['is_featured'] = (bool) $validated['is_featured'];
        }

        $notice->update($validated);

        $removeIds = $request->input('remove_attachment_ids', []);
        if (is_array($removeIds) && ! empty($removeIds)) {
            $toRemove = $notice->attachments()->whereIn('id', $removeIds)->get();
            foreach ($toRemove as $att) {
                Storage::disk('public')->delete($att->file_path);
                $att->delete();
            }
        }

        $newFiles = $request->file('attachments');
        if (is_array($newFiles)) {
            $this->storeAttachments($notice, $newFiles);
        }

        $notice->load('category', 'attachments');
        $this->activityLog->log('notices', 'update', Notice::class, $notice->id, "Notice updated: {$notice->title}", [], $request);

        return response()->json([
            'message' => 'Notice updated.',
            'notice' => $this->noticeToArray($notice, true),
        ]);
    }

    public function destroy(Request $request, Notice $notice): JsonResponse
    {
        if (! $request->user()->hasPermission('delete-notices')) {
            abort(403, 'Unauthorized.');
        }

        foreach ($notice->attachments as $att) {
            Storage::disk('public')->delete($att->file_path);
        }
        $dir = 'notices/'.$notice->id;
        if (Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->deleteDirectory($dir);
        }
        $noticeId = $notice->id;
        $title = $notice->title;
        $notice->delete();
        $this->activityLog->log('notices', 'delete', Notice::class, $noticeId, "Notice deleted: {$title}", [], $request);

        return response()->json(['message' => 'Notice deleted.']);
    }

    public function publish(Request $request, Notice $notice): JsonResponse
    {
        if (! $request->user()->hasPermission('publish-notices')) {
            abort(403, 'Unauthorized.');
        }

        $notice->update([
            'status' => 'published',
            'updated_by' => $request->user()->id,
        ]);
        $notice->load('category', 'attachments');
        $this->activityLog->log('notices', 'publish', Notice::class, $notice->id, "Notice published: {$notice->title}", [], $request);

        return response()->json([
            'message' => 'Notice published.',
            'notice' => $this->noticeToArray($notice, true),
        ]);
    }

    public function unpublish(Request $request, Notice $notice): JsonResponse
    {
        if (! $request->user()->hasPermission('publish-notices')) {
            abort(403, 'Unauthorized.');
        }

        $notice->update([
            'status' => 'draft',
            'updated_by' => $request->user()->id,
        ]);
        $notice->load('category', 'attachments');

        return response()->json([
            'message' => 'Notice unpublished.',
            'notice' => $this->noticeToArray($notice, true),
        ]);
    }

    private function generateUniqueSlug(string $title, ?int $schoolId, ?int $excludeId): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $n = 1;
        $query = Notice::query()
            ->when($schoolId !== null, fn ($q) => $q->where('school_id', $schoolId))
            ->when($schoolId === null, fn ($q) => $q->whereNull('school_id'))
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId));

        while ($query->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$n);
        }

        return $slug;
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>  $files
     */
    private function storeAttachments(Notice $notice, array $files): void
    {
        $dir = 'notices/'.$notice->id;
        foreach ($files as $file) {
            if (! $file instanceof \Illuminate\Http\UploadedFile) {
                continue;
            }
            $path = $file->store($dir, 'public');
            NoticeAttachment::create([
                'notice_id' => $notice->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);
        }
    }

    private function noticeToArray(Notice $n, bool $withAttachments = false): array
    {
        $arr = [
            'id' => $n->id,
            'school_id' => $n->school_id,
            'title' => $n->title,
            'slug' => $n->slug,
            'content' => $n->content,
            'category_id' => $n->category_id,
            'category_name' => $n->category?->name,
            'publish_date' => $n->publish_date?->format('Y-m-d'),
            'publish_date_formatted' => $this->dateTimeFormatter->formatDate($n->publish_date),
            'expiry_date' => $n->expiry_date?->format('Y-m-d'),
            'expiry_date_formatted' => $this->dateTimeFormatter->formatDate($n->expiry_date),
            'status' => $n->status,
            'is_featured' => $n->is_featured,
            'created_at' => $n->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($n->created_at),
            'updated_at' => $n->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($n->updated_at),
        ];

        if ($withAttachments && $n->relationLoaded('attachments')) {
            $arr['attachments'] = $n->attachments->map(fn (NoticeAttachment $a) => [
                'id' => $a->id,
                'file_name' => $a->file_name,
                'file_path' => $a->file_path,
                'file_size' => $a->file_size,
                'mime_type' => $a->mime_type,
                'url' => Storage::disk('public')->url($a->file_path),
            ])->values()->all();
        } else {
            $arr['attachments_count'] = $n->relationLoaded('attachments') ? $n->attachments->count() : $n->attachments()->count();
        }

        return $arr;
    }
}
