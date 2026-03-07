<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAcademicSessionRequest;
use App\Http\Requests\Admin\UpdateAcademicSessionRequest;
use App\Models\AcademicSession;
use App\Models\School;
use App\Services\AcademicSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicSessionController extends Controller
{
    public function __construct(
        private AcademicSessionService $academicSessionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-academic-setup')) {
            abort(403, 'Unauthorized.');
        }

        $sessions = AcademicSession::query()
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn (AcademicSession $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code,
                'start_date' => $s->start_date->format('Y-m-d'),
                'end_date' => $s->end_date->format('Y-m-d'),
                'is_current' => $s->is_current,
                'status' => $s->status,
                'description' => $s->description,
            ]);

        return response()->json($sessions);
    }

    public function store(StoreAcademicSessionRequest $request): JsonResponse
    {
        $school = School::first();
        $session = AcademicSession::create(array_merge($request->validated(), [
            'school_id' => $school?->id,
        ]));

        return response()->json([
            'message' => 'Academic session created.',
            'session' => $this->sessionToArray($session),
        ], 201);
    }

    public function show(Request $request, AcademicSession $academic_session): JsonResponse
    {
        if (! $request->user()->hasPermission('view-academic-setup')) {
            abort(403, 'Unauthorized.');
        }

        return response()->json($this->sessionToArray($academic_session));
    }

    public function update(UpdateAcademicSessionRequest $request, AcademicSession $academic_session): JsonResponse
    {
        $academic_session->update($request->validated());

        return response()->json([
            'message' => 'Academic session updated.',
            'session' => $this->sessionToArray($academic_session->fresh()),
        ]);
    }

    public function setCurrent(Request $request, AcademicSession $academic_session): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-academic-sessions')) {
            abort(403, 'Unauthorized.');
        }

        $this->academicSessionService->setCurrent($academic_session);

        return response()->json([
            'message' => 'Current session updated.',
            'session' => $this->sessionToArray($academic_session->fresh()),
        ]);
    }

    private function sessionToArray(AcademicSession $s): array
    {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'code' => $s->code,
            'start_date' => $s->start_date->format('Y-m-d'),
            'end_date' => $s->end_date->format('Y-m-d'),
            'is_current' => $s->is_current,
            'status' => $s->status,
            'description' => $s->description,
        ];
    }
}
