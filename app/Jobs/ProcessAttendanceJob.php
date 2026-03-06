<?php

namespace App\Jobs;

use App\Services\AttendanceProcessor;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?Carbon $date = null,
        public int $backDays = 0
    ) {
        $this->date = $this->date ?? now();
        $this->onQueue('attendance');
    }

    public function handle(AttendanceProcessor $processor): void
    {
        $processor->processForDate($this->date);
        for ($i = 1; $i <= $this->backDays; $i++) {
            $processor->processForDate($this->date->copy()->subDays($i));
        }
    }
}
