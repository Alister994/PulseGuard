<?php

namespace App\Jobs;

use App\Models\MonitoredSite;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSitesCheckBatchJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $siteId
    ) {
        $this->onConnection(config('pulseguard.queue.connection'));
    }

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $site = MonitoredSite::find($this->siteId);
        if (! $site || ! $site->is_active) {
            return;
        }

        RunHttpCheckJob::dispatch($site);

        if ($site->ssl_check_enabled) {
            RunSslCheckJob::dispatch($site);
        }
    }
}
