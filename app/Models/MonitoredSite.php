<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoredSite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'url',
        'is_active',
        'check_interval_minutes',
        'ssl_check_enabled',
        'alert_channels',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'ssl_check_enabled' => 'boolean',
            'alert_channels' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function httpChecks(): HasMany
    {
        return $this->hasMany(HttpCheck::class);
    }

    public function downtimeIncidents(): HasMany
    {
        return $this->hasMany(DowntimeIncident::class);
    }

    public function sslChecks(): HasMany
    {
        return $this->hasMany(SslCheck::class);
    }

    public function alertLogs(): HasMany
    {
        return $this->hasMany(AlertLog::class);
    }

    public function getAlertChannelsList(): array
    {
        $channels = $this->alert_channels;
        return is_array($channels) ? $channels : [];
    }
}
