<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Machine A", "Reception Terminal"
            $table->string('device_serial')->nullable(); // from Mantra/PayTime
            $table->string('api_key', 64)->unique()->nullable(); // for sync agent to authenticate
            $table->timestamp('last_sync_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
