<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use App\Models\Permission;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-permissions')) {
            abort(403, 'Unauthorized.');
        }

        $query = Permission::query();

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%' . $request->input('search') . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('module', 'like', $term);
            });
        });
        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        $permissions = $query->orderBy('module')->orderBy('name')->get()
            ->map(fn (Permission $p) => $this->permissionToArray($p));

        return response()->json($permissions);
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = Permission::create($request->validated());

        return response()->json([
            'message' => 'Permission created.',
            'permission' => $this->permissionToArray($permission),
        ], 201);
    }

    public function show(Request $request, Permission $permission): JsonResponse
    {
        if (! $request->user()->hasPermission('view-permissions')) {
            abort(403, 'Unauthorized.');
        }

        return response()->json($this->permissionToArray($permission));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission->update($request->validated());

        return response()->json([
            'message' => 'Permission updated.',
            'permission' => $this->permissionToArray($permission),
        ]);
    }

    public function destroy(Request $request, Permission $permission): JsonResponse
    {
        if (! $request->user()->hasPermission('view-permissions')) {
            abort(403, 'Unauthorized.');
        }

        if ($permission->roles()->exists()) {
            return response()->json(['message' => 'Cannot delete permission that is assigned to one or more roles.'], 422);
        }

        $permission->roles()->detach();
        $permission->delete();

        return response()->json(['message' => 'Permission deleted.']);
    }

    private function permissionToArray(Permission $p): array
    {
        return [
            'id' => $p->id,
            'module' => $p->module,
            'name' => $p->name,
            'slug' => $p->slug,
            'description' => $p->description,
            'created_at' => $p->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($p->created_at),
            'updated_at' => $p->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($p->updated_at),
        ];
    }
}
