<?php

namespace Database\Seeders;

use App\Models\MonitoredSite;
use App\Models\User;
use Illuminate\Database\Seeder;

class MonitoredSiteSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        MonitoredSite::create([
            'user_id' => $user?->id,
            'name' => 'Example Site',
            'url' => 'https://example.com',
            'is_active' => true,
            'check_interval_minutes' => 1,
            'ssl_check_enabled' => true,
            'alert_channels' => ['mail'],
        ]);
    }
}
