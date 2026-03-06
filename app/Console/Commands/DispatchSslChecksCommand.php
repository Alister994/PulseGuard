<?php

namespace App\Console\Commands;

use App\Jobs\RunSslCheckJob;
use App\Models\MonitoredSite;
use Illuminate\Console\Command;

class DispatchSslChecksCommand extends Command
{
    protected $signature = 'pulseguard:dispatch-ssl';

    protected $description = 'Dispatch SSL certificate check jobs for all active sites (run daily)';

    public function handle(): int
    {
        $sites = MonitoredSite::query()
            ->where('is_active', true)
            ->where('ssl_check_enabled', true)
            ->get();

        foreach ($sites as $site) {
            RunSslCheckJob::dispatch($site);
        }

        $this->info("Dispatched SSL checks for {$sites->count()} site(s).");
        return self::SUCCESS;
    }
}
