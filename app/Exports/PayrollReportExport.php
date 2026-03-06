<?php

namespace App\Exports;

use App\Models\SalarySlip;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayrollReportExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private int $month,
        private int $year,
        private ?int $locationId = null,
        private ?int $departmentId = null
    ) {}

    public function query()
    {
        $q = SalarySlip::with('employee.location', 'employee.department')
            ->where('month', $this->month)->where('year', $this->year);
        if ($this->locationId) {
            $q->whereHas('employee', fn ($q) => $q->where('location_id', $this->locationId));
        }
        if ($this->departmentId) {
            $q->whereHas('employee', fn ($q) => $q->where('department_id', $this->departmentId));
        }
        return $q->orderBy('employee_id');
    }

    public function headings(): array
    {
        return ['Employee', 'Employee No', 'Location', 'Department', 'Base', 'Additions', 'Deductions', 'Net', 'Currency'];
    }

    public function map($row): array
    {
        return [
            $row->employee->name ?? '',
            $row->employee->employee_no ?? '',
            $row->employee->location->name ?? '',
            $row->employee->department->name ?? '',
            $row->base_amount,
            $row->additions,
            $row->deductions,
            $row->net_amount,
            $row->employee->currency ?? 'INR',
        ];
    }
}
