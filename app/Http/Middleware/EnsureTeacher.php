<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeacher
{
    /**
     * Ensure the authenticated user is a teacher (has staff with active teacher record).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = $request->user();
        $user->load(['staff.teacher']);

        if (! $user->staff || ! $user->staff->teacher) {
            return response()->json(['message' => 'Access denied. Teacher only.'], 403);
        }

        if ($user->staff->teacher->status !== 'active') {
            return response()->json(['message' => 'Your teacher account is not active.'], 403);
        }

        return $next($request);
    }
}
