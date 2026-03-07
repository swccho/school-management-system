<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSchoolClassRequest;
use App\Http\Requests\Admin\UpdateSchoolClassRequest;
use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-academic-setup')) {
            abort(403, 'Unauthorized.');
        }

        $classes = SchoolClass::query()
            ->ordered()
            ->get()
            ->map(fn (SchoolClass $c) => $this->classToArray($c));

        return response()->json($classes);
    }

    public function store(StoreSchoolClassRequest $request): JsonResponse
    {
        $school = School::first();
        $schoolClass = SchoolClass::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
        ]));

        return response()->json([
            'message' => 'Class created.',
            'class' => $this->classToArray($schoolClass),
        ], 201);
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
            'updated_at' => $c->updated_at->toIso8601String(),
        ];
    }
}
