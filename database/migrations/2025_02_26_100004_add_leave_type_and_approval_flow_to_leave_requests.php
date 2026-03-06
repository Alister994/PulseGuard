<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('leave_type', 32)->default('CL')->after('type'); // PL, CL, SL, half_day
            $table->string('approval_level', 32)->default('pending_manager')->after('status'); // pending_manager, pending_hr, approved, rejected
            $table->foreignId('approved_by_manager')->nullable()->after('admin_remarks')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at_manager')->nullable();
            $table->foreignId('approved_by_hr')->nullable()->after('approved_by_manager')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at_hr')->nullable();
            $table->boolean('is_half_day')->default(false)->after('leave_type');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by_manager']);
            $table->dropForeign(['approved_by_hr']);
            $table->dropColumn([
                'leave_type', 'approval_level', 'approved_by_manager', 'approved_at_manager',
                'approved_by_hr', 'approved_at_hr', 'is_half_day',
            ]);
        });
    }
};
