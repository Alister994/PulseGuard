<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalarySlip extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'month', 'year', 'base_amount', 'additions',
        'deductions', 'net_amount', 'breakdown', 'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'additions' => 'decimal:2',
            'deductions' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'breakdown' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
