<?php

namespace App\Services;

use App\Models\AttendanceDaily;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\SalarySlip;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalaryService
{
    /**
     * Calculate and create/update salary slip for an employee for a given month.
     * Admin can choose salary_type: monthly, hourly, daily.
     */
    public function calculateForMonth(Employee $employee, int $month, int $year): SalarySlip
    {
        $workingHours = (float) Setting::get('working_hours_per_day', 8);
        $expectedWorkMinutes = (int) round($workingHours * 60);
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end = Carbon::createFromDate($year, $month, $daysInMonth)->endOfDay();

        $dailyRecords = AttendanceDaily::where('employee_id', $employee->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $leaveRecords = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', [LeaveRequest::STATUS_APPROVED_PAID, LeaveRequest::STATUS_APPROVED_UNPAID, LeaveRequest::STATUS_REJECTED])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('from_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('to_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('from_date', '<=', $start)->where('to_date', '>=', $end);
                    });
            })
            ->get();

        $baseAmount = (float) $employee->salary_value;
        $additions = 0.0;
        $deductions = 0.0;
        $breakdown = [];

        if ($employee->salary_type === 'monthly') {
            $monthlyRate = $baseAmount;
            $perDayRate = $monthlyRate / $daysInMonth;
            $perMinuteRate = $perDayRate / $expectedWorkMinutes;

            $presentDays = $dailyRecords->where('status', 'present')->count();
            $halfDays = $dailyRecords->where('status', 'half_day')->count();
            $paidLeaveDays = 0;
            $unpaidLeaveDays = 0;
            foreach ($leaveRecords as $leave) {
                $overlapDays = $this->overlapDays($leave->from_date, $leave->to_date, $start->toDateString(), $end->toDateString());
                if ($leave->status === 'approved_paid') {
                    $paidLeaveDays += $overlapDays;
                } elseif ($leave->status === 'approved_unpaid') {
                    $unpaidLeaveDays += $overlapDays;
                }
            }

            $payableDays = $presentDays + ($halfDays * 0.5) + $paidLeaveDays;
            $baseAmount = $payableDays * $perDayRate;
            $deductions += $unpaidLeaveDays * $perDayRate;
            $breakdown['present_days'] = $presentDays;
            $breakdown['half_days'] = $halfDays;
            $breakdown['paid_leave_days'] = $paidLeaveDays;
            $breakdown['unpaid_leave_days'] = $unpaidLeaveDays;

            $lateMinutes = $dailyRecords->sum('late_minutes');
            if ($lateMinutes > 0 && $perMinuteRate > 0) {
                $lateDeduction = round($lateMinutes * $perMinuteRate, 2);
                $deductions += $lateDeduction;
                $breakdown['late_deduction'] = -$lateDeduction;
            }

            $overtimeMinutes = $dailyRecords->sum('overtime_minutes');
            if ($overtimeMinutes > 0 && $perMinuteRate > 0) {
                $overtimePay = round($overtimeMinutes * $perMinuteRate * 1.5, 2);
                $additions += $overtimePay;
                $breakdown['overtime'] = $overtimePay;
            }
        } elseif ($employee->salary_type === 'hourly') {
            $hourlyRate = $baseAmount;
            $perMinuteRate = $hourlyRate / 60;
            $workMinutes = $dailyRecords->sum('work_minutes');
            $baseAmount = ($workMinutes / 60) * $hourlyRate;
            $breakdown['work_minutes'] = $workMinutes;
            $breakdown['work_hours'] = round($workMinutes / 60, 2);

            $lateMinutes = $dailyRecords->sum('late_minutes');
            if ($lateMinutes > 0) {
                $deductions += $lateMinutes * $perMinuteRate;
                $breakdown['late_deduction'] = -$lateMinutes * $perMinuteRate;
            }
        } elseif ($employee->salary_type === 'daily') {
            $dailyRate = $baseAmount;
            $presentDays = $dailyRecords->whereIn('status', ['present', 'half_day'])->count();
            $baseAmount = $presentDays * $dailyRate;
            $breakdown['present_days'] = $presentDays;
        }

        $netAmount = $baseAmount + $additions - $deductions;

        return SalarySlip::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'month' => $month,
                'year' => $year,
            ],
            [
                'base_amount' => round($baseAmount, 2),
                'additions' => round($additions, 2),
                'deductions' => round($deductions, 2),
                'net_amount' => round($netAmount, 2),
                'breakdown' => $breakdown,
            ]
        );
    }

    private function overlapDays(string $from, string $to, string $rangeStart, string $rangeEnd): int
    {
        $start = max(strtotime($from), strtotime($rangeStart));
        $end = min(strtotime($to), strtotime($rangeEnd));
        if ($end < $start) {
            return 0;
        }
        return (int) (($end - $start) / 86400) + 1;
    }
}
