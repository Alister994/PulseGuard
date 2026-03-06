<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitored_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('url', 2048);
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('check_interval_minutes')->default(1);
            $table->boolean('ssl_check_enabled')->default(true);
            $table->json('alert_channels')->nullable(); // ['slack','telegram','mail','webhook']
            $table->string('timezone', 50)->default('UTC');
            $table->timestamps();

            $table->index(['is_active', 'check_interval_minutes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitored_sites');
    }
};
