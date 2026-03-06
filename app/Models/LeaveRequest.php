<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'from_date', 'to_date', 'type', 'leave_type', 'is_half_day', 'reason',
        'status', 'approval_level', 'approved_by', 'approved_at', 'admin_remarks',
        'approved_by_manager', 'approved_at_manager', 'approved_by_hr', 'approved_at_hr',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'is_half_day' => 'boolean',
            'approved_at' => 'datetime',
            'approved_at_manager' => 'datetime',
            'approved_at_hr' => 'datetime',
        ];
    }

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED_PAID = 'approved_paid';
    public const STATUS_APPROVED_UNPAID = 'approved_unpaid';
    public const STATUS_REJECTED = 'rejected';

    public const LEAVE_TYPE_PL = 'PL';
    public const LEAVE_TYPE_CL = 'CL';
    public const LEAVE_TYPE_SL = 'SL';
    public const LEAVE_TYPE_HALF_DAY = 'half_day';

    public const APPROVAL_LEVEL_PENDING_MANAGER = 'pending_manager';
    public const APPROVAL_LEVEL_PENDING_HR = 'pending_hr';
    public const APPROVAL_LEVEL_APPROVED = 'approved';
    public const APPROVAL_LEVEL_REJECTED = 'rejected';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvedByManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_manager');
    }

    public function approvedByHr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_hr');
    }

    public function isPending(): bool
    {
        return in_array($this->approval_level, [
            self::APPROVAL_LEVEL_PENDING_MANAGER,
            self::APPROVAL_LEVEL_PENDING_HR,
        ], true);
    }

    public function isApproved(): bool
    {
        return $this->approval_level === self::APPROVAL_LEVEL_APPROVED
            || in_array($this->status, [self::STATUS_APPROVED_PAID, self::STATUS_APPROVED_UNPAID], true);
    }

    public function isRejected(): bool
    {
        return $this->approval_level === self::APPROVAL_LEVEL_REJECTED
            || $this->status === self::STATUS_REJECTED;
    }
}
