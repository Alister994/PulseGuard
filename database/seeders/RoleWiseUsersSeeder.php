<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * MoU: Role-wise 2 users (e.g. 2 HR, 2 Manager) for live/demo use.
 * Creates 2 HR, 2 Department Manager, 1 additional Branch Admin (2 total with AdminSeeder), and 2 Employee users.
 */
class RoleWiseUsersSeeder extends Seeder
{
    public function run(): void
    {
        $location = Location::first();
        if (! $location) {
            return;
        }

        $password = '98245@biotime';
        $dept = Department::first();
        $shift = Shift::first();

        // 2 HR
        User::updateOrCreate(
            ['username' => 'hr1_biotime'],
            [
                'name' => 'HR One',
                'email' => 'hr1@biotime.local',
                'password' => $password,
                'role' => User::ROLE_HR,
                'is_active' => true,
                'location_id' => $location->id,
            ]
        );
        User::updateOrCreate(
            ['username' => 'hr2_biotime'],
            [
                'name' => 'HR Two',
                'email' => 'hr2@biotime.local',
                'password' => $password,
                'role' => User::ROLE_HR,
                'is_active' => true,
                'location_id' => $location->id,
            ]
        );

        // 2 Department Manager
        User::updateOrCreate(
            ['username' => 'manager1_biotime'],
            [
                'name' => 'Manager One',
                'email' => 'manager1@biotime.local',
                'password' => $password,
                'role' => User::ROLE_DEPARTMENT_MANAGER,
                'is_active' => true,
                'location_id' => $location->id,
            ]
        );
        User::updateOrCreate(
            ['username' => 'manager2_biotime'],
            [
                'name' => 'Manager Two',
                'email' => 'manager2@biotime.local',
                'password' => $password,
                'role' => User::ROLE_DEPARTMENT_MANAGER,
                'is_active' => true,
                'location_id' => $location->id,
            ]
        );

        // Ensure first branch admin (from AdminSeeder) has location
        User::where('username', 'admin_biotime')->update(['location_id' => $location->id]);

        // 1 additional Branch Admin (AdminSeeder already has admin_biotime)
        User::updateOrCreate(
            ['username' => 'branch_admin2_biotime'],
            [
                'name' => 'Branch Admin Two',
                'email' => 'branchadmin2@biotime.local',
                'password' => $password,
                'role' => User::ROLE_BRANCH_ADMIN,
                'is_active' => true,
                'location_id' => $location->id,
            ]
        );

        // 2 Employee-role users: create demo employees then users (unique: location_id + device_user_id)
        $emp1 = Employee::firstOrCreate(
            ['location_id' => $location->id, 'device_user_id' => '1'],
            [
                'employee_no' => 'EMP001',
                'name' => 'Employee One',
                'email' => 'employee1@biotime.local',
                'department_id' => $dept?->id,
                'shift_id' => $shift?->id,
                'salary_type' => 'monthly',
                'salary_value' => 0,
                'currency' => 'INR',
                'is_active' => true,
            ]
        );
        $emp2 = Employee::firstOrCreate(
            ['location_id' => $location->id, 'device_user_id' => '2'],
            [
                'employee_no' => 'EMP002',
                'name' => 'Employee Two',
                'email' => 'employee2@biotime.local',
                'department_id' => $dept?->id,
                'shift_id' => $shift?->id,
                'salary_type' => 'monthly',
                'salary_value' => 0,
                'currency' => 'INR',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['username' => 'employee1_biotime'],
            [
                'name' => 'Employee One',
                'email' => 'employee1@biotime.local',
                'password' => $password,
                'role' => User::ROLE_EMPLOYEE,
                'is_active' => true,
                'location_id' => $location->id,
                'employee_id' => $emp1->id,
            ]
        );
        User::updateOrCreate(
            ['username' => 'employee2_biotime'],
            [
                'name' => 'Employee Two',
                'email' => 'employee2@biotime.local',
                'password' => $password,
                'role' => User::ROLE_EMPLOYEE,
                'is_active' => true,
                'location_id' => $location->id,
                'employee_id' => $emp2->id,
            ]
        );
    }
}
