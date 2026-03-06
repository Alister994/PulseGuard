<?php

namespace App\Services\Monitoring;

use App\Models\HttpCheck;
use App\Models\MonitoredSite;
use Illuminate\Support\Facades\DB;

class UptimeCalculator
{
    /**
     * Calculate uptime percentage for the last N days based on HTTP check results.
     */
    public function calculate(MonitoredSite $site, int $days = 30): float
    {
        $since = now()->subDays($days);

        $total = $site->httpChecks()
            ->where('checked_at', '>=', $since)
            ->count();

        if ($total === 0) {
            return 100.0;
        }

        $upCount = $site->httpChecks()
            ->where('checked_at', '>=', $since)
            ->where('status', 'up')
            ->count();

        return round(($upCount / $total) * 100, 2);
    }

    /**
     * Get response time stats (avg, min, max) for charting.
     *
     * @return array{avg: int, min: int, max: int, points: array<int, int>}
     */
    public function getResponseTimeStats(MonitoredSite $site, int $days = 7): array
    {
        $since = now()->subDays($days);

        $checks = $site->httpChecks()
            ->where('checked_at', '>=', $since)
            ->whereNotNull('response_time_ms')
            ->orderBy('checked_at')
            ->get(['checked_at', 'response_time_ms']);

        $points = $checks->map(fn ($c) => [
            'at' => $c->checked_at->toIso8601String(),
            'ms' => (int) $c->response_time_ms,
        ])->values()->all();

        $values = $checks->pluck('response_time_ms')->filter()->values();

        return [
            'avg' => $values->isEmpty() ? 0 : (int) round($values->avg()),
            'min' => $values->isEmpty() ? 0 : (int) $values->min(),
            'max' => $values->isEmpty() ? 0 : (int) $values->max(),
            'points' => $points,
        ];
    }
}
