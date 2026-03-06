<?php

namespace App\Services\Alerts;

use App\Models\AlertLog;
use App\Models\MonitoredSite;
use Illuminate\Support\Facades\Http;

class TelegramAlertChannel implements AlertChannelInterface
{
    public function __construct(
        protected ?string $botToken = null,
        protected ?string $chatId = null
    ) {
        $this->botToken = $this->botToken ?? config('pulseguard.alerts.telegram.bot_token');
        $this->chatId = $this->chatId ?? config('pulseguard.alerts.telegram.chat_id');
    }

    public function sendDowntimeAlert(MonitoredSite $site, string $message, array $context = []): bool
    {
        return $this->send($site, "🔴 *Site Down*\n\n", $message);
    }

    public function sendRecoveryAlert(MonitoredSite $site, string $message, array $context = []): bool
    {
        return $this->send($site, "🟢 *Site Recovered*\n\n", $message);
    }

    public function sendSslExpiringAlert(MonitoredSite $site, string $message, array $context = []): bool
    {
        return $this->send($site, "⚠️ *SSL Expiring Soon*\n\n", $message);
    }

    public function getName(): string
    {
        return 'telegram';
    }

    protected function send(MonitoredSite $site, string $prefix, string $message): bool
    {
        if (empty($this->botToken) || empty($this->chatId)) {
            return false;
        }

        $text = $prefix . "*{$site->name}* ({$site->url})\n\n" . $message;

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $this->chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);

            $success = $response->successful();

            AlertLog::create([
                'monitored_site_id' => $site->id,
                'channel' => 'telegram',
                'type' => 'alert',
                'success' => $success,
                'message' => $message,
                'response' => $response->body(),
                'sent_at' => now(),
            ]);

            return $success;
        } catch (\Throwable $e) {
            AlertLog::create([
                'monitored_site_id' => $site->id,
                'channel' => 'telegram',
                'type' => 'alert',
                'success' => false,
                'message' => $e->getMessage(),
                'response' => null,
                'sent_at' => now(),
            ]);

            return false;
        }
    }
}
