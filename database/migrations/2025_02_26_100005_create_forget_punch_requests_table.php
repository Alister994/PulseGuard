<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forget_punch_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('punch_slot'); // 1-6
            $table->string('punch_type', 32)->default('in'); // in, out, break_in, break_out
            $table->dateTime('requested_time'); // time employee claims they punched
            $table->text('reason')->nullable();
            $table->string('status', 32)->default('pending'); // pending, approved, rejected
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_remarks')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forget_punch_requests');
    }
};
