<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id', 'address', 'department_id', 'shift_id',
        'shift_rotation_start_date', 'rotation_phase',
        'device_user_id', 'employee_no', 'name', 'email', 'phone',
        'join_date', 'salary_type', 'salary_value', 'currency', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'shift_rotation_start_date' => 'date',
            'salary_value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function attendanceDaily(): HasMany
    {
        return $this->hasMany(AttendanceDaily::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function salarySlips(): HasMany
    {
        return $this->hasMany(SalarySlip::class);
    }

    public function forgetPunchRequests(): HasMany
    {
        return $this->hasMany(ForgetPunchRequest::class);
    }

    /** Employee code for device push: employee_no or device_user_id */
    public function getEmployeeCodeAttribute(): string
    {
        return $this->employee_no ?: (string) $this->device_user_id;
    }

    /**
     * Find employee by device user id (or employee_no) for a location.
     * Matches exact string; if value is numeric, also matches zero-padded to 8 digits (e.g. 1 and 00000001).
     */
    public static function findByDeviceUserIdForLocation(int $locationId, string $deviceUserId): ?self
    {
        $deviceUserId = trim($deviceUserId);
        if ($deviceUserId === '') {
            return null;
        }
        $ids = [$deviceUserId];
        if (preg_match('/^\d+$/', $deviceUserId)) {
            $ids[] = str_pad($deviceUserId, 8, '0', STR_PAD_LEFT);
        }
        return self::where('location_id', $locationId)
            ->where(function ($q) use ($ids) {
                $q->whereIn('device_user_id', $ids)->orWhereIn('employee_no', $ids);
            })
            ->first();
    }
}
