<?php

<<<<<<< HEAD
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
=======
use App\Models\AttendanceLog;
use App\Models\Device;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('device:push-status {--limit=15 : Number of recent punches to show}', function () {
    $limit = (int) $this->option('limit');
    $this->line('');
    $this->line('--- Device push & cron status ---');
    $this->line('');
    $lastSchedule = Cache::get('last_schedule_run');
    if ($lastSchedule) {
        $this->info('Last scheduler run (cron): ' . $lastSchedule->format('Y-m-d H:i:s'));
    } else {
        $this->warn('Last scheduler run: not recorded (cron may not be set or schedule:run never ran).');
    }
    $this->line('');
    $devices = Device::with('location')->get();
    if ($devices->isEmpty()) {
        $this->warn('No devices. Create a device in admin and use its API key for push URL.');
    } else {
        $this->info('Devices (last_sync_at = last punch received by API):');
        foreach ($devices as $d) {
            $sync = $d->last_sync_at ? $d->last_sync_at->format('Y-m-d H:i:s') : 'never';
            $this->line('  ' . $d->name . ' (id=' . $d->id . ') @ ' . ($d->location?->name ?? '?') . ' — last_sync_at: ' . $sync . ' — active: ' . ($d->is_active ? 'yes' : 'no'));
        }
    }
    $this->line('');
    $recent = AttendanceLog::with('device.location', 'employee')
        ->orderByDesc('punch_time')
        ->limit($limit)
        ->get();
    if ($recent->isEmpty()) {
        $this->warn('No punches in database. Device may not be pushing, or API key/location wrong.');
    } else {
        $this->info('Recent punches (latest ' . $limit . '):');
        foreach ($recent as $log) {
            $emp = $log->employee_id ? ($log->employee?->name ?? 'id=' . $log->employee_id) : 'UNMAPPED';
            $this->line('  ' . $log->punch_time->format('Y-m-d H:i:s') . ' | device_user_id=' . $log->device_user_id . ' | employee=' . $emp . ' | device=' . ($log->device?->name ?? $log->device_id));
        }
    }
    $this->line('');
    $since = now()->subDay();
    $unmapped = AttendanceLog::whereNull('employee_id')->where('punch_time', '>=', $since)->count();
    if ($unmapped > 0) {
        $this->warn('Unmapped punches (last 24h): ' . $unmapped . ' — set employee Device User ID + Location to match device for these to appear in reports.');
    } else {
        $this->info('Unmapped punches (last 24h): 0');
    }
    $this->line('');
    $this->line('---');
})->purpose('Show if machine punches reached DB, device last sync, unmapped count, and last scheduler run');

Artisan::command('device:push-test', function () {
    $base = rtrim(config('app.url'), '/');
    $device = Device::where('is_active', true)->first();
    $this->line('');
    $this->line('--- Test device push from this server ---');
    $this->line('');
    $this->info('1. Test reachability (no key):');
    $this->line('   curl -s "' . $base . '/api/device/ping"');
    $this->line('');
    if ($device) {
        $key = $device->api_key;
        $this->info('2. Test push with header (device: ' . $device->name . '):');
        $this->line('   curl -X POST "' . $base . '/api/device/push" -H "X-Device-Key: ' . $key . '" -H "Content-Type: application/json" -d \'{"PIN":"00000001","DateTime":"' . now()->format('Y-m-d H:i:s') . '"}\'');
        $this->line('');
        $this->info('3. Test push with key in URL (if device cannot send header):');
        $this->line('   curl -X POST "' . $base . '/api/device/push?api_key=' . $key . '" -H "Content-Type: application/json" -d \'{"PIN":"00000001","DateTime":"' . now()->format('Y-m-d H:i:s') . '"}\'');
        $this->line('');
        $this->comment('Then: tail -f storage/logs/laravel.log and run one of the curl above. You should see "Device push request received" and either success or a warning.');
    } else {
        $this->warn('No active device. Create one in Admin → Devices first.');
    }
    $this->line('');
})->purpose('Print curl commands to test push URL and key (run on production)');

Schedule::command('attendance:process --days=3')->everyFifteenMinutes();

Schedule::call(function () {
    Cache::put('last_schedule_run', now(), now()->addHours(2));
})->everyMinute()->name('heartbeat');
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
