<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBannerRequest;
use App\Http\Requests\Admin\UpdateBannerRequest;
use App\Models\Banner;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-banners')) {
            abort(403, 'Unauthorized.');
        }

        $banners = Banner::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Banner $b) => $this->bannerToArray($b, true));

        return response()->json($banners);
    }

    public function store(StoreBannerRequest $request): JsonResponse
    {
        $school = School::first();
        $validated = $request->validated();
        $validated['school_id'] = $school?->id;

        $image = $request->file('image');
        if ($image) {
            $validated['image'] = $image->store('banners', 'public');
        }

        $maxOrder = Banner::max('sort_order') ?? 0;
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? $maxOrder + 1);

        $banner = Banner::create($validated);

        return response()->json([
            'message' => 'Banner created.',
            'banner' => $this->bannerToArray($banner, true),
        ], 201);
    }

    public function show(Request $request, Banner $banner): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-banners')) {
            abort(403, 'Unauthorized.');
        }

        return response()->json($this->bannerToArray($banner, true));
    }

    public function update(UpdateBannerRequest $request, Banner $banner): JsonResponse
    {
        $validated = $request->validated();

        $image = $request->file('image');
        if ($image) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = $image->store('banners', 'public');
        }

        $banner->update($validated);

        return response()->json([
            'message' => 'Banner updated.',
            'banner' => $this->bannerToArray($banner, true),
        ]);
    }

    public function destroy(Request $request, Banner $banner): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-banners')) {
            abort(403, 'Unauthorized.');
        }

        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();

        return response()->json(['message' => 'Banner deleted.']);
    }

    public function reorder(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-banners')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'banner_ids' => ['required', 'array'],
            'banner_ids.*' => ['integer', 'exists:banners,id'],
        ]);

        $ids = $request->input('banner_ids');
        foreach ($ids as $order => $id) {
            Banner::where('id', $id)->update(['sort_order' => $order]);
        }

        return response()->json(['message' => 'Order updated.']);
    }

    private function bannerToArray(Banner $b, bool $withUrl = false): array
    {
        $arr = [
            'id' => $b->id,
            'school_id' => $b->school_id,
            'title' => $b->title,
            'subtitle' => $b->subtitle,
            'image' => $b->image,
            'button_text' => $b->button_text,
            'button_url' => $b->button_url,
            'sort_order' => $b->sort_order,
            'status' => $b->status,
            'created_at' => $b->created_at->toIso8601String(),
            'updated_at' => $b->updated_at->toIso8601String(),
        ];

        if ($withUrl && $b->image) {
            $arr['image_url'] = Storage::disk('public')->url($b->image);
        }

        return $arr;
    }
}
