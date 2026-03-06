<?php

namespace App\Services\Alerts;

use App\Models\AlertLog;
use App\Models\MonitoredSite;
use Illuminate\Support\Facades\Http;

class WebhookAlertChannel implements AlertChannelInterface
{
    public function __construct(
        protected ?string $url = null,
        protected ?string $secretHeader = null,
        protected ?string $secretValue = null
    ) {
        $this->url = $url ?? config('pulseguard.alerts.webhook.url');
        $this->secretHeader = $secretHeader ?? config('pulseguard.alerts.webhook.secret_header');
        $this->secretValue = $secretValue ?? config('pulseguard.alerts.webhook.secret_value');
    }

    public function sendDowntimeAlert(MonitoredSite $site, string $message, array $context = []): bool
    {
        return $this->send($site, 'downtime', $message, $context);
    }

    public function sendRecoveryAlert(MonitoredSite $site, string $message, array $context = []): bool
    {
        return $this->send($site, 'recovery', $message, $context);
    }

    public function sendSslExpiringAlert(MonitoredSite $site, string $message, array $context = []): bool
    {
        return $this->send($site, 'ssl_expiring', $message, $context);
    }

    public function getName(): string
    {
        return 'webhook';
    }

    protected function send(MonitoredSite $site, string $type, string $message, array $context): bool
    {
        if (empty($this->url)) {
            return false;
        }

        $payload = array_merge([
            'event' => $type,
            'site_id' => $site->id,
            'site_name' => $site->name,
            'site_url' => $site->url,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ], $context);

        $headers = ['Content-Type' => 'application/json'];
        if ($this->secretHeader && $this->secretValue) {
            $headers[$this->secretHeader] = $this->secretValue;
        }

        try {
            $response = Http::withHeaders($headers)->post($this->url, $payload);
            $success = $response->successful();

            AlertLog::create([
                'monitored_site_id' => $site->id,
                'channel' => 'webhook',
                'type' => $type,
                'success' => $success,
                'message' => $message,
                'response' => $response->body(),
                'sent_at' => now(),
            ]);

            return $success;
        } catch (\Throwable $e) {
            AlertLog::create([
                'monitored_site_id' => $site->id,
                'channel' => 'webhook',
                'type' => $type,
                'success' => false,
                'message' => $e->getMessage(),
                'response' => null,
                'sent_at' => now(),
            ]);

            return false;
        }
    }
}
