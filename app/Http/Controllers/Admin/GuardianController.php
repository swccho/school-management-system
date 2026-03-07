<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGuardianRequest;
use App\Http\Requests\Admin\UpdateGuardianRequest;
use App\Models\School;
use App\Models\StudentGuardian;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-guardians')) {
            abort(403, 'Unauthorized.');
        }

        $query = StudentGuardian::query();

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $guardians = $query->orderBy('name')->get()->map(fn (StudentGuardian $g) => $this->toArray($g));

        return response()->json($guardians);
    }

    public function store(StoreGuardianRequest $request): JsonResponse
    {
        $school = School::first();
        $guardian = StudentGuardian::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
            'status' => $request->input('status', 'active'),
        ]));

        return response()->json([
            'message' => 'Guardian created.',
            'guardian' => $this->toArray($guardian),
        ], 201);
    }

    public function update(UpdateGuardianRequest $request, StudentGuardian $student_guardian): JsonResponse
    {
        $student_guardian->update($request->validated());

        return response()->json([
            'message' => 'Guardian updated.',
            'guardian' => $this->toArray($student_guardian->fresh()),
        ]);
    }

    private function toArray(StudentGuardian $g): array
    {
        return [
            'id' => $g->id,
            'name' => $g->name,
            'relation_type' => $g->relation_type,
            'phone' => $g->phone,
            'email' => $g->email,
            'occupation' => $g->occupation,
            'address' => $g->address,
            'status' => $g->status,
            'created_at' => $g->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($g->created_at),
            'updated_at' => $g->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($g->updated_at),
        ];
    }
}
