<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MonitoredSite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sites = MonitoredSite::query()
            ->when($request->boolean('active'), fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(min((int) $request->get('per_page', 15), 50));

        return response()->json($sites);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'check_interval_minutes' => 'nullable|integer|min:1|max:60',
            'ssl_check_enabled' => 'nullable|boolean',
            'alert_channels' => 'nullable|array',
            'alert_channels.*' => 'string|in:slack,telegram,mail,webhook',
        ]);

        $site = MonitoredSite::create([
            'user_id' => $request->user()?->id,
            'name' => $validated['name'],
            'url' => $validated['url'],
            'check_interval_minutes' => $validated['check_interval_minutes'] ?? 1,
            'ssl_check_enabled' => $validated['ssl_check_enabled'] ?? true,
            'alert_channels' => $validated['alert_channels'] ?? null,
        ]);

        return response()->json($site, 201);
    }

    public function show(MonitoredSite $site): JsonResponse
    {
        return response()->json($site);
    }

    public function update(Request $request, MonitoredSite $site): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'url' => 'sometimes|url|max:2048',
            'is_active' => 'sometimes|boolean',
            'check_interval_minutes' => 'sometimes|integer|min:1|max:60',
            'ssl_check_enabled' => 'sometimes|boolean',
            'alert_channels' => 'nullable|array',
            'alert_channels.*' => 'string|in:slack,telegram,mail,webhook',
        ]);

        $site->update($validated);

        return response()->json($site);
    }

    public function destroy(MonitoredSite $site): JsonResponse
    {
        $site->delete();
        return response()->json(null, 204);
    }

    public function checks(Request $request, MonitoredSite $site): JsonResponse
    {
        $checks = $site->httpChecks()
            ->orderByDesc('checked_at')
            ->limit(min((int) $request->get('limit', 100), 500))
            ->get();

        return response()->json(['data' => $checks]);
    }
}
