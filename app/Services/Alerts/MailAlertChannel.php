<?php

namespace App\Services\Alerts;

use App\Models\AlertLog;
use App\Models\MonitoredSite;
use Illuminate\Support\Facades\Mail;

class MailAlertChannel implements AlertChannelInterface
{
    public function sendDowntimeAlert(MonitoredSite $site, string $message, array $context = []): bool
    {
        return $this->send($site, '[PulseGuard] Site Down: ' . $site->name, $message);
    }

    public function sendRecoveryAlert(MonitoredSite $site, string $message, array $context = []): bool
    {
        return $this->send($site, '[PulseGuard] Site Recovered: ' . $site->name, $message);
    }

    public function sendSslExpiringAlert(MonitoredSite $site, string $message, array $context = []): bool
    {
        return $this->send($site, '[PulseGuard] SSL Expiring Soon: ' . $site->name, $message);
    }

    public function getName(): string
    {
        return 'mail';
    }

    protected function send(MonitoredSite $site, string $subject, string $body): bool
    {
        $addresses = config('pulseguard.alerts.mail.addresses', []);
        if (empty($addresses)) {
            $addresses = [config('mail.from.address')];
        }

        try {
            Mail::raw($body . "\n\nSite: {$site->name}\nURL: {$site->url}", function ($m) use ($addresses, $subject) {
                $m->to($addresses)->subject($subject);
            });

            AlertLog::create([
                'monitored_site_id' => $site->id,
                'channel' => 'mail',
                'type' => 'alert',
                'success' => true,
                'message' => $subject,
                'response' => null,
                'sent_at' => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            AlertLog::create([
                'monitored_site_id' => $site->id,
                'channel' => 'mail',
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
