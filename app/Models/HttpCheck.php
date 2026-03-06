<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HttpCheck extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'monitored_site_id',
        'status_code',
        'response_time_ms',
        'status',
        'error_message',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
        ];
    }

    public function monitoredSite(): BelongsTo
    {
        return $this->belongsTo(MonitoredSite::class);
    }

    public function isUp(): bool
    {
        return $this->status === 'up' && $this->status_code >= 200 && $this->status_code < 300;
    }
}
