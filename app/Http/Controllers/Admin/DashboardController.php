<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private AdminDashboardService $dashboardService
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-dashboard')) {
            abort(403, 'Unauthorized.');
        }

        return response()->json($this->dashboardService->getDashboardData());
    }
}
