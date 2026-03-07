<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDesignationRequest;
use App\Http\Requests\Admin\UpdateDesignationRequest;
use App\Models\Designation;
use App\Models\School;
use App\Services\DateTimeFormatter;
use App\Services\DesignationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter,
        private DesignationService $designationService
    ) {}
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-staff')) {
            abort(403, 'Unauthorized.');
        }

        $query = Designation::query()->with('department');
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
        $query->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->input('department_id')));
        $designations = $query->orderBy('name')
            ->get()
            ->map(fn (Designation $d) => $this->toArray($d));

        return response()->json($designations);
    }

    public function store(StoreDesignationRequest $request): JsonResponse
    {
        $school = School::first();
        $designation = Designation::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
            'code' => $this->designationService->generateDesignationCode($school?->id),
        ]));

        $designation->load('department');

        return response()->json([
            'message' => 'Designation created.',
            'designation' => $this->toArray($designation),
        ], 201);
    }

    public function update(UpdateDesignationRequest $request, Designation $designation): JsonResponse
    {
        $payload = $request->validated();
        unset($payload['code']);
        $designation->update($payload);
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
