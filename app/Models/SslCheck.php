<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SslCheck extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'monitored_site_id',
        'is_valid',
        'valid_from',
        'valid_until',
        'issuer',
        'error_message',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'is_valid' => 'boolean',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'checked_at' => 'datetime',
        ];
    }

    public function monitoredSite(): BelongsTo
    {
        return $this->belongsTo(MonitoredSite::class);
    }

    public function daysUntilExpiry(): ?int
    {
        if (! $this->valid_until) {
            return null;
        }

        return (int) now()->diffInDays($this->valid_until, false);
    }
}
