<?php

namespace App\Http\Controllers;

use App\Models\MonitoredSite;
use App\Services\Monitoring\UptimeCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected UptimeCalculator $uptime
    ) {}

    public function index(Request $request): View
    {
        $sites = MonitoredSite::query()
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->get('status') === 'active') {
                    $q->where('is_active', true);
                } elseif ($request->get('status') === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->get('search') . '%')
                ->orWhere('url', 'like', '%' . $request->get('search') . '%'))
            ->orderBy('name')
            ->get();

        $summary = $sites->map(function (MonitoredSite $site) {
            $days = 30;
            $uptime = $this->uptime->calculate($site, $days);
            $lastCheck = $site->httpChecks()->latest('checked_at')->first();
            $openIncident = $site->downtimeIncidents()->whereNull('resolved_at')->first();
            $lastSsl = $site->sslChecks()->latest('checked_at')->first();
            $recentIncidents = $site->downtimeIncidents()->orderByDesc('started_at')->limit(5)->get();

            return [
                'site' => $site,
                'uptime_percentage' => $uptime,
                'status' => $lastCheck?->status ?? 'unknown',
                'last_checked_at' => $lastCheck?->checked_at,
                'response_time_ms' => $lastCheck?->response_time_ms,
                'has_open_incident' => (bool) $openIncident,
                'open_incident' => $openIncident,
                'ssl_valid' => $lastSsl?->is_valid,
                'ssl_expires_at' => $lastSsl?->valid_until,
                'recent_incidents' => $recentIncidents,
            ];
        });

        return view('dashboard.index', [
            'summary' => $summary,
            'total' => $sites->count(),
        ]);
    }

    public function site(Request $request, MonitoredSite $site): View
    {
        $days = min(max((int) $request->get('days', 30), 1), 90);
        $uptime = $this->uptime->calculate($site, $days);
        $responseTime = $this->uptime->getResponseTimeStats($site, min($days, 7));
        $incidents = $site->downtimeIncidents()->orderByDesc('started_at')->paginate(10);
        $recentChecks = $site->httpChecks()->orderByDesc('checked_at')->limit(50)->get();
        $lastSsl = $site->sslChecks()->latest('checked_at')->first();

        return view('dashboard.site', [
            'site' => $site,
            'uptime' => $uptime,
            'days' => $days,
            'responseTime' => $responseTime,
            'incidents' => $incidents,
            'recentChecks' => $recentChecks,
            'lastSsl' => $lastSsl,
        ]);
    }
}
