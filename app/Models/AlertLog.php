<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'monitored_site_id',
        'channel',
        'type',
        'success',
        'message',
        'response',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    public function monitoredSite(): BelongsTo
    {
        return $this->belongsTo(MonitoredSite::class);
    }
}
