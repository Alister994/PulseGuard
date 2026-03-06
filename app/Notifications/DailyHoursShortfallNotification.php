<?php

namespace App\Notifications;

use App\Models\AttendanceDaily;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DailyHoursShortfallNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AttendanceDaily $attendance,
        public int $expectedMinutes,
        public int $actualMinutes
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $shortfall = $this->expectedMinutes - $this->actualMinutes;
        return [
            'type' => 'hours_shortfall',
            'message' => $this->attendance->employee->name . ' did not fulfill daily hours on ' . $this->attendance->date->format('d M Y') . ' (worked ' . round($this->actualMinutes / 60, 1) . 'h, expected ' . round($this->expectedMinutes / 60, 1) . 'h, shortfall ' . round($shortfall / 60, 1) . 'h).',
            'attendance_daily_id' => $this->attendance->id,
            'employee_id' => $this->attendance->employee_id,
            'date' => $this->attendance->date->toDateString(),
            'expected_minutes' => $this->expectedMinutes,
            'actual_minutes' => $this->actualMinutes,
        ];
    }
}
