<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitored_site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel'); // slack, telegram, mail, webhook
            $table->string('type'); // downtime, ssl_expiring, recovery
            $table->boolean('success')->default(true);
            $table->text('message')->nullable();
            $table->text('response')->nullable();
            $table->timestamp('sent_at');

            $table->index(['monitored_site_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_logs');
    }
};
