<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('http_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitored_site_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->string('status', 20); // up, down, timeout
            $table->text('error_message')->nullable();
            $table->timestamp('checked_at');

            $table->index(['monitored_site_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('http_checks');
    }
};
