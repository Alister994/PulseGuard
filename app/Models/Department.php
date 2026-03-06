<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['location_id', 'name', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'department_id');
    }

    public function weeklyOffs(): HasMany
    {
        return $this->hasMany(DepartmentWeeklyOff::class, 'department_id');
    }

    /** Check if given day of week (0-6) is weekly off for this department */
    public function isWeeklyOff(int $dayOfWeek): bool
    {
        return $this->weeklyOffs()->where('day_of_week', $dayOfWeek)->exists();
    }
}
