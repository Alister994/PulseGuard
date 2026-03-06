<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('device_user_id', 64)->nullable(); // ID from fingerprint machine (mapped after enrollment)
            $table->string('employee_no', 64)->nullable(); // internal employee number
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->date('join_date')->nullable();
            $table->string('salary_type', 32)->default('monthly'); // monthly, hourly, daily
            $table->decimal('salary_value', 12, 2)->default(0);
            $table->string('currency', 8)->default('INR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['location_id', 'device_user_id'], 'employees_location_device_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
