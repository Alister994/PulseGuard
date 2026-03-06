<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceReportExport;
use App\Exports\PayrollReportExport;
use App\Models\AttendanceDaily;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Location;
use App\Models\SalarySlip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(): View
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('reports.index', compact('locations', 'departments'));
    }

    public function attendanceExport(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'format' => 'required|in:excel,pdf',
        ]);
        $from = $request->date('from');
        $to = $request->date('to');
        $locationId = $request->location_id;
        $departmentId = $request->department_id;

        if ($request->format === 'excel') {
            return Excel::download(
                new AttendanceReportExport($from, $to, $locationId, $departmentId),
                'attendance-report-' . $from->format('Y-m-d') . '-to-' . $to->format('Y-m-d') . '.xlsx'
            );
        }

        $query = AttendanceDaily::with('employee.location', 'employee.department')
            ->whereBetween('date', [$from, $to]);
        if ($locationId) {
            $query->whereHas('employee', fn ($q) => $q->where('location_id', $locationId));
        }
        if ($departmentId) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId));
        }
        $rows = $query->orderBy('date')->orderBy('employee_id')->get();
        $watermarkText = \App\Models\Setting::get('watermark_text', 'BIOTIME');
        $pdf = Pdf::loadView('reports.attendance-pdf', compact('rows', 'from', 'to', 'watermarkText'));
        return $pdf->stream('attendance-report-' . $from->format('Y-m-d') . '.pdf');
    }

    public function payrollExport(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'format' => 'required|in:excel,pdf',
        ]);
        $month = (int) $request->month;
        $year = (int) $request->year;
        $locationId = $request->location_id;
        $departmentId = $request->department_id;

        if ($request->format === 'excel') {
            return Excel::download(
                new PayrollReportExport($month, $year, $locationId, $departmentId),
                "payroll-{$year}-" . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '.xlsx'
            );
        }

        $query = SalarySlip::with('employee.location', 'employee.department')
            ->where('month', $month)->where('year', $year);
        if ($locationId) {
            $query->whereHas('employee', fn ($q) => $q->where('location_id', $locationId));
        }
        if ($departmentId) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $departmentId));
        }
        $slips = $query->orderBy('employee_id')->get();
        $watermarkText = \App\Models\Setting::get('watermark_text', 'BIOTIME');
        $pdf = Pdf::loadView('reports.payroll-pdf', compact('slips', 'month', 'year', 'watermarkText'));
        return $pdf->stream("payroll-{$year}-" . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '.pdf');
    }
}
