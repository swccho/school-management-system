<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAcademicSessionRequest;
use App\Http\Requests\Admin\UpdateAcademicSessionRequest;
use App\Models\AcademicSession;
use App\Models\School;
use App\Services\AcademicSessionService;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicSessionController extends Controller
{
    public function __construct(
        private AcademicSessionService $academicSessionService,
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-academic-setup')) {
            abort(403, 'Unauthorized.');
        }

        $query = AcademicSession::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when(
                $request->filled('date_from') && $request->filled('date_to'),
                fn ($q) => $q->whereDate('start_date', '<=', $request->date_to)
                    ->whereDate('end_date', '>=', $request->date_from)
            )
            ->orderBy('start_date', 'desc');

        $sessions = $query->get()->map(fn (AcademicSession $s) => $this->sessionToArray($s));

        return response()->json($sessions);
    }

    public function store(StoreAcademicSessionRequest $request): JsonResponse
    {
        $school = School::first();
        $schoolId = $school?->id;

        $validated = $request->validated();
        if (blank($validated['code'] ?? null)) {
            $validated['code'] = $this->academicSessionService->generateCode(
                $validated['name'] ?? null,
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null,
                null,
                $schoolId
            );
        }

        $session = AcademicSession::create(array_merge($validated, [
            'school_id' => $schoolId,
        ]));

        return response()->json([
            'message' => 'Academic session created.',
            'session' => $this->sessionToArray($session),
        ], 201);
    }

    public function generateCode(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('manage-academic-sessions')) {
            abort(403, 'Unauthorized.');
        }

        $code = $this->academicSessionService->generateCode(
            $request->input('name'),
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('exclude_id') ? (int) $request->input('exclude_id') : null,
            $request->input('school_id') ? (int) $request->input('school_id') : null
        );

        return response()->json([
            'success' => true,
            'data' => ['code' => $code],
        ]);
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
            'start_date_formatted' => $this->dateTimeFormatter->formatDate($s->start_date),
            'end_date' => $s->end_date->format('Y-m-d'),
            'end_date_formatted' => $this->dateTimeFormatter->formatDate($s->end_date),
            'is_current' => $s->is_current,
            'status' => $s->status,
            'description' => $s->description,
        ];
    }
}
