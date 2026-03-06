<?php

namespace App\Exports;

use App\Models\AttendanceDaily;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceReportExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private Carbon $from,
        private Carbon $to,
        private ?int $locationId = null,
        private ?int $departmentId = null
    ) {}

    public function query()
    {
        $q = AttendanceDaily::with('employee.location', 'employee.department')
            ->whereBetween('date', [$this->from, $this->to]);
        if ($this->locationId) {
            $q->whereHas('employee', fn ($q) => $q->where('location_id', $this->locationId));
        }
        if ($this->departmentId) {
            $q->whereHas('employee', fn ($q) => $q->where('department_id', $this->departmentId));
        }
        return $q->orderBy('date')->orderBy('employee_id');
    }

    public function headings(): array
    {
        return ['Date', 'Employee', 'Employee No', 'Location', 'Department', 'Duty In', 'Duty Out', 'Work (hrs)', 'Lunch (min)', 'Tea (min)', 'Late (min)', 'Overtime (min)', 'Status'];
    }

    public function map($row): array
    {
        return [
            $row->date->format('Y-m-d'),
            $row->employee->name ?? '',
            $row->employee->employee_no ?? '',
            $row->employee->location->name ?? '',
            $row->employee->department->name ?? '',
            $row->punch_1_at?->format('H:i') ?? '',
            $row->punch_6_at?->format('H:i') ?: ($row->punch_4_at?->format('H:i') ?? ''),
            round($row->work_minutes / 60, 2),
            $row->lunch_minutes,
            $row->tea_minutes,
            $row->late_minutes,
            $row->overtime_minutes,
            $row->status,
        ];
    }
}
