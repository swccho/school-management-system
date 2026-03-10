<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGalleryItemRequest;
use App\Http\Requests\Admin\UpdateGalleryItemRequest;
use App\Models\Gallery;
use App\Models\GalleryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryItemController extends Controller
{
    public function store(StoreGalleryItemRequest $request, Gallery $gallery): JsonResponse
    {
        $file = $request->file('file');
        $mime = $file->getMimeType();
        $mediaType = str_starts_with($mime, 'image/') ? 'image' : 'video';

        $path = $file->store('galleries/items', 'public');

        $maxOrder = $gallery->items()->max('sort_order') ?? 0;

        $item = GalleryItem::create([
            'gallery_id' => $gallery->id,
            'media_type' => $mediaType,
            'file_path' => $path,
            'caption' => $request->input('caption'),
            'sort_order' => (int) $request->input('sort_order', $maxOrder + 1),
            'status' => $request->input('status', 'active'),
        ]);

        return response()->json([
            'message' => 'Item added.',
            'item' => [
                'id' => $item->id,
                'gallery_id' => $item->gallery_id,
                'media_type' => $item->media_type,
                'file_path' => $item->file_path,
                'url' => Storage::disk('public')->url($item->file_path),
                'caption' => $item->caption,
                'sort_order' => $item->sort_order,
                'status' => $item->status,
            ],
        ], 201);
    }

    public function update(UpdateGalleryItemRequest $request, GalleryItem $gallery_item): JsonResponse
    {
        $gallery_item->update($request->validated());

        return response()->json([
            'message' => 'Item updated.',
            'item' => [
                'id' => $gallery_item->id,
                'gallery_id' => $gallery_item->gallery_id,
                'media_type' => $gallery_item->media_type,
                'file_path' => $gallery_item->file_path,
                'url' => Storage::disk('public')->url($gallery_item->file_path),
                'caption' => $gallery_item->caption,
                'sort_order' => $gallery_item->sort_order,
                'status' => $gallery_item->status,
            ],
        ]);
    }

    public function destroy(Request $request, GalleryItem $gallery_item): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-gallery')) {
            abort(403, 'Unauthorized.');
        }

        Storage::disk('public')->delete($gallery_item->file_path);
        $gallery_item->delete();

        return response()->json(['message' => 'Item deleted.']);
    }

    public function reorder(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-gallery')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'item_ids' => ['required', 'array'],
            'item_ids.*' => ['integer', 'exists:gallery_items,id'],
        ]);

        $ids = $request->input('item_ids');
        foreach ($ids as $order => $id) {
            GalleryItem::where('id', $id)->update(['sort_order' => $order]);
        }

        return response()->json(['message' => 'Order updated.']);
    }
}
