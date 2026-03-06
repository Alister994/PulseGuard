<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Services\LeaveApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveApprovalController extends Controller
{
    public function __construct(
        private LeaveApprovalService $leaveApprovalService
    ) {}

    /**
     * Manager or HR first-level approval (moves to pending_hr or directly to final if HR).
     */
    public function approveByManagerOrHr(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $request->validate(['remarks' => 'nullable|string|max:500']);
        $this->leaveApprovalService->approveByManagerOrHr($leave, $request->user());
        return back()->with('message', 'Leave approved (first level).');
    }

    /**
     * HR or Admin final approval (paid/unpaid).
     */
    public function approveFinal(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $request->validate(['paid' => 'boolean']);
        $this->leaveApprovalService->approveFinal($leave, $request->user(), $request->boolean('paid', true));
        return back()->with('message', 'Leave finally approved.');
    }

    /**
     * Reject leave.
     */
    public function reject(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $request->validate(['remarks' => 'nullable|string|max:500']);
        $this->leaveApprovalService->reject($leave, $request->user(), $request->input('remarks'));
        return back()->with('message', 'Leave rejected.');
    }

    /**
     * Admin override: set status directly (approved_paid, approved_unpaid, rejected).
     */
    public function adminOverride(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:approved_paid,approved_unpaid,rejected',
            'remarks' => 'nullable|string|max:500',
        ]);
        $this->leaveApprovalService->adminOverride($leave, $request->user(), $request->input('status'), $request->input('remarks'));
        return back()->with('message', 'Leave overridden.');
    }
}
