<?php

namespace App\Notifications;

use App\Models\AttendanceDaily;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LateEntryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AttendanceDaily $attendance
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'late_entry',
            'message' => $this->attendance->employee->name . ' had a late entry on ' . $this->attendance->date->format('d M Y') . ' (' . $this->attendance->late_minutes . ' min late).',
            'attendance_daily_id' => $this->attendance->id,
            'employee_id' => $this->attendance->employee_id,
            'date' => $this->attendance->date->toDateString(),
            'late_minutes' => $this->attendance->late_minutes,
        ];
    }
}
