<?php

<<<<<<< HEAD
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/sites/{site}', [DashboardController::class, 'site'])->name('dashboard.site');
=======
use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveApprovalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalarySlipController;
use App\Http\Controllers\ShiftController;
use App\Models\AttendanceDaily;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/favicon.ico', function () {
    $path = public_path('images/logo.png');
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path, ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=86400']);
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        $stats = [
            'employees' => Employee::where('is_active', true)->count(),
            'locations' => Location::where('is_active', true)->count(),
            'today_present' => AttendanceDaily::where('date', today())->count(),
            'unread_notifications' => auth()->user()->unreadNotifications()->count(),
        ];
        return view('dashboard', compact('stats'));
    })->name('dashboard');
    Route::get('/salary-slip/{employee}/{month}/{year}/pdf', [SalarySlipController::class, 'generatePdf'])
        ->name('salary-slip.pdf');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read')->where('id', '[\w-]+');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    Route::resource('employees', EmployeeController::class)->except(['show', 'destroy']);
    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/attendance-export', [ReportController::class, 'attendanceExport'])->name('reports.attendance');
    Route::get('reports/payroll-export', [ReportController::class, 'payrollExport'])->name('reports.payroll');

    Route::resource('locations', LocationController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('shifts', ShiftController::class)->except(['show']);
    Route::resource('devices', DeviceController::class)->except(['show']);
    Route::get('attendance-logs', [AttendanceLogController::class, 'index'])->name('attendance-logs.index')->middleware('super_admin');
    Route::post('leave-requests/{leave}/approve-manager-hr', [LeaveApprovalController::class, 'approveByManagerOrHr'])->name('leave.approve-manager-hr');
    Route::post('leave-requests/{leave}/approve-final', [LeaveApprovalController::class, 'approveFinal'])->name('leave.approve-final');
    Route::post('leave-requests/{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('leave.reject');
    Route::post('leave-requests/{leave}/admin-override', [LeaveApprovalController::class, 'adminOverride'])->name('leave.admin-override');
});
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
