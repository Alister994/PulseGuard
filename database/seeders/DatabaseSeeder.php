<?php

namespace Database\Seeders;

<<<<<<< HEAD
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
=======
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
<<<<<<< HEAD
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(MonitoredSiteSeeder::class);
=======
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SettingsSeeder::class,
            LocationSeeder::class,
            DepartmentsAndShiftsSeeder::class,
            RoleWiseUsersSeeder::class,
        ]);
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
    }
}
