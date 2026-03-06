<?php

namespace App\Jobs;

use App\Models\MonitoredSite;
use App\Services\Monitoring\MonitoringEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunHttpCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    public int $backoff;

    public function __construct(
        public MonitoredSite $site
    ) {
        $this->tries = config('pulseguard.queue.retry_attempts', 3);
        $this->backoff = config('pulseguard.queue.retry_after', 90);
        $this->onConnection(config('pulseguard.queue.connection'));
    }

    public function handle(MonitoringEngine $engine): void
    {
        if (! $this->site->is_active) {
            return;
        }

        $engine->runHttpCheck($this->site);
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHours(2);
    }
}
