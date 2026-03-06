<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        Location::firstOrCreate(
            ['name' => 'Main Branch'],
            [
                'address' => 'Head Office',
                'timezone' => 'Asia/Kolkata',
                'is_active' => true,
            ]
        );
    }
}
