<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downtime_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitored_site_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('status', 20)->default('down');
            $table->text('summary')->nullable();
            $table->boolean('alert_sent')->default(false);

            $table->index(['monitored_site_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('downtime_incidents');
    }
};
