<?php

namespace App\Listeners;

use App\Events\SiteRecovered;
use App\Services\Alerts\AlertDispatcher;

class SendRecoveryAlerts
{
    public function __construct(
        protected AlertDispatcher $alerts
    ) {}

    public function handle(SiteRecovered $event): void
    {
        $site = $event->site;
        $incident = $event->incident;

        $duration = $incident->durationInSeconds();
        $durationStr = $duration !== null ? sprintf('%d seconds', $duration) : 'N/A';

        $message = sprintf(
            "Site %s has recovered.\nURL: %s\nDowntime duration: %s",
            $site->name,
            $site->url,
            $durationStr
        );

        $this->alerts->dispatchRecovery($site, $message, [
            'incident_id' => $incident->id,
            'resolved_at' => $incident->resolved_at?->toIso8601String(),
        ]);
    }
}
