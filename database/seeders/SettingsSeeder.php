<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'working_hours_per_day' => '8',
            'break_hours_per_day' => '1',
            'grace_minutes' => '10',
            'half_day_hours' => '4',
            'shift_start_time' => '09:00',
            'watermark_text' => 'BIOTIME',
            'late_rule_same_for_all' => '1',
            'backup_enabled' => '0',
            'backup_disk' => 'local',
            'admin_editable_punch' => '1',
        ];

        foreach ($defaults as $key => $value) {
            if (Setting::find($key) === null) {
                Setting::set($key, $value);
            }
        }
    }
}
