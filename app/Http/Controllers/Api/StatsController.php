<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MonitoredSite;
use App\Services\Monitoring\UptimeCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function __construct(
        protected UptimeCalculator $uptime
    ) {}

    public function site(Request $request, MonitoredSite $site): JsonResponse
    {
        $days = (int) $request->get('days', 30);
        $days = min(max($days, 1), 90);

        $uptime = $this->uptime->calculate($site, $days);
        $responseTime = $this->uptime->getResponseTimeStats($site, min($days, 7));

        $openIncident = $site->downtimeIncidents()->whereNull('resolved_at')->first();
        $lastCheck = $site->httpChecks()->latest('checked_at')->first();
        $lastSsl = $site->sslChecks()->latest('checked_at')->first();

        return response()->json([
            'uptime_percentage' => $uptime,
            'days' => $days,
            'response_time' => $responseTime,
            'current_status' => $lastCheck?->status ?? 'unknown',
            'last_checked_at' => $lastCheck?->checked_at?->toIso8601String(),
            'ssl_valid' => $lastSsl?->is_valid,
            'ssl_expires_at' => $lastSsl?->valid_until?->toIso8601String(),
            'open_incident' => $openIncident ? [
                'id' => $openIncident->id,
                'started_at' => $openIncident->started_at->toIso8601String(),
            ] : null,
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $sites = MonitoredSite::where('is_active', true)->get();

        $summary = $sites->map(function (MonitoredSite $site) {
            $days = 30;
            $uptime = $this->uptime->calculate($site, $days);
            $lastCheck = $site->httpChecks()->latest('checked_at')->first();
            $openIncident = $site->downtimeIncidents()->whereNull('resolved_at')->first();

            return [
                'id' => $site->id,
                'name' => $site->name,
                'url' => $site->url,
                'uptime_percentage' => $uptime,
                'status' => $lastCheck?->status ?? 'unknown',
                'last_checked_at' => $lastCheck?->checked_at?->toIso8601String(),
                'has_open_incident' => (bool) $openIncident,
            ];
        });

        return response()->json([
            'sites' => $summary,
            'total_sites' => $sites->count(),
        ]);
    }
}
