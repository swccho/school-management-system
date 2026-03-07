<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttachStudentGuardianRequest;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-students')) {
            abort(403, 'Unauthorized.');
        }

        $query = Student::query()->with(['guardians']);

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('admission_no', 'like', "%{$term}%")
                    ->orWhere('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%");
            });
        }
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $students = $query->orderBy('first_name')->orderBy('last_name')->get()
            ->map(fn (Student $s) => $this->toArray($s));

        return response()->json($students);
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $school = School::first();
        $validated = $request->validated();

        $primaryGuardianData = $request->input('primary_guardian');
        if (is_array($primaryGuardianData) && ! empty($primaryGuardianData['name'] ?? null)) {
            $request->validate([
                'primary_guardian.name' => ['required', 'string', 'max:255'],
                'primary_guardian.relation_type' => ['nullable', 'string', 'max:50'],
                'primary_guardian.phone' => ['nullable', 'string', 'max:50'],
                'primary_guardian.email' => ['nullable', 'email', 'max:255'],
                'primary_guardian.occupation' => ['nullable', 'string', 'max:100'],
                'primary_guardian.address' => ['nullable', 'string', 'max:500'],
            ]);
        } else {
            $primaryGuardianData = null;
        }

        $student = DB::transaction(function () use ($validated, $primaryGuardianData, $school) {
            $student = Student::create(array_merge($validated, [
                'school_id' => $school?->id,
                'status' => $validated['status'] ?? 'active',
            ]));

            if ($primaryGuardianData !== null) {
                $guardian = StudentGuardian::create([
                    'school_id' => $school?->id,
                    'name' => $primaryGuardianData['name'],
                    'relation_type' => $primaryGuardianData['relation_type'] ?? null,
                    'phone' => $primaryGuardianData['phone'] ?? null,
                    'email' => $primaryGuardianData['email'] ?? null,
                    'occupation' => $primaryGuardianData['occupation'] ?? null,
                    'address' => $primaryGuardianData['address'] ?? null,
                    'status' => 'active',
                ]);
                $student->guardians()->attach($guardian->id, [
                    'relationship_label' => $primaryGuardianData['relation_type'] ?? null,
                    'is_primary' => true,
                    'can_receive_sms' => true,
                    'can_receive_email' => true,
                    'can_login' => false,
                ]);
            }

            return $student->load('guardians');
        });

        return response()->json([
            'message' => 'Student created.',
            'student' => $this->toArray($student->fresh(['guardians'])),
        ], 201);
    }

    public function show(Request $request, Student $student): JsonResponse
    {
        if (! $request->user()->hasPermission('view-students')) {
            abort(403, 'Unauthorized.');
        }

        $student->load('guardians');

        return response()->json($this->toDetailArray($student));
    }

    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $student->update($request->validated());
        $student->load('guardians');

        return response()->json([
            'message' => 'Student updated.',
            'student' => $this->toArray($student->fresh(['guardians'])),
        ]);
    }

    public function attachGuardian(AttachStudentGuardianRequest $request, Student $student): JsonResponse
    {
        $payload = $request->validated();
        $guardianId = (int) $payload['guardian_id'];
        $existing = $student->guardians()->where('student_guardians.id', $guardianId)->exists();
        if ($existing) {
            throw ValidationException::withMessages([
                'guardian_id' => ['This guardian is already linked to this student.'],
            ]);
        }
        $student->guardians()->attach($guardianId, [
            'relationship_label' => $payload['relationship_label'] ?? null,
            'is_primary' => (bool) ($payload['is_primary'] ?? false),
            'can_receive_sms' => (bool) ($payload['can_receive_sms'] ?? true),
            'can_receive_email' => (bool) ($payload['can_receive_email'] ?? true),
            'can_login' => (bool) ($payload['can_login'] ?? false),
        ]);
        $student->load('guardians');

        return response()->json([
            'message' => 'Guardian linked.',
            'student' => $this->toDetailArray($student->fresh(['guardians'])),
        ], 201);
    }

    private function toArray(Student $s): array
    {
        $primary = $s->guardians->first(fn ($g) => $g->pivot->is_primary) ?? $s->guardians->first();

        return [
            'id' => $s->id,
            'admission_no' => $s->admission_no,
            'registration_no' => $s->registration_no,
            'roll_no' => $s->roll_no,
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
            'present_address' => $s->present_address,
            'permanent_address' => $s->permanent_address,
            'status' => $s->status,
            'primary_guardian_name' => $primary?->name,
            'primary_guardian_phone' => $primary?->phone,
            'guardians' => $s->guardians->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'relation_type' => $g->relation_type,
                'phone' => $g->phone,
                'email' => $g->email,
                'is_primary' => (bool) $g->pivot->is_primary,
            ])->values()->all(),
            'created_at' => $s->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($s->created_at),
            'updated_at' => $s->updated_at->toIso8601String(),
            'updated_at_formatted' => $this->dateTimeFormatter->formatDateTime($s->updated_at),
        ];
    }

    private function toDetailArray(Student $s): array
    {
        return array_merge($this->toArray($s), [
            'guardians' => $s->guardians->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'relation_type' => $g->relation_type,
                'phone' => $g->phone,
                'email' => $g->email,
                'occupation' => $g->occupation,
                'address' => $g->address,
                'is_primary' => (bool) $g->pivot->is_primary,
                'relationship_label' => $g->pivot->relationship_label,
                'can_receive_sms' => (bool) $g->pivot->can_receive_sms,
                'can_receive_email' => (bool) $g->pivot->can_receive_email,
                'can_login' => (bool) $g->pivot->can_login,
            ])->values()->all(),
        ]);
    }
}
