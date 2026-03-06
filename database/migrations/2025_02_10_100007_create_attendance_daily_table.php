<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->dateTime('punch_1_at')->nullable(); // in
            $table->dateTime('punch_2_at')->nullable(); // break start
            $table->dateTime('punch_3_at')->nullable(); // break end
            $table->dateTime('punch_4_at')->nullable(); // out
            $table->unsignedInteger('work_minutes')->default(0);
            $table->unsignedInteger('break_minutes')->default(0);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->string('status', 32)->default('present'); // present, half_day, absent, leave, holiday, weekly_off
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_daily');
    }
};
