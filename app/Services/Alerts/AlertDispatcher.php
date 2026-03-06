<?php

namespace App\Services\Alerts;

use App\Models\MonitoredSite;
use Illuminate\Support\Facades\Log;

class AlertDispatcher
{
    /** @var array<string, AlertChannelInterface> */
    protected array $channels = [];

    public function __construct()
    {
        if (config('pulseguard.alerts.slack.enabled')) {
            $this->channels['slack'] = app(SlackAlertChannel::class);
        }
        if (config('pulseguard.alerts.telegram.enabled')) {
            $this->channels['telegram'] = app(TelegramAlertChannel::class);
        }
        if (config('pulseguard.alerts.mail.enabled')) {
            $this->channels['mail'] = app(MailAlertChannel::class);
        }
        if (config('pulseguard.alerts.webhook.enabled')) {
            $this->channels['webhook'] = app(WebhookAlertChannel::class);
        }
    }

    public function dispatchDowntime(MonitoredSite $site, string $message, array $context = []): void
    {
        $this->dispatch($site, ['slack', 'telegram', 'mail', 'webhook'], fn (AlertChannelInterface $ch) => $ch->sendDowntimeAlert($site, $message, $context));
    }

    public function dispatchRecovery(MonitoredSite $site, string $message, array $context = []): void
    {
        $this->dispatch($site, ['slack', 'telegram', 'mail', 'webhook'], fn (AlertChannelInterface $ch) => $ch->sendRecoveryAlert($site, $message, $context));
    }

    public function dispatchSslExpiring(MonitoredSite $site, string $message, array $context = []): void
    {
        $this->dispatch($site, ['slack', 'telegram', 'mail', 'webhook'], fn (AlertChannelInterface $ch) => $ch->sendSslExpiringAlert($site, $message, $context));
    }

    protected function dispatch(MonitoredSite $site, array $channelNames, callable $send): void
    {
        $enabled = $site->getAlertChannelsList();
        if (empty($enabled)) {
            $enabled = array_keys($this->channels);
        }

        foreach ($channelNames as $name) {
            if (! in_array($name, $enabled, true)) {
                continue;
            }
            $channel = $this->channels[$name] ?? null;
            if (! $channel) {
                continue;
            }
            try {
                $send($channel);
            } catch (\Throwable $e) {
                Log::warning('PulseGuard alert failed: ' . $e->getMessage(), ['channel' => $name, 'site_id' => $site->id]);
            }
        }
    }
}
