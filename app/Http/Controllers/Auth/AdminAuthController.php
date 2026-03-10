<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\LoginHistory;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    /**
     * Handle admin login. Only users with user_type 'admin' and status 'active' are allowed.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 422);
        }

        $user = Auth::user();

        if ($user->user_type !== 'admin') {
            Auth::logout();
            return response()->json([
                'message' => 'Access denied. Admin only.',
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
        app(ActivityLogService::class)->log('auth', 'login', null, null, "User logged in: {$user->email}", [], $request);
        LoginHistory::create([
            'user_id' => $user->id,
            'login_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'success',
        ]);

        return response()->json([
            'message' => 'Authenticated.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
            ],
        ]);
    }

    /**
     * Invalidate session and return success.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            app(ActivityLogService::class)->log('auth', 'logout', null, null, "User logged out: {$user->email}", [], $request);
            LoginHistory::where('user_id', $user->id)
                ->whereNull('logout_at')
                ->latest('login_at')
                ->limit(1)
                ->update(['logout_at' => now()]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }

    /**
     * Return the currently authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'user_type' => $user->user_type,
        ]);
    }
}
