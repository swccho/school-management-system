<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\NoticeCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        $query = Notice::query()
            ->where('school_id', $schoolId)
            ->where('status', 'published')
            ->with('category')
            ->orderByDesc('is_featured')
            ->orderByDesc('publish_date');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('publish_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('publish_date', '<=', $request->date_to);
        }

        $notices = $query->limit(50)->get()->map(fn ($n) => [
            'id' => $n->id,
            'title' => $n->title,
            'slug' => $n->slug,
            'content' => $n->content,
            'excerpt' => \Illuminate\Support\Str::limit(strip_tags($n->content), 120),
            'category' => $n->category ? ['id' => $n->category->id, 'name' => $n->category->name] : null,
            'publish_date' => $n->publish_date?->format('Y-m-d'),
            'expiry_date' => $n->expiry_date?->format('Y-m-d'),
            'is_featured' => $n->is_featured,
        ]);

        return response()->json($notices);
    }

    public function categoryOptions(Request $request): JsonResponse
    {
        $options = NoticeCategory::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]);

        return response()->json($options);
    }

    public function show(Request $request, Notice $notice): JsonResponse
    {
        if ($notice->school_id !== $request->user()->school_id || $notice->status !== 'published') {
            abort(404);
        }
        $notice->load('category');
        return response()->json([
            'id' => $notice->id,
            'title' => $notice->title,
            'slug' => $notice->slug,
            'content' => $notice->content,
            'category' => $notice->category ? ['id' => $notice->category->id, 'name' => $notice->category->name] : null,
            'publish_date' => $notice->publish_date?->format('Y-m-d'),
            'expiry_date' => $notice->expiry_date?->format('Y-m-d'),
            'is_featured' => $notice->is_featured,
        ]);
    }
}
