<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Models\Role;
use App\Services\ActivityLogService;
use App\Http\Requests\Admin\SyncRolePermissionsRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter,
        private ActivityLogService $activityLog
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-roles')) {
            abort(403, 'Unauthorized.');
        }

        $query = Role::query()->withCount('permissions');

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%' . $request->input('search') . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        });
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('is_system') && $request->input('is_system') !== '') {
            $query->where('is_system', $request->boolean('is_system'));
        }

        $roles = $query->orderBy('name')->get()->map(fn (Role $role) => $this->roleToArray($role));

        return response()->json($roles);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $slug = $validated['slug'] ?? \Illuminate\Support\Str::slug($validated['name']);
        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'is_system' => false,
        ]);
        if (! empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }
        $role->loadCount('permissions');

        $this->activityLog->log('roles', 'create', Role::class, $role->id, "Role created: {$role->name}", [], $request);

        return response()->json([
            'message' => 'Role created.',
            'role' => $this->roleToArray($role),
        ], 201);
    }

    public function show(Request $request, Role $role): JsonResponse
    {
        if (! $request->user()->hasPermission('view-roles')) {
            abort(403, 'Unauthorized.');
        }

        $role->load('permissions');

        $data = $this->roleToArray($role);
        $data['permissions'] = $role->permissions->map(fn ($p) => [
            'id' => $p->id,
            'module' => $p->module,
            'name' => $p->name,
            'slug' => $p->slug,
        ]);

        return response()->json($data);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $validated = collect($request->validated())->except('permissions')->all();
        if ($role->is_system) {
            $validated = collect($validated)->except('slug')->all();
        }
        $role->update($validated);
        if (array_key_exists('permissions', $request->validated())) {
            $role->permissions()->sync($request->input('permissions', []));
        }
        $role->loadCount('permissions')->load('permissions');

        $this->activityLog->log('roles', 'update', Role::class, $role->id, "Role updated: {$role->name}", [], $request);

        return response()->json([
            'message' => 'Role updated.',
            'role' => $this->roleToArray($role),
        ]);
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        if (! $request->user()->hasPermission('edit-roles')) {
            abort(403, 'Unauthorized.');
        }

        if ($role->is_system) {
            return response()->json(['message' => 'System roles cannot be deleted.'], 422);
        }

        $name = $role->name;
        $roleId = $role->id;
        $role->permissions()->detach();
        $role->users()->detach();
        $role->delete();

        $this->activityLog->log('roles', 'delete', Role::class, $roleId, "Role deleted: {$name}", [], $request);

        return response()->json(['message' => 'Role deleted.']);
    }

    public function getPermissions(Request $request, Role $role): JsonResponse
    {
        if (! $request->user()->hasPermission('view-roles')) {
            abort(403, 'Unauthorized.');
        }

        $permissionIds = $role->permissions()->pluck('permissions.id')->all();

        return response()->json(['permission_ids' => $permissionIds]);
    }

    public function syncPermissions(SyncRolePermissionsRequest $request, Role $role): JsonResponse
    {
        $role->permissions()->sync($request->input('permission_ids', []));

        $this->activityLog->log('roles', 'assign-permissions', Role::class, $role->id, "Permissions updated for role: {$role->name}", [], $request);

        return response()->json([
            'message' => 'Permissions updated.',
            'permission_ids' => $role->permissions()->pluck('permissions.id')->all(),
        ]);
    }

    private function roleToArray(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'is_system' => $role->is_system,
            'status' => $role->status,
            'permissions_count' => $role->permissions_count ?? $role->permissions->count(),
            'created_at' => $role->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($role->created_at),
            'updated_at' => $role->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($role->updated_at),
        ];
    }
}
