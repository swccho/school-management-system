<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffDepartmentRequest;
use App\Http\Requests\Admin\UpdateStaffDepartmentRequest;
use App\Models\School;
use App\Models\StaffDepartment;
use App\Services\DateTimeFormatter;
use App\Services\StaffDepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffDepartmentController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter,
        private StaffDepartmentService $staffDepartmentService
    ) {}
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-staff')) {
            abort(403, 'Unauthorized.');
        }

        $query = StaffDepartment::query();
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->input('search');
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        });
        $query->when(
            $request->filled('status') && in_array($request->input('status'), ['active', 'inactive', 'archived'], true),
            fn ($q) => $q->where('status', $request->input('status'))
        );
        $departments = $query->orderBy('name')
            ->get()
            ->map(fn (StaffDepartment $d) => $this->toArray($d));

        return response()->json($departments);
    }

    public function store(StoreStaffDepartmentRequest $request): JsonResponse
    {
        $school = School::first();
        $department = StaffDepartment::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
            'code' => $this->staffDepartmentService->generateDepartmentCode($school?->id),
        ]));

        return response()->json([
            'message' => 'Department created.',
            'department' => $this->toArray($department),
        ], 201);
    }

    public function update(UpdateStaffDepartmentRequest $request, StaffDepartment $staff_department): JsonResponse
    {
        $payload = $request->validated();
        unset($payload['code']);
        $staff_department->update($payload);

        return response()->json([
            'message' => 'Department updated.',
            'department' => $this->toArray($staff_department->fresh()),
        ]);
    }

    private function toArray(StaffDepartment $d): array
    {
        return [
            'id' => $d->id,
            'name' => $d->name,
            'code' => $d->code,
            'description' => $d->description,
            'status' => $d->status,
            'created_at' => $d->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($d->created_at),
            'updated_at' => $d->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($d->updated_at),
        ];
    }
}
