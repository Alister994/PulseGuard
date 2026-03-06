<?php

namespace App\Events;

use App\Models\MonitoredSite;
use App\Models\SslCheck;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SslExpiringSoon implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MonitoredSite $site,
        public SslCheck $sslCheck
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('pulseguard'),
            new Channel('pulseguard.site.' . $this->site->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ssl.expiring';
    }

    public function broadcastWith(): array
    {
        return [
            'site_id' => $this->site->id,
            'site_name' => $this->site->name,
            'site_url' => $this->site->url,
            'valid_until' => $this->sslCheck->valid_until?->toIso8601String(),
            'days_remaining' => $this->sslCheck->daysUntilExpiry(),
        ];
    }
}
