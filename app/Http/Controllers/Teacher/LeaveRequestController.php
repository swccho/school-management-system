<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $items = LeaveRequest::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'leave_type' => $l->leave_type,
                'start_date' => $l->start_date?->format('Y-m-d'),
                'end_date' => $l->end_date?->format('Y-m-d'),
                'reason' => $l->reason,
                'status' => $l->status,
                'reviewed_at' => $l->reviewed_at?->toIso8601String(),
                'remarks' => $l->remarks,
            ]);

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'leave_type' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $schoolId = $user->school_id;

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('leave-requests', 'public');
        }

        $leave = LeaveRequest::create([
            'school_id' => $schoolId,
            'user_id' => $user->id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment_path' => $path,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Leave request submitted.',
            'leave_request' => [
                'id' => $leave->id,
                'leave_type' => $leave->leave_type,
                'start_date' => $leave->start_date->format('Y-m-d'),
                'end_date' => $leave->end_date->format('Y-m-d'),
                'status' => $leave->status,
            ],
        ], 201);
    }

    public function show(Request $request, LeaveRequest $leave_request): JsonResponse
    {
        if ($leave_request->user_id !== $request->user()->id) {
            abort(403);
        }
        return response()->json([
            'id' => $leave_request->id,
            'leave_type' => $leave_request->leave_type,
            'start_date' => $leave_request->start_date?->format('Y-m-d'),
            'end_date' => $leave_request->end_date?->format('Y-m-d'),
            'reason' => $leave_request->reason,
            'attachment_path' => $leave_request->attachment_path,
            'status' => $leave_request->status,
            'reviewed_at' => $leave_request->reviewed_at?->toIso8601String(),
            'remarks' => $leave_request->remarks,
        ]);
    }
}
