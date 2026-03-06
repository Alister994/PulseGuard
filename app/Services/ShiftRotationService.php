<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Shift rotation: 15 days day shift / 15 days night shift.
 * Employees can have two shifts (day + night) assigned via department; this service
 * determines which shift applies on a given date based on rotation_phase and rotation_start_date.
 */
class ShiftRotationService
{
    public const ROTATION_DAYS_DAY = 15;
    public const ROTATION_DAYS_NIGHT = 15;
    public const ROTATION_CYCLE_DAYS = 30;

    /**
     * Get the effective shift for an employee on a date (for rotation pattern).
     * If employee has shift_rotation_start_date set, use 15d/15d rotation; else return assigned shift.
     */
    public function getEffectiveShiftForDate(Employee $employee, Carbon $date): ?Shift
    {
        $assignedShift = $employee->shift;
        if (! $assignedShift) {
            return null;
        }
        $startDate = $employee->shift_rotation_start_date;
        if (! $startDate) {
            return $assignedShift;
        }

        $start = Carbon::parse($employee->shift_rotation_start_date);
        $dayIndex = (int) $start->diffInDays($date, false);
        if ($dayIndex < 0) {
            return $assignedShift;
        }
        $phaseInCycle = $dayIndex % self::ROTATION_CYCLE_DAYS;
        $isNightPeriod = $phaseInCycle >= self::ROTATION_DAYS_DAY;

        $department = $employee->department;
        if (! $department) {
            return $assignedShift;
        }

        $dayShift = $department->shifts()->where('is_night_shift', false)->first();
        $nightShift = $department->shifts()->where('is_night_shift', true)->first();

        if ($isNightPeriod && $nightShift) {
            return $nightShift;
        }
        if (! $isNightPeriod && $dayShift) {
            return $dayShift;
        }
        return $assignedShift;
    }

    /**
     * Initialize or update rotation for an employee (e.g. start of employment or roster change).
     */
    public function setRotationStart(Employee $employee, ?Carbon $startDate = null): void
    {
        $startDate = $startDate ?? now()->startOfDay();
        $employee->update([
            'shift_rotation_start_date' => $startDate,
            'rotation_phase' => 0,
        ]);
    }

    /**
     * Return current phase (0 = day period, 1 = night period) for an employee on a date.
     */
    public function getPhaseOnDate(Employee $employee, Carbon $date): int
    {
        $start = $employee->shift_rotation_start_date;
        if (! $start) {
            return 0;
        }
        $start = Carbon::parse($start);
        $dayIndex = (int) $start->diffInDays($date, false);
        if ($dayIndex < 0) {
            return 0;
        }
        $phaseInCycle = $dayIndex % self::ROTATION_CYCLE_DAYS;
        return $phaseInCycle >= self::ROTATION_DAYS_DAY ? 1 : 0;
    }
}
