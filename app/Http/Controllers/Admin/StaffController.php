<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use App\Models\School;
use App\Models\Staff;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-staff')) {
            abort(403, 'Unauthorized.');
        }

        $query = Staff::query()->with(['department', 'designation']);

        if ($request->boolean('for_teacher')) {
            $query->whereDoesntHave('teacher');
        }
        if ($request->has('employee_type') && $request->employee_type !== '') {
            $query->where('employee_type', $request->employee_type);
        }
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->has('designation_id') && $request->designation_id) {
            $query->where('designation_id', $request->designation_id);
        }
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $staffs = $query->orderBy('first_name')->orderBy('last_name')->get()
            ->map(fn (Staff $s) => $this->toArray($s));

        return response()->json($staffs);
    }

    public function store(StoreStaffRequest $request): JsonResponse
    {
        $school = School::first();
        $staff = Staff::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
        ]));

        $staff->load(['department', 'designation']);

        return response()->json([
            'message' => 'Staff created.',
            'staff' => $this->toArray($staff),
        ], 201);
    }

    public function show(Request $request, Staff $staff): JsonResponse
    {
        if (! $request->user()->hasPermission('view-staff')) {
            abort(403, 'Unauthorized.');
        }

        $staff->load(['department', 'designation', 'teacher']);

        return response()->json($this->toArray($staff));
    }

    public function update(UpdateStaffRequest $request, Staff $staff): JsonResponse
    {
        $staff->update($request->validated());
        $staff->load(['department', 'designation']);

        return response()->json([
            'message' => 'Staff updated.',
            'staff' => $this->toArray($staff->fresh()),
        ]);
    }

    private function toArray(Staff $s): array
    {
        return [
            'id' => $s->id,
            'employee_id' => $s->employee_id,
            'first_name' => $s->first_name,
            'last_name' => $s->last_name,
            'full_name' => $s->full_name,
            'gender' => $s->gender,
            'date_of_birth' => $s->date_of_birth?->format('Y-m-d'),
            'date_of_birth_formatted' => $this->dateTimeFormatter->formatDate($s->date_of_birth),
            'blood_group' => $s->blood_group,
            'religion' => $s->religion,
            'phone' => $s->phone,
            'email' => $s->email,
            'address' => $s->address,
            'joining_date' => $s->joining_date?->format('Y-m-d'),
            'joining_date_formatted' => $this->dateTimeFormatter->formatDate($s->joining_date),
            'designation_id' => $s->designation_id,
            'designation_name' => $s->designation?->name,
            'department_id' => $s->department_id,
            'department_name' => $s->department?->name,
            'employee_type' => $s->employee_type,
            'status' => $s->status,
            'created_at' => $s->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($s->created_at),
            'updated_at' => $s->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($s->updated_at),
        ];
    }
}
