<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftBreak extends Model
{
    protected $fillable = [
        'shift_id', 'break_type', 'start_time', 'end_time',
        'duration_minutes', 'sort_order',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
