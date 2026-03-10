<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemMonitoringController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    public function overview(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-system-monitoring')) {
            abort(403, 'Unauthorized.');
        }

        $recentLogins = LoginHistory::where('login_at', '>=', now()->subDays(7))->where('status', 'success')->count();
        $activeSessions = DB::table('sessions')->whereNotNull('user_id')->where('last_activity', '>=', now()->subMinutes(30)->timestamp)->count();
        $failedLogins = LoginHistory::where('login_at', '>=', now()->subDays(7))->where('status', 'failed')->count();

        return response()->json([
            'recent_logins_count' => $recentLogins,
            'active_sessions_count' => $activeSessions,
            'failed_logins_count' => $failedLogins,
        ]);
    }

    public function loginHistory(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-system-monitoring')) {
            abort(403, 'Unauthorized.');
        }

        $query = LoginHistory::query()->with('user:id,name,email');

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%' . $request->input('search') . '%';
            $q->where(function ($sub) use ($term) {
                $sub->whereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term))
                    ->orWhere('ip_address', 'like', $term);
            });
        });
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('login_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('login_at', '<=', $request->input('date_to'));
        }

        $histories = $query->orderByDesc('login_at')->limit(200)->get()
            ->map(fn (LoginHistory $h) => $this->loginHistoryToArray($h));

        return response()->json($histories);
    }

    public function activeSessions(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-system-monitoring')) {
            abort(403, 'Unauthorized.');
        }

        $query = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.id as session_id',
                'sessions.user_id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'users.name as user_name',
                'users.email as user_email'
            );

        if ($request->filled('search')) {
            $term = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('users.name', 'like', $term)->orWhere('users.email', 'like', $term);
            });
        }
        if ($request->filled('user_id')) {
            $query->where('sessions.user_id', $request->input('user_id'));
        }

        $sessions = $query->orderByDesc('sessions.last_activity')->limit(100)->get()
            ->map(function ($row) {
                return [
                    'session_id' => $row->session_id,
                    'user_id' => $row->user_id,
                    'user_name' => $row->user_name,
                    'user_email' => $row->user_email,
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'last_activity' => $row->last_activity,
                    'last_activity_formatted' => $this->dateTimeFormatter->formatDateTime(\Carbon\Carbon::createFromTimestamp($row->last_activity)),
                ];
            });

        return response()->json($sessions);
    }

    private function loginHistoryToArray(LoginHistory $h): array
    {
        return [
            'id' => $h->id,
            'user_id' => $h->user_id,
            'user_name' => $h->user?->name,
            'user_email' => $h->user?->email,
            'login_at' => $h->login_at->toIso8601String(),
            'login_at_formatted' => $this->dateTimeFormatter->formatDateTime($h->login_at),
            'logout_at' => $h->logout_at?->toIso8601String(),
            'logout_at_formatted' => $this->dateTimeFormatter->formatDateTime($h->logout_at),
            'ip_address' => $h->ip_address,
            'user_agent' => $h->user_agent,
            'status' => $h->status,
        ];
    }
}
