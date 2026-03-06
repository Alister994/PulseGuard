<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id', 'name', 'start_time', 'end_time',
        'is_night_shift', 'grace_minutes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_night_shift' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function shiftBreaks(): HasMany
    {
        return $this->hasMany(ShiftBreak::class, 'shift_id')->orderBy('sort_order');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'shift_id');
    }
}
