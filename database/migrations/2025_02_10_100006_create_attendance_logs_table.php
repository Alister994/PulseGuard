<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete(); // null until mapped
            $table->string('device_user_id', 64); // raw ID from machine
            $table->dateTime('punch_time');
            $table->unsignedTinyInteger('punch_sequence')->default(1); // 1=in, 2=break_start, 3=break_end, 4=out (set by processor)
            $table->string('punch_type', 32)->nullable(); // in, out, break_in, break_out - if device sends it
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
            $table->index(['device_id', 'punch_time']);
            $table->index(['employee_id', 'punch_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
