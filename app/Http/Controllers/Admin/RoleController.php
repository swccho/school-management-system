<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-roles')) {
            abort(403, 'Unauthorized.');
        }

        $roles = Role::withCount('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'is_system' => $role->is_system,
                'status' => $role->status,
                'permissions_count' => $role->permissions_count,
            ]);

        return response()->json($roles);
    }

    public function show(Request $request, Role $role): JsonResponse
    {
        if (! $request->user()->hasPermission('view-roles')) {
            abort(403, 'Unauthorized.');
        }

        $role->load('permissions');

        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'is_system' => $role->is_system,
            'status' => $role->status,
            'permissions' => $role->permissions->map(fn ($p) => [
                'id' => $p->id,
                'module' => $p->module,
                'name' => $p->name,
                'slug' => $p->slug,
            ]),
        ]);
    }
}
