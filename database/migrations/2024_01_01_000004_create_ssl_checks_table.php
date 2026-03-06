<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ssl_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitored_site_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_valid')->default(false);
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->string('issuer', 500)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('checked_at');

            $table->index(['monitored_site_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ssl_checks');
    }
};
