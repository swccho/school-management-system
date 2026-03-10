<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SchoolProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemMaintenanceController extends Controller
{
    public function __construct(
        private SchoolProfileService $schoolProfileService
    ) {}

    public function status(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-settings') && ! $request->user()->hasPermission('manage-maintenance')) {
            abort(403, 'Unauthorized.');
        }

        $school = $this->schoolProfileService->getDefaultSchool();
        $maintenanceMode = $school?->settings?->maintenance_mode ?? false;

        return response()->json([
            'maintenance_mode' => $maintenanceMode,
        ]);
    }

    public function enable(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-maintenance')) {
            abort(403, 'Unauthorized.');
        }

        $school = $this->schoolProfileService->getDefaultSchool();
        if (! $school) {
            return response()->json(['message' => 'No school found.'], 404);
        }

        $settings = $school->settings ?? $school->settings()->create(['school_id' => $school->id]);
        $settings->update(['maintenance_mode' => true]);

        return response()->json([
            'message' => 'Maintenance mode enabled successfully.',
            'maintenance_mode' => true,
        ]);
    }

    public function disable(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-maintenance')) {
            abort(403, 'Unauthorized.');
        }

        $school = $this->schoolProfileService->getDefaultSchool();
        if (! $school) {
            return response()->json(['message' => 'No school found.'], 404);
        }

        $settings = $school->settings ?? $school->settings()->create(['school_id' => $school->id]);
        $settings->update(['maintenance_mode' => false]);

        return response()->json([
            'message' => 'Maintenance mode disabled successfully.',
            'maintenance_mode' => false,
        ]);
    }
}
