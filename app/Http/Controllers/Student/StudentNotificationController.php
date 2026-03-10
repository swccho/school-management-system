<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = StudentNotification::query()
            ->forUser($user->id)
            ->orderByDesc('created_at');

        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        $perPage = min((int) $request->input('per_page', 20), 50);
        $notifications = $query->paginate($perPage);

        $items = $notifications->getCollection()->map(function (StudentNotification $n) {
            $actionUrl = $this->actionUrlFor($n);
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

    public function markAsRead(Request $request, StudentNotification $student_notification): JsonResponse
    {
        if ($student_notification->user_id !== $request->user()->id) {
            abort(403);
        }
        $student_notification->update(['read_at' => now()]);
        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        StudentNotification::query()
            ->forUser($request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);
        return response()->json(['message' => 'All marked as read']);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = StudentNotification::query()
            ->forUser($request->user()->id)
            ->unread()
            ->count();
        return response()->json(['count' => $count]);
    }

    private function actionUrlFor(StudentNotification $n): ?string
    {
        $data = $n->data ?? [];
        if ($n->type === 'notice_published' && ! empty($data['notice_id'])) {
            return '/student/announcements/' . $data['notice_id'];
        }
        if ($n->type === 'new_assignment' && ! empty($data['homework_id'])) {
            return '/student/assignments/' . $data['homework_id'];
        }
        if ($n->type === 'assignment_reminder' && ! empty($data['homework_id'])) {
            return '/student/assignments/' . $data['homework_id'];
        }
        if ($n->type === 'result_published' && ! empty($data['result_summary_id'])) {
            return '/student/results';
        }
        if ($n->type === 'exam_schedule') {
            return '/student/exams';
        }
        if ($n->type === 'fee_due') {
            return '/student/fees';
        }
        return null;
    }
}
