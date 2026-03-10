<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentNoticeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $schoolId = $request->user()->school_id;

        $query = Notice::query()
            ->where('school_id', $schoolId)
            ->published()
            ->with('category')
            ->orderByDesc('is_featured')
            ->orderByDesc('publish_date');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $notices = $query->limit(100)->get()->map(fn ($n) => [
            'id' => $n->id,
            'title' => $n->title,
            'excerpt' => $n->content ? Str::limit(strip_tags($n->content), 120) : null,
            'publish_date' => $n->publish_date?->format('Y-m-d'),
            'category' => $n->category ? ['id' => $n->category->id, 'name' => $n->category->name] : null,
            'is_featured' => $n->is_featured,
        ]);

        return response()->json(['data' => $notices]);
    }

    public function show(Request $request, Notice $notice): JsonResponse
    {
        if ($notice->school_id !== $request->user()->school_id) {
            abort(404);
        }
        if ($notice->status !== 'published') {
            abort(404);
        }
        $notice->load(['category', 'attachments']);
        return response()->json([
            'id' => $notice->id,
            'title' => $notice->title,
            'content' => $notice->content,
            'category' => $notice->category ? ['id' => $notice->category->id, 'name' => $notice->category->name] : null,
            'publish_date' => $notice->publish_date?->format('Y-m-d'),
            'expiry_date' => $notice->expiry_date?->format('Y-m-d'),
            'is_featured' => $notice->is_featured,
            'attachments' => $notice->attachments->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->file_name ?? 'Attachment',
                'file_path' => $a->file_path,
            ])->toArray(),
        ]);
    }
}
