<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Setting;
use App\Models\SalarySlip;
use App\Services\SalaryService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SalarySlipController extends Controller
{
    public function __construct(
        private SalaryService $salaryService
    ) {}

    public function generatePdf(int $employee, int $month, int $year)
    {
        $employee = Employee::findOrFail($employee);
        $slip = $this->salaryService->calculateForMonth($employee, $month, $year);
        $watermarkText = Setting::get('watermark_text', 'BIOTIME');

        $pdf = Pdf::loadView('reports.salary_slip', [
            'slip' => $slip,
            'watermarkText' => $watermarkText,
        ]);

        return $pdf->stream('salary-slip-' . $employee->id . '-' . $year . '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '.pdf');
    }
}
