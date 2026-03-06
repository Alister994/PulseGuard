<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForgetPunchRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'date', 'punch_slot', 'punch_type', 'requested_time', 'reason',
        'status', 'requested_by', 'approved_by', 'approved_at', 'admin_remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'requested_time' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
