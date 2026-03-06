<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeavePendingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $type = 'leave_pending'
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if (config('broadcasting.default') !== 'null') {
            $channels[] = 'broadcast';
        }
        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'message' => $this->leaveRequest->employee->name . ' has a pending leave request (' . $this->leaveRequest->from_date->format('d M') . ' - ' . $this->leaveRequest->to_date->format('d M') . ').',
            'leave_request_id' => $this->leaveRequest->id,
            'employee_id' => $this->leaveRequest->employee_id,
        ];
    }
}
