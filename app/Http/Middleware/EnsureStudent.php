<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = $request->user();
        $user->load(['student']);

        if (! $user->student) {
            return response()->json(['message' => 'Access denied. Student only.'], 403);
        }

        if ($user->student->status !== 'active') {
            return response()->json(['message' => 'Your student account is not active.'], 403);
        }

        if ($user->status !== 'active') {
            return response()->json(['message' => 'Your account is not active.'], 403);
        }

        return $next($request);
    }
}
