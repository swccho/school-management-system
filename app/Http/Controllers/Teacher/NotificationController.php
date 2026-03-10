<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = TeacherNotification::query()
            ->forUser($user->id)
            ->orderByDesc('created_at');

        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        $perPage = min((int) $request->input('per_page', 20), 50);
        $notifications = $query->paginate($perPage);

        $items = $notifications->getCollection()->map(function (TeacherNotification $n) {
            $actionUrl = null;
            $data = $n->data ?? [];
            if ($n->type === 'attendance_reminder' && ! empty($data['attendance_session_id'])) {
                $actionUrl = '/teacher/attendance/take/' . $data['attendance_session_id'];
            } elseif ($n->type === 'marks_deadline' && ! empty($data['exam_id'])) {
                $actionUrl = '/teacher/exams/' . $data['exam_id'] . '/marks';
            } elseif ($n->type === 'leave_approved' || $n->type === 'leave_rejected') {
                $actionUrl = '/teacher/leave';
            } elseif ($n->type === 'notice_published' && ! empty($data['notice_id'])) {
                $actionUrl = '/teacher/notices/' . $data['notice_id'];
            } elseif ($n->type === 'event_reminder' && ! empty($data['event_id'])) {
                $actionUrl = '/teacher/calendar/events/' . $data['event_id'];
            }

            return [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'data' => $n->data,
                'action_url' => $actionUrl,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function markAsRead(Request $request, TeacherNotification $teacher_notification): JsonResponse
    {
        if ($teacher_notification->user_id !== $request->user()->id) {
            abort(403);
        }
        $teacher_notification->update(['read_at' => now()]);
        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        TeacherNotification::query()
            ->forUser($request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);
        return response()->json(['message' => 'All marked as read']);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = TeacherNotification::query()
            ->forUser($request->user()->id)
            ->unread()
            ->count();
        return response()->json(['count' => $count]);
    }
}
