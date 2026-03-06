<?php

namespace App\Services\Monitoring;

use App\Events\SiteDownDetected;
use App\Events\SiteRecovered;
use App\Models\DowntimeIncident;
use App\Models\HttpCheck;
use App\Models\MonitoredSite;
use Illuminate\Support\Facades\Log;

class MonitoringEngine
{
    public function __construct(
        protected HttpPingService $httpPing,
        protected UptimeCalculator $uptimeCalculator
    ) {}

    /**
     * Run a single HTTP check for a site and persist result.
     */
    public function runHttpCheck(MonitoredSite $site): HttpCheck
    {
        $result = $this->httpPing->ping($site->url);

        $check = $site->httpChecks()->create([
            'status_code' => $result->statusCode,
            'response_time_ms' => $result->responseTimeMs,
            'status' => $result->status,
            'error_message' => $result->errorMessage,
            'checked_at' => now(),
        ]);

        $this->handleCheckResult($site, $check, $result->status);

        return $check;
    }

    protected function handleCheckResult(MonitoredSite $site, HttpCheck $check, string $status): void
    {
        $openIncident = $site->downtimeIncidents()
            ->whereNull('resolved_at')
            ->first();

        if ($status === 'up') {
            if ($openIncident) {
                $openIncident->update([
                    'resolved_at' => now(),
                    'status' => 'resolved',
                ]);
                event(new SiteRecovered($site, $openIncident));
            }
            return;
        }

        // Down or timeout
        if ($openIncident) {
            return; // Already in incident
        }

        $incident = $site->downtimeIncidents()->create([
            'started_at' => now(),
            'status_code' => $check->status_code,
            'status' => $status,
            'summary' => $check->error_message ?? "HTTP {$check->status_code}",
        ]);

        event(new SiteDownDetected($site, $incident));
    }

    public function getUptimePercentage(MonitoredSite $site, ?int $days = 30): float
    {
        return $this->uptimeCalculator->calculate($site, $days);
    }
}
