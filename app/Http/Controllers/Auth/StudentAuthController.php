<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StudentLoginRequest;
use App\Models\AcademicSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentAuthController extends Controller
{
    public function login(StudentLoginRequest $request): JsonResponse
    {
        $login = $request->input('login');
        $user = User::query()
            ->where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (! $user || ! Auth::attempt(['email' => $user->email, 'password' => $request->password], $request->boolean('remember'))) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 422);
        }

        $user = Auth::user();
        $user->load(['student']);

        if (! $user->student) {
            Auth::logout();
            return response()->json([
                'message' => 'Access denied. Student account only.',
            ], 403);
        }

        if ($user->student->status !== 'active') {
            Auth::logout();
            return response()->json([
                'message' => 'Your student account is not active.',
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

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['student']);
        if (! $user->student) {
            return response()->json(['message' => 'Not a student.'], 403);
        }
        return response()->json($this->buildMePayload($user));
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Logged out.']);
    }

    private function buildMePayload($user): array
    {
        $user->load(['student.studentAcademicAssignments.academicSession', 'student.studentAcademicAssignments.schoolClass', 'student.studentAcademicAssignments.section']);
        $student = $user->student;

        $currentSession = AcademicSession::query()
            ->where('school_id', $user->school_id)
            ->where('is_current', true)
            ->first();

        $currentAssignment = null;
        if ($currentSession) {
            $assignment = $student->studentAcademicAssignments()
                ->where('academic_session_id', $currentSession->id)
                ->active()
                ->with(['schoolClass', 'section'])
                ->first();
            if ($assignment) {
                $currentAssignment = [
                    'class_id' => $assignment->class_id,
                    'section_id' => $assignment->section_id,
                    'class_name' => $assignment->schoolClass?->name,
                    'section_name' => $assignment->section?->name,
                    'academic_session_id' => $assignment->academic_session_id,
                    'roll_no' => $assignment->roll_no ?? $student->roll_no,
                ];
            }
        }

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'student' => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'admission_no' => $student->admission_no,
                'roll_no' => $student->roll_no,
                'email' => $student->email ?? $user->email,
                'phone' => $student->phone ?? $user->phone,
                'photo_path' => $student->photo_path,
            ],
            'school_id' => $user->school_id,
            'current_academic_session' => $currentSession ? [
                'id' => $currentSession->id,
                'name' => $currentSession->name,
                'code' => $currentSession->code,
            ] : null,
            'current_assignment' => $currentAssignment,
        ];
    }
}
