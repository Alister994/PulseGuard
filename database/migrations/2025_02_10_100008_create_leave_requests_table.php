<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->string('type', 32)->default('leave'); // leave, absent
            $table->text('reason')->nullable();
            $table->string('status', 32)->default('pending'); // pending, approved_paid, approved_unpaid, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_remarks')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'from_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
