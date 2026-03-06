<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'super_admin_biotime'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@biotime.local',
                'password' => '98245@biotime',
                'role' => User::ROLE_SUPER_ADMIN,
                'is_active' => true,
            ]
        );
        User::updateOrCreate(
            ['username' => 'admin_biotime'],
            [
                'name' => 'Branch Admin',
                'email' => 'admin@biotime.local',
                'password' => '98245@biotime',
                'role' => User::ROLE_BRANCH_ADMIN,
                'is_active' => true,
            ]
        );
    }
}
