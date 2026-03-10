<?php

namespace App\Observers;

use App\Models\LeaveRequest;
use App\Services\TeacherNotificationService;

class LeaveRequestObserver
{
    public function updated(LeaveRequest $leaveRequest): void
    {
        if (! $leaveRequest->wasChanged('status')) {
            return;
        }
        $status = $leaveRequest->status;
        if ($status === 'approved') {
            TeacherNotificationService::create(
                $leaveRequest->user_id,
                $leaveRequest->school_id ?? 0,
                'leave_approved',
                'Leave request approved',
                $leaveRequest->remarks ? "Your leave request has been approved. Remarks: {$leaveRequest->remarks}" : 'Your leave request has been approved.',
                ['leave_request_id' => $leaveRequest->id]
            );
        } elseif ($status === 'rejected') {
            TeacherNotificationService::create(
                $leaveRequest->user_id,
                $leaveRequest->school_id ?? 0,
                'leave_rejected',
                'Leave request rejected',
                $leaveRequest->remarks ? "Your leave request was not approved. Remarks: {$leaveRequest->remarks}" : 'Your leave request was not approved.',
                ['leave_request_id' => $leaveRequest->id]
            );
        }
    }
}
