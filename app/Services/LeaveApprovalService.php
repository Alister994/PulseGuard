<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Leave approval flow (Option B): Employee → Manager OR HR → Final.
 * Admin (Branch Admin / Super Admin) override allowed at any time.
 */
class LeaveApprovalService
{
    /**
     * Manager or HR approves (first level). Moves to pending_hr if manager approved, or to approved if HR approves.
     */
    public function approveByManagerOrHr(LeaveRequest $leave, User $approver): void
    {
        if (! $approver->isDepartmentManager() && ! $approver->isHr() && ! $approver->canOverrideApproval()) {
            throw new \InvalidArgumentException('Only Manager, HR or Admin can approve.');
        }
        if (! $leave->isPending()) {
            throw new \InvalidArgumentException('Leave is not pending.');
        }

        DB::transaction(function () use ($leave, $approver) {
            if ($approver->isHr() || $approver->canOverrideApproval()) {
                $leave->update([
                    'approved_by_manager' => $approver->id,
                    'approved_at_manager' => now(),
                ]);
                $this->approveFinal($leave, $approver, true);
                return;
            }
            if ($approver->isDepartmentManager()) {
                $leave->update([
                    'approval_level' => LeaveRequest::APPROVAL_LEVEL_PENDING_HR,
                    'approved_by_manager' => $approver->id,
                    'approved_at_manager' => now(),
                ]);
            }
        });
    }

    /**
     * HR or Admin gives final approval (approved_paid / approved_unpaid) or reject.
     */
    public function approveFinal(LeaveRequest $leave, User $approver, bool $paid = true): void
    {
        if (! $approver->isHr() && ! $approver->canOverrideApproval()) {
            throw new \InvalidArgumentException('Only HR or Admin can give final approval.');
        }
        if ($leave->approval_level === LeaveRequest::APPROVAL_LEVEL_REJECTED) {
            throw new \InvalidArgumentException('Leave is already rejected.');
        }

        $status = $paid ? LeaveRequest::STATUS_APPROVED_PAID : LeaveRequest::STATUS_APPROVED_UNPAID;
        $leave->update([
            'approval_level' => LeaveRequest::APPROVAL_LEVEL_APPROVED,
            'status' => $status,
            'approved_by_hr' => $approver->id,
            'approved_at_hr' => now(),
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject leave (by Manager, HR or Admin).
     */
    public function reject(LeaveRequest $leave, User $rejector, ?string $remarks = null): void
    {
        if (! $rejector->isDepartmentManager() && ! $rejector->isHr() && ! $rejector->canOverrideApproval()) {
            throw new \InvalidArgumentException('Only Manager, HR or Admin can reject.');
        }
        if (! $leave->isPending()) {
            throw new \InvalidArgumentException('Leave is not pending.');
        }

        $leave->update([
            'approval_level' => LeaveRequest::APPROVAL_LEVEL_REJECTED,
            'status' => LeaveRequest::STATUS_REJECTED,
            'approved_by' => $rejector->id,
            'approved_at' => now(),
            'admin_remarks' => $remarks ?? $leave->admin_remarks,
        ]);
    }

    /**
     * Admin override: set final status directly (approved_paid, approved_unpaid, rejected).
     */
    public function adminOverride(LeaveRequest $leave, User $admin, string $status, ?string $remarks = null): void
    {
        if (! $admin->canOverrideApproval()) {
            throw new \InvalidArgumentException('Only Branch Admin or Super Admin can override.');
        }
        $valid = [LeaveRequest::STATUS_APPROVED_PAID, LeaveRequest::STATUS_APPROVED_UNPAID, LeaveRequest::STATUS_REJECTED];
        if (! in_array($status, $valid, true)) {
            throw new \InvalidArgumentException('Invalid status.');
        }

        $leave->update([
            'approval_level' => $status === LeaveRequest::STATUS_REJECTED
                ? LeaveRequest::APPROVAL_LEVEL_REJECTED
                : LeaveRequest::APPROVAL_LEVEL_APPROVED,
            'status' => $status,
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'admin_remarks' => $remarks ?? $leave->admin_remarks,
        ]);
    }
}
