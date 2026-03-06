<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MonitoredSite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function index(Request $request, MonitoredSite $site): JsonResponse
    {
        $incidents = $site->downtimeIncidents()
            ->orderByDesc('started_at')
            ->paginate(min((int) $request->get('per_page', 15), 50));

        return response()->json($incidents);
    }
}
