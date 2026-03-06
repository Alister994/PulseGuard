<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DowntimeIncident extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'monitored_site_id',
        'started_at',
        'resolved_at',
        'status_code',
        'status',
        'summary',
        'alert_sent',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
            'alert_sent' => 'boolean',
        ];
    }

    public function monitoredSite(): BelongsTo
    {
        return $this->belongsTo(MonitoredSite::class);
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function durationInSeconds(): ?int
    {
        if (! $this->resolved_at) {
            return null;
        }

        return (int) $this->started_at->diffInSeconds($this->resolved_at);
    }
}
