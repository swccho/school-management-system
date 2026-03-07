<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDesignationRequest;
use App\Http\Requests\Admin\UpdateDesignationRequest;
use App\Models\Designation;
use App\Models\School;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-staff')) {
            abort(403, 'Unauthorized.');
        }

        $designations = Designation::query()
            ->with('department')
            ->orderBy('name')
            ->get()
            ->map(fn (Designation $d) => $this->toArray($d));

        return response()->json($designations);
    }

    public function store(StoreDesignationRequest $request): JsonResponse
    {
        $school = School::first();
        $designation = Designation::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
        ]));

        $designation->load('department');

        return response()->json([
            'message' => 'Designation created.',
            'designation' => $this->toArray($designation),
        ], 201);
    }

    public function update(UpdateDesignationRequest $request, Designation $designation): JsonResponse
    {
        $designation->update($request->validated());
        $designation->load('department');

        return response()->json([
            'message' => 'Designation updated.',
            'designation' => $this->toArray($designation->fresh()),
        ]);
    }

    private function toArray(Designation $d): array
    {
        return [
            'id' => $d->id,
            'department_id' => $d->department_id,
            'department_name' => $d->department?->name,
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
