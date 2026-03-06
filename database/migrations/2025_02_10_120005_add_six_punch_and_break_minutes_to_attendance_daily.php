<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_daily', function (Blueprint $table) {
            $table->dateTime('punch_5_at')->nullable()->after('punch_4_at');
            $table->dateTime('punch_6_at')->nullable()->after('punch_5_at');
            $table->unsignedInteger('lunch_minutes')->default(0)->after('break_minutes');
            $table->unsignedInteger('tea_minutes')->default(0)->after('lunch_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_daily', function (Blueprint $table) {
            $table->dropColumn(['punch_5_at', 'punch_6_at', 'lunch_minutes', 'tea_minutes']);
        });
    }
};
