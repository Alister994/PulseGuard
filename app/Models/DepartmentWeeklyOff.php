<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentWeeklyOff extends Model
{
    protected $fillable = ['department_id', 'day_of_week'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public static function dayName(int $dayOfWeek): string
    {
        return match ($dayOfWeek) {
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            default => 'Unknown',
        };
    }
}
