<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AcademicSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherAuthController extends Controller
{
    /**
     * Handle teacher login. Only users with linked Staff and active Teacher record are allowed.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 422);
        }

        $user = Auth::user();
        $user->load(['staff.teacher']);

        if (! $user->staff || ! $user->staff->teacher) {
            Auth::logout();
            return response()->json([
                'message' => 'Access denied. Teacher account only.',
            ], 403);
        }

        $teacher = $user->staff->teacher;
        if ($teacher->status !== 'active') {
            Auth::logout();
            return response()->json([
                'message' => 'Your teacher account is not active.',
            ], 403);
        }

        if ($user->status !== 'active') {
            Auth::logout();
            return response()->json([
                'message' => 'Your account is not active.',
            ], 403);
        }

        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        $payload = $this->buildMePayload($user);
        return response()->json([
            'message' => 'Authenticated.',
            ...$payload,
        ]);
    }

    /**
     * Return the currently authenticated teacher and assignments.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['staff.teacher']);
        if (! $user->staff || ! $user->staff->teacher) {
            return response()->json(['message' => 'Not a teacher.'], 403);
        }
        return response()->json($this->buildMePayload($user));
    }

    /**
     * Invalidate session and return success.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Logged out.']);
    }

    private function buildMePayload($user): array
    {
        $user->load(['staff.teacher', 'staff.designation', 'staff.department']);
        $staff = $user->staff;
        $teacher = $staff->teacher;

        $currentSession = AcademicSession::query()
            ->where('school_id', $user->school_id)
            ->where('is_current', true)
            ->first();

        $assignments = [];
        if ($currentSession) {
            $assignments = $teacher->subjectAssignments()
                ->where('academic_session_id', $currentSession->id)
                ->active()
                ->with(['academicSession', 'schoolClass', 'section', 'subject'])
                ->get()
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'academic_session_id' => $a->academic_session_id,
                    'academic_session_name' => $a->academicSession?->name,
                    'class_id' => $a->class_id,
                    'class_name' => $a->schoolClass?->name,
                    'section_id' => $a->section_id,
                    'section_name' => $a->section?->name,
                    'subject_id' => $a->subject_id,
                    'subject_name' => $a->subject?->name,
                ])
                ->toArray();
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'staff' => [
                'id' => $staff->id,
                'full_name' => $staff->full_name,
                'employee_id' => $staff->employee_id,
                'joining_date' => $staff->joining_date?->format('Y-m-d'),
                'photo_path' => $staff->photo_path,
                'designation' => $staff->designation ? ['id' => $staff->designation->id, 'name' => $staff->designation->name] : null,
                'department' => $staff->department ? ['id' => $staff->department->id, 'name' => $staff->department->name] : null,
            ],
            'teacher' => [
                'id' => $teacher->id,
                'teacher_code' => $teacher->teacher_code,
                'qualification' => $teacher->qualification,
                'specialization' => $teacher->specialization,
                'experience_years' => $teacher->experience_years,
                'is_class_teacher' => $teacher->is_class_teacher,
            ],
            'current_academic_session' => $currentSession ? [
                'id' => $currentSession->id,
                'name' => $currentSession->name,
                'code' => $currentSession->code,
            ] : null,
            'assignments' => $assignments,
        ];
    }
}
