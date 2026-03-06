<?php

namespace App\Events;

use App\Models\DowntimeIncident;
use App\Models\MonitoredSite;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SiteDownDetected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MonitoredSite $site,
        public DowntimeIncident $incident
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
        return 'site.down';
    }

    public function broadcastWith(): array
    {
        return [
            'site_id' => $this->site->id,
            'site_name' => $this->site->name,
            'site_url' => $this->site->url,
            'incident_id' => $this->incident->id,
            'started_at' => $this->incident->started_at->toIso8601String(),
            'status_code' => $this->incident->status_code,
            'summary' => $this->incident->summary,
        ];
    }
}
