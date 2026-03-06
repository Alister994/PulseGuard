<?php

namespace App\Services;

use App\Models\AttendanceDaily;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\ForgetPunchRequest;
use App\Models\LeaveRequest;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\DailyHoursShortfallNotification;
use App\Notifications\LateEntryNotification;
use Carbon\Carbon;

class AttendanceProcessor
{
    /**
     * Process attendance for a date: raw logs → daily record.
     * Handles: 4–6 punches, cross-midnight shift, grace time, half-day rule (editable hours),
     * weekly off, leave, duplicate/double punch (via remarks), missing punch (remarks).
     */
    public function processForDate(Carbon $date): void
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        $logs = AttendanceLog::whereBetween('punch_time', [$startOfDay, $endOfDay])
            ->orderBy('punch_time')
            ->get();

        $byEmployee = $logs->groupBy(function ($log) {
            if ($log->employee_id) {
                return 'e_' . $log->employee_id;
            }
            $emp = Employee::findByDeviceUserIdForLocation($log->device->location_id, $log->device_user_id);
            return $emp ? 'e_' . $emp->id : 'skip_' . $log->device_user_id;
        });

        foreach ($byEmployee as $key => $dayLogs) {
            if (! str_starts_with($key, 'e_')) {
                continue;
            }
            $employeeId = (int) substr($key, 2);
            $employee = Employee::find($employeeId);
            if (! $employee || ! $employee->is_active) {
                continue;
            }

            $sorted = $dayLogs->sortBy('punch_time')->values();
            $punch1 = $sorted->get(0)?->punch_time;
            $punch2 = $sorted->get(1)?->punch_time;
            $punch3 = $sorted->get(2)?->punch_time;
            $punch4 = $sorted->get(3)?->punch_time;
            $punch5 = $sorted->get(4)?->punch_time;
            $punch6 = $sorted->get(5)?->punch_time;
            [$punch1, $punch2, $punch3, $punch4, $punch5, $punch6] = $this->getPunchesWithForgetCorrections($employee, $date, [$punch1, $punch2, $punch3, $punch4, $punch5, $punch6]);

            $remarks = [];
            $status = $this->resolveInitialStatus($employee, $date, $punch1, $punch6 ?? $punch4, $remarks);

            $lunchMinutes = 0;
            $teaMinutes = 0;
            $workMinutes = 0;
            $breakMinutes = 0;
            $lateMinutes = 0;
            $overtimeMinutes = 0;
            $expectedWorkMinutes = $this->getExpectedWorkMinutes($employee, 0);

            if ($punch1 && ($punch6 ?? $punch4)) {
                $dutyEnd = $punch6 ?? $punch4;
                $totalSpan = $punch1->diffInMinutes($dutyEnd);

                if ($punch2 && $punch3) {
                    $lunchMinutes = $punch2->diffInMinutes($punch3);
                }
                if ($punch4 && $punch5) {
                    $teaMinutes = $punch4->diffInMinutes($punch5);
                }
                $breakMinutes = $lunchMinutes + $teaMinutes;
                $workMinutes = max(0, $totalSpan - $breakMinutes);

                $shift = $employee->shift_rotation_start_date
                    ? app(ShiftRotationService::class)->getEffectiveShiftForDate($employee, $date)
                    : $employee->shift;
                $shift = $shift ?? $employee->shift;
                $graceMinutes = (int) ($shift?->grace_minutes ?? Setting::get('grace_minutes', 10));
                $lateMinutes = $this->computeLateMinutes($employee, $date, $punch1, $shift, $graceMinutes);

                $expectedWorkMinutes = $this->getExpectedWorkMinutesWithShift($employee, $shift, $breakMinutes);
                if ($expectedWorkMinutes > 0 && $workMinutes > $expectedWorkMinutes) {
                    $overtimeMinutes = $workMinutes - $expectedWorkMinutes;
                }

                $halfDayMinutes = (int) round((float) Setting::get('half_day_hours', 4) * 60);
                if ($expectedWorkMinutes > 0 && $workMinutes > 0 && $workMinutes < $halfDayMinutes && $status === 'present') {
                    $status = 'half_day';
                    $remarks[] = 'Below half-day hours (' . ($halfDayMinutes / 60) . 'h)';
                } elseif ($expectedWorkMinutes > 0 && $workMinutes >= $halfDayMinutes && $workMinutes < $expectedWorkMinutes && $status === 'present') {
                    $remarks[] = 'Shortfall: ' . ($expectedWorkMinutes - $workMinutes) . ' min';
                }

                $punchCount = $sorted->count();
                if ($punchCount < 2) {
                    $remarks[] = 'Missing punch(s); only ' . $punchCount . ' punch(es)';
                } elseif ($punchCount % 2 !== 0 && $punchCount < 6) {
                    $remarks[] = 'Odd number of punches; possible missing out punch';
                }
            } elseif (! $punch1) {
                if ($status === 'present') {
                    $status = 'absent';
                }
                $remarks[] = 'No punch-in recorded';
            }

            $daily = AttendanceDaily::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'punch_1_at' => $punch1,
                    'punch_2_at' => $punch2,
                    'punch_3_at' => $punch3,
                    'punch_4_at' => $punch4,
                    'punch_5_at' => $punch5,
                    'punch_6_at' => $punch6,
                    'work_minutes' => max(0, $workMinutes),
                    'break_minutes' => $breakMinutes,
                    'lunch_minutes' => $lunchMinutes,
                    'tea_minutes' => $teaMinutes,
                    'late_minutes' => $lateMinutes,
                    'overtime_minutes' => $overtimeMinutes,
                    'status' => $status,
                    'remarks' => implode('; ', array_unique($remarks)) ?: null,
                ]
            );

            $this->sendNotifications($daily, $employee, $workMinutes, $expectedWorkMinutes ?? 0, $lateMinutes);
        }
    }

    /** Merge approved forget-punch requested_time into punch slots when slot is empty. */
    private function getPunchesWithForgetCorrections(Employee $employee, Carbon $date, array $punches): array
    {
        $approved = ForgetPunchRequest::where('employee_id', $employee->id)
            ->where('date', $date->toDateString())
            ->where('status', 'approved')
            ->orderBy('punch_slot')
            ->get();

        $out = $punches;
        foreach ($approved as $req) {
            $slot = $req->punch_slot;
            if ($slot < 1 || $slot > 6) {
                continue;
            }
            $idx = $slot - 1;
            if (isset($out[$idx]) && $out[$idx] !== null) {
                continue;
            }
            $out[$idx] = $req->requested_time;
        }
        return $out;
    }

    private function resolveInitialStatus(Employee $employee, Carbon $date, $punch1, $lastPunch, array &$remarks): string
    {
        $department = $employee->department;
        if ($department && $department->isWeeklyOff((int) $date->dayOfWeek)) {
            $remarks[] = 'Weekly off';
            return $lastPunch ? 'present' : 'weekly_off';
        }

        $leave = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', [LeaveRequest::STATUS_APPROVED_PAID, LeaveRequest::STATUS_APPROVED_UNPAID])
            ->whereDate('from_date', '<=', $date)
            ->whereDate('to_date', '>=', $date)
            ->first();
        if ($leave) {
            $remarks[] = 'Leave: ' . $leave->leave_type;
            return 'leave';
        }

        return 'present';
    }

    private function computeLateMinutes(Employee $employee, Carbon $date, $punch1, $shift, int $graceMinutes): int
    {
        if (! $punch1) {
            return 0;
        }
        if ($shift) {
            $shiftStart = Carbon::parse($date->toDateString() . ' ' . $shift->start_time);
            if ($shift->is_night_shift) {
                $shiftStart = Carbon::parse($date->copy()->subDay()->toDateString() . ' ' . $shift->start_time);
            }
        } else {
            $shiftStartTime = Setting::get('shift_start_time', '09:00');
            $shiftStart = Carbon::parse($date->toDateString() . ' ' . $shiftStartTime);
        }
        $lateThreshold = $shiftStart->copy()->addMinutes($graceMinutes);
        if ($punch1->gt($lateThreshold)) {
            return (int) $lateThreshold->diffInMinutes($punch1);
        }
        return 0;
    }

    private function getExpectedWorkMinutes(Employee $employee, int $breakMinutes): int
    {
        $shift = $employee->shift;
        return $this->getExpectedWorkMinutesWithShift($employee, $shift, $breakMinutes);
    }

    private function getExpectedWorkMinutesWithShift(Employee $employee, $shift, int $breakMinutes): int
    {
        if ($shift) {
            $start = Carbon::parse($shift->start_time);
            $end = Carbon::parse($shift->end_time);
            if ($shift->is_night_shift && $end->lt($start)) {
                $end = $end->copy()->addDay();
            }
            return max(0, $start->diffInMinutes($end) - $breakMinutes);
        }
        $workingHours = (float) Setting::get('working_hours_per_day', 8);
        return (int) round($workingHours * 60);
    }

    private function sendNotifications(AttendanceDaily $daily, Employee $employee, int $workMinutes, int $expectedWorkMinutes, int $lateMinutes): void
    {
        $adminRoles = [User::ROLE_SUPER_ADMIN, User::ROLE_BRANCH_ADMIN];
        $admins = User::whereIn('role', $adminRoles)->where('is_active', true)->get();
        $branchAdmins = $admins->filter(fn ($u) => $u->canManageBranch($employee->location_id));

        if ($lateMinutes > 0) {
            foreach ($branchAdmins as $admin) {
                $admin->notify(new LateEntryNotification($daily));
            }
        }
        if ($expectedWorkMinutes > 0 && $workMinutes > 0 && $workMinutes < $expectedWorkMinutes) {
            foreach ($branchAdmins as $admin) {
                $admin->notify(new DailyHoursShortfallNotification($daily, $expectedWorkMinutes, $workMinutes));
            }
        }
    }

    public function processLastDays(int $days = 3): void
    {
        for ($i = 0; $i <= $days; $i++) {
            $this->processForDate(now()->subDays($i));
        }
    }
}
