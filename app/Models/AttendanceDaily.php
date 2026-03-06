<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceDaily extends Model
{
    use HasFactory;

    protected $table = 'attendance_daily';

    /** Punch order: 1=duty_in, 2=lunch_start, 3=lunch_end, 4=tea_start, 5=tea_end, 6=duty_out */
    protected $fillable = [
        'employee_id', 'date',
        'punch_1_at', 'punch_2_at', 'punch_3_at', 'punch_4_at', 'punch_5_at', 'punch_6_at',
        'work_minutes', 'break_minutes', 'lunch_minutes', 'tea_minutes', 'late_minutes', 'overtime_minutes',
        'status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'punch_1_at' => 'datetime',
            'punch_2_at' => 'datetime',
            'punch_3_at' => 'datetime',
            'punch_4_at' => 'datetime',
            'punch_5_at' => 'datetime',
            'punch_6_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
