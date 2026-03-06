<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Location;
use App\Models\SalarySlip;
use App\Services\SalaryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        $query = SalarySlip::with('employee.location', 'employee.department');
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('location_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('location_id', $request->location_id);
            });
        }
        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        $slips = $query->orderBy('year', 'desc')->orderBy('month', 'desc')->orderBy('employee_id')->paginate(20)->withQueryString();
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('payroll.index', compact('slips', 'locations', 'departments'));
    }

    public function generate(Request $request)
    {
        $request->validate(['month' => 'required|integer|min:1|max:12', 'year' => 'required|integer|min:2020|max:2100']);
        $salaryService = app(SalaryService::class);
        $employees = Employee::where('is_active', true)->get();
        $count = 0;
        foreach ($employees as $emp) {
            $salaryService->calculateForMonth($emp, (int) $request->month, (int) $request->year);
            $count++;
        }
        return redirect()->route('payroll.index', ['month' => $request->month, 'year' => $request->year])->with('success', "Payroll generated for {$count} employees.");
    }
}
