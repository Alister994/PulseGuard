<?php

namespace App\Console;

use App\Services\AttendanceProcessor;
use Illuminate\Console\Command;

class ProcessAttendanceCommand extends Command
{
    protected $signature = 'attendance:process {--days=3 : Number of past days to process}';

    protected $description = 'Process raw attendance logs into daily attendance (4-punch logic)';

    public function handle(AttendanceProcessor $processor): int
    {
        $days = (int) $this->option('days');
        $this->info("Processing attendance for last {$days} days...");
        $processor->processLastDays($days);
        $this->info('Done.');

        return self::SUCCESS;
    }
}
