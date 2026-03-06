<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DepartmentWeeklyOff;
use App\Models\Shift;
use App\Models\ShiftBreak;
use Illuminate\Database\Seeder;

class DepartmentsAndShiftsSeeder extends Seeder
{
    public function run(): void
    {
        $waterjet = Department::firstOrCreate(
            ['name' => 'Waterjet'],
            ['description' => 'Waterjet department', 'is_active' => true]
        );
        DepartmentWeeklyOff::firstOrCreate(['department_id' => $waterjet->id, 'day_of_week' => 0]); // Sunday

        $day = Shift::firstOrCreate(
            ['department_id' => $waterjet->id, 'name' => 'Day'],
            [
                'start_time' => '08:00',
                'end_time' => '19:00',
                'is_night_shift' => false,
                'grace_minutes' => 0,
                'is_active' => true,
            ]
        );
        ShiftBreak::firstOrCreate(
            ['shift_id' => $day->id, 'break_type' => 'lunch', 'sort_order' => 0],
            ['duration_minutes' => 60]
        );
        ShiftBreak::firstOrCreate(
            ['shift_id' => $day->id, 'break_type' => 'tea', 'sort_order' => 1],
            ['duration_minutes' => 20]
        );

        $night = Shift::firstOrCreate(
            ['department_id' => $waterjet->id, 'name' => 'Night'],
            [
                'start_time' => '19:00',
                'end_time' => '08:00',
                'is_night_shift' => true,
                'grace_minutes' => 0,
                'is_active' => true,
            ]
        );
        ShiftBreak::firstOrCreate(
            ['shift_id' => $night->id, 'break_type' => 'dinner', 'sort_order' => 0],
            ['duration_minutes' => 60]
        );
        ShiftBreak::firstOrCreate(
            ['shift_id' => $night->id, 'break_type' => 'tea', 'sort_order' => 1],
            ['duration_minutes' => 20]
        );

        $cleaning = Department::firstOrCreate(
            ['name' => 'Cleaning'],
            ['description' => 'Cleaning department', 'is_active' => true]
        );
        DepartmentWeeklyOff::firstOrCreate(['department_id' => $cleaning->id, 'day_of_week' => 0]); // Sunday
        $cleaningShift = Shift::firstOrCreate(
            ['department_id' => $cleaning->id, 'name' => 'Day'],
            [
                'start_time' => '09:00',
                'end_time' => '17:00',
                'is_night_shift' => false,
                'grace_minutes' => 0,
                'is_active' => true,
            ]
        );
        ShiftBreak::firstOrCreate(
            ['shift_id' => $cleaningShift->id, 'break_type' => 'lunch', 'sort_order' => 0],
            ['start_time' => '12:00', 'end_time' => '13:30', 'duration_minutes' => 90]
        );

        $heap = Department::firstOrCreate(
            ['name' => 'HEAP'],
            ['description' => 'HEAP department', 'is_active' => true]
        );
        DepartmentWeeklyOff::firstOrCreate(['department_id' => $heap->id, 'day_of_week' => 6]); // Saturday
        $heapDay = Shift::firstOrCreate(
            ['department_id' => $heap->id, 'name' => 'Day'],
            [
                'start_time' => '08:30',
                'end_time' => '18:00',
                'is_night_shift' => false,
                'grace_minutes' => 0,
                'is_active' => true,
            ]
        );
        ShiftBreak::firstOrCreate(
            ['shift_id' => $heapDay->id, 'break_type' => 'lunch', 'sort_order' => 0],
            ['duration_minutes' => 60]
        );
        $heapNight = Shift::firstOrCreate(
            ['department_id' => $heap->id, 'name' => 'Night'],
            [
                'start_time' => '20:30',
                'end_time' => '05:30',
                'is_night_shift' => true,
                'grace_minutes' => 0,
                'is_active' => true,
            ]
        );
        ShiftBreak::firstOrCreate(
            ['shift_id' => $heapNight->id, 'break_type' => 'lunch', 'sort_order' => 0],
            ['duration_minutes' => 60]
        );
    }
}
