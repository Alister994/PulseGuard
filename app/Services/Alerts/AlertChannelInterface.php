<?php

namespace App\Services\Alerts;

use App\Models\MonitoredSite;

interface AlertChannelInterface
{
    public function sendDowntimeAlert(MonitoredSite $site, string $message, array $context = []): bool;

    public function sendRecoveryAlert(MonitoredSite $site, string $message, array $context = []): bool;

    public function sendSslExpiringAlert(MonitoredSite $site, string $message, array $context = []): bool;

    public function getName(): string;
}
