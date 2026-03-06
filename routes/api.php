<?php

<<<<<<< HEAD
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\StatsController;
use Illuminate\Support\Facades\Route;

Route::post('auth/token', [AuthController::class, 'store'])->middleware('throttle:10,1');
Route::delete('auth/token', [AuthController::class, 'destroy'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::apiResource('sites', SiteController::class);
    Route::get('sites/{site}/stats', [StatsController::class, 'site'])->name('sites.stats');
    Route::get('sites/{site}/incidents', [IncidentController::class, 'index'])->name('sites.incidents');
    Route::get('sites/{site}/checks', [SiteController::class, 'checks'])->name('sites.checks');
    Route::get('dashboard', [StatsController::class, 'dashboard'])->name('dashboard');
});
=======
use App\Http\Controllers\Api\DeviceSyncController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// Reachability check: GET /api/device/ping (no auth). Use to verify URL from device network.
Route::get('device/ping', function () {
    Log::info('Device ping received', ['ip' => request()->ip(), 'user_agent' => request()->userAgent()]);
    return response()->json(['ok' => true, 'message' => 'Push URL is live']);
});

// T304F Mini Push Mode / sync agent: both URLs supported (MoU: neoarcade.fun/api/device/push)
Route::post('device/sync', [DeviceSyncController::class, 'sync']);
Route::post('device/push', [DeviceSyncController::class, 'sync']);
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
