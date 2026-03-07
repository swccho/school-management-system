<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSchoolClassRequest;
use App\Http\Requests\Admin\UpdateSchoolClassRequest;
use App\Models\School;
use App\Models\SchoolClass;
use App\Services\DateTimeFormatter;
use App\Services\SchoolClassService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter,
        private SchoolClassService $schoolClassService
    ) {}
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-academic-setup')) {
            abort(403, 'Unauthorized.');
        }

        $classes = SchoolClass::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when(
                $request->filled('status') && in_array($request->status, ['active', 'inactive', 'archived'], true),
                fn ($q) => $q->where('status', $request->status)
            )
            ->ordered()
            ->get()
            ->map(fn (SchoolClass $c) => $this->classToArray($c));

        return response()->json($classes);
    }

    public function store(StoreSchoolClassRequest $request): JsonResponse
    {
        $school = School::first();
        $schoolId = $school?->id;

        $validated = $request->validated();
        if (blank($validated['code'] ?? null)) {
            $validated['code'] = $this->schoolClassService->generateCode(
                $validated['name'] ?? null,
                $validated['numeric_level'] ?? null,
                null,
                $schoolId
            );
        }

        $schoolClass = SchoolClass::create(array_merge($validated, [
            'school_id' => $schoolId,
        ]));

        return response()->json([
            'message' => 'Class created.',
            'class' => $this->classToArray($schoolClass),
        ], 201);
    }

    public function generateCode(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-classes')) {
            abort(403, 'Unauthorized.');
        }

        $schoolId = $request->input('school_id') ? (int) $request->input('school_id') : School::first()?->id;

        $code = $this->schoolClassService->generateCode(
            $request->input('name'),
            $request->input('numeric_level') ? (int) $request->input('numeric_level') : null,
            $request->input('exclude_id') ? (int) $request->input('exclude_id') : null,
            $schoolId
        );

        return response()->json([
            'success' => true,
            'data' => ['code' => $code],
        ]);
    }

    public function show(Request $request, SchoolClass $school_class): JsonResponse
    {
        if (! $request->user()->hasPermission('view-academic-setup')) {
            abort(403, 'Unauthorized.');
        }

        return response()->json($this->classToArray($school_class));
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $school_class): JsonResponse
    {
        $school_class->update($request->validated());

        return response()->json([
            'message' => 'Class updated.',
            'class' => $this->classToArray($school_class->fresh()),
        ]);
    }

    private function classToArray(SchoolClass $c): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'code' => $c->code,
            'numeric_level' => $c->numeric_level,
            'description' => $c->description,
            'status' => $c->status,
            'created_at' => $c->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($c->created_at),
            'updated_at' => $c->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($c->updated_at),
        ];
    }
}
