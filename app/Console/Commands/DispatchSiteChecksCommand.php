<?php

namespace App\Console\Commands;

use App\Jobs\RunHttpCheckJob;
use App\Jobs\RunSslCheckJob;
use App\Models\MonitoredSite;
use Illuminate\Console\Command;

class DispatchSiteChecksCommand extends Command
{
    protected $signature = 'pulseguard:dispatch-checks
                            {--chunk= : Process sites in chunks (default from config)}';

    protected $description = 'Dispatch HTTP and SSL check jobs for all due monitored sites';

    public function handle(): int
    {
        $chunkSize = (int) ($this->option('chunk') ?: config('pulseguard.queue.chunk_size', 10));

        $sites = MonitoredSite::query()
            ->where('is_active', true)
            ->get();

        $dueSites = $sites->filter(function (MonitoredSite $site) {
            return $this->isDueForCheck($site);
        });

        $count = $dueSites->count();
        if ($count === 0) {
            $this->info('No sites due for check.');
            return self::SUCCESS;
        }

        $dueSites->chunk($chunkSize)->each(function ($chunk) {
            foreach ($chunk as $site) {
                RunHttpCheckJob::dispatch($site);
                if ($site->ssl_check_enabled) {
                    RunSslCheckJob::dispatch($site);
                }
            }
        });

        $this->info("Dispatched checks for {$count} site(s).");
        return self::SUCCESS;
    }

    protected function isDueForCheck(MonitoredSite $site): bool
    {
        $interval = $site->check_interval_minutes ?: 1;
        $lastCheck = $site->httpChecks()->latest('checked_at')->first();

        if (! $lastCheck) {
            return true;
        }

        return $lastCheck->checked_at->addMinutes($interval)->lte(now());
    }
}
