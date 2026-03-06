<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Shift rotation: 15 days day shift / 15 days night shift.
     * rotation_phase: 0 = day period, 1 = night period (within current 30-day cycle).
     * rotation_start_date: start of current 30-day cycle for this employee.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->date('shift_rotation_start_date')->nullable()->after('shift_id');
            $table->unsignedTinyInteger('rotation_phase')->default(0)->after('shift_rotation_start_date'); // 0=day, 1=night
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['shift_rotation_start_date', 'rotation_phase']);
        });
    }
};
