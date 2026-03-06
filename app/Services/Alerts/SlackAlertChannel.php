<?php

namespace App\Services\Alerts;

use App\Models\AlertLog;
use App\Models\MonitoredSite;
use Illuminate\Support\Facades\Http;

class SlackAlertChannel implements AlertChannelInterface
{
    public function __construct(
        protected ?string $webhookUrl = null
    ) {
        $this->webhookUrl = $webhookUrl ?? config('pulseguard.alerts.slack.webhook_url');
    }

    public function sendDowntimeAlert(MonitoredSite $site, string $message, array $context = []): bool
    {
        return $this->send($site, '🔴 Site Down', $message, $context);
    }

    public function sendRecoveryAlert(MonitoredSite $site, string $message, array $context = []): bool
    {
        return $this->send($site, '🟢 Site Recovered', $message, $context);
    }

    public function sendSslExpiringAlert(MonitoredSite $site, string $message, array $context = []): bool
    {
        return $this->send($site, '⚠️ SSL Expiring Soon', $message, $context);
    }

    public function getName(): string
    {
        return 'slack';
    }

    protected function send(MonitoredSite $site, string $title, string $message, array $context): bool
    {
        if (empty($this->webhookUrl)) {
            return false;
        }

        $payload = [
            'blocks' => [
                [
                    'type' => 'header',
                    'text' => ['type' => 'plain_text', 'text' => $title, 'emoji' => true],
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        ['type' => 'mrkdwn', 'text' => "*Site:*\n{$site->name}"],
                        ['type' => 'mrkdwn', 'text' => "*URL:*\n<{$site->url}|{$site->url}>"],
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => ['type' => 'mrkdwn', 'text' => $message],
                ],
            ],
        ];

        try {
            $response = Http::post($this->webhookUrl, $payload);
            $success = $response->successful();

            AlertLog::create([
                'monitored_site_id' => $site->id,
                'channel' => 'slack',
                'type' => strtolower(str_replace(' ', '_', $title)),
                'success' => $success,
                'message' => $message,
                'response' => $response->body(),
                'sent_at' => now(),
            ]);

            return $success;
        } catch (\Throwable $e) {
            AlertLog::create([
                'monitored_site_id' => $site->id,
                'channel' => 'slack',
                'type' => 'downtime',
                'success' => false,
                'message' => $e->getMessage(),
                'response' => null,
                'sent_at' => now(),
            ]);

            return false;
        }
    }
}
