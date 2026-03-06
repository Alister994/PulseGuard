<?php

namespace App\Listeners;

use App\Events\SiteDownDetected;
use App\Services\Alerts\AlertDispatcher;

class SendDowntimeAlerts
{
    public function __construct(
        protected AlertDispatcher $alerts
    ) {}

    public function handle(SiteDownDetected $event): void
    {
        $site = $event->site;
        $incident = $event->incident;

        $message = sprintf(
            "Site %s is down.\nURL: %s\nStarted: %s\nStatus: %s",
            $site->name,
            $site->url,
            $incident->started_at->toIso8601String(),
            $incident->summary ?? 'Unknown'
        );

        $this->alerts->dispatchDowntime($site, $message, [
            'incident_id' => $incident->id,
            'started_at' => $incident->started_at->toIso8601String(),
        ]);

        $incident->update(['alert_sent' => true]);
    }
}
