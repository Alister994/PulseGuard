<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Employee::with(['location', 'department', 'shift']);
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('employee_no', 'like', "%{$s}%")->orWhere('device_user_id', 'like', "%{$s}%");
            });
        }
        $employees = $query->orderBy('name')->paginate(15)->withQueryString();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('employees.index', compact('employees', 'departments'));
    }

    public function create(): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $shifts = Shift::where('is_active', true)->with('department')->orderBy('department_id')->get();
        return view('employees.create', compact('departments', 'shifts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address' => ['nullable', 'string', 'max:2000'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'device_user_id' => ['nullable', 'string', 'max:64'],
            'employee_no' => ['nullable', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:128'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'join_date' => ['nullable', 'date'],
            'salary_type' => ['required', 'in:monthly,hourly,daily'],
            'salary_value' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
        ]);
        $validated['currency'] = $validated['currency'] ?? 'INR';
        Employee::create($validated);
        return redirect()->route('employees.index')->with('success', 'Employee created.');
    }

    public function edit(Employee $employee): View
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $shifts = Shift::where('is_active', true)->with('department')->orderBy('department_id')->get();
        return view('employees.edit', compact('employee', 'departments', 'shifts'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'address' => ['nullable', 'string', 'max:2000'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'device_user_id' => ['nullable', 'string', 'max:64'],
            'employee_no' => ['nullable', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:128'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'join_date' => ['nullable', 'date'],
            'salary_type' => ['required', 'in:monthly,hourly,daily'],
            'salary_value' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'is_active' => ['boolean'],
        ]);
        $validated['currency'] = $validated['currency'] ?? 'INR';
        $validated['is_active'] = $request->boolean('is_active');
        $employee->update($validated);
        return redirect()->route('employees.index')->with('success', 'Employee updated.');
    }
}
