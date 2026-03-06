<?php

namespace App\Listeners;

use App\Events\SslExpiringSoon;
use App\Services\Alerts\AlertDispatcher;

class SendSslExpiringAlerts
{
    public function __construct(
        protected AlertDispatcher $alerts
    ) {}

    public function handle(SslExpiringSoon $event): void
    {
        $site = $event->site;
        $sslCheck = $event->sslCheck;

        $days = $sslCheck->daysUntilExpiry() ?? 0;
        $validUntil = $sslCheck->valid_until?->toIso8601String() ?? 'N/A';

        $message = sprintf(
            "SSL certificate for %s expires in %d days.\nURL: %s\nValid until: %s",
            $site->name,
            $days,
            $site->url,
            $validUntil
        );

        $this->alerts->dispatchSslExpiring($site, $message, [
            'valid_until' => $validUntil,
            'days_remaining' => $days,
        ]);
    }
}
