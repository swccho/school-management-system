<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\DateTimeFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct(
        private DateTimeFormatter $dateTimeFormatter
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-activity-logs')) {
            abort(403, 'Unauthorized.');
        }

        $query = ActivityLog::query()
            ->with('user:id,name,email')
            ->latestFirst();

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%'.$request->input('search').'%';
            $q->where(function ($sub) use ($term) {
                $sub->where('description', 'like', $term)
                    ->orWhere('module', 'like', $term)
                    ->orWhere('action', 'like', $term)
                    ->orWhereHas('user', function ($u) use ($term) {
                        $u->where('name', 'like', $term)->orWhere('email', 'like', $term);
                    });
            });
        });
        if ($request->filled('module')) {
            $query->byModule($request->input('module'));
        }
        if ($request->filled('action')) {
            $query->byAction($request->input('action'));
        }
        if ($request->filled('user_id')) {
            $query->byUser((int) $request->input('user_id'));
        }
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->input('created_from'));
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->input('created_to'));
        }

        $perPage = min(max((int) $request->input('per_page', 25), 10), 100);
        $paginator = $query->paginate($perPage);

        $items = $paginator->getCollection()->map(fn (ActivityLog $log) => $this->logToArray($log));

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function filterOptions(Request $request): JsonResponse
    {
        if (! $request->user()->hasPermission('view-activity-logs')) {
            abort(403, 'Unauthorized.');
        }

        $userIds = ActivityLog::query()->whereNotNull('user_id')->distinct()->pluck('user_id');
        $users = User::query()
            ->whereIn('id', $userIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]);

        $modules = ActivityLog::query()
            ->select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $actions = ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return response()->json([
            'users' => $users,
            'modules' => $modules,
            'actions' => $actions,
        ]);
    }

    public function show(Request $request, ActivityLog $activity_log): JsonResponse
    {
        if (! $request->user()->hasPermission('view-activity-logs')) {
            abort(403, 'Unauthorized.');
        }

        $activity_log->load('user:id,name,email');

        $data = $this->logToArray($activity_log);
        $data['metadata'] = $activity_log->metadata;
        $data['record_type'] = $activity_log->subject_type;
        $data['record_id'] = $activity_log->subject_id;
        $data['subject_type'] = $activity_log->subject_type;
        $data['subject_id'] = $activity_log->subject_id;
        $data['user_agent'] = $activity_log->user_agent;

        return response()->json($data);
    }

    private function logToArray(ActivityLog $log): array
    {
        return [
            'id' => $log->id,
            'user_id' => $log->user_id,
            'user_name' => $log->user?->name,
            'user_email' => $log->user?->email,
            'module' => $log->module,
            'action' => $log->action,
            'description' => $log->description,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at->toIso8601String(),
            'created_at_formatted' => $this->dateTimeFormatter->formatDateTime($log->created_at),
        ];
    }
}
