<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-permissions')) {
            abort(403, 'Unauthorized.');
        }

        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->map(fn (Permission $p) => [
                'id' => $p->id,
                'module' => $p->module,
                'name' => $p->name,
                'slug' => $p->slug,
                'description' => $p->description,
            ]);

        return response()->json($permissions);
    }
}
