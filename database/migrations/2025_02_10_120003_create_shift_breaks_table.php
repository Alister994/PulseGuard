<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->string('break_type', 32); // lunch, dinner, tea
            $table->time('start_time')->nullable(); // fixed window start (e.g. 12:00)
            $table->time('end_time')->nullable();   // fixed window end (e.g. 13:30)
            $table->unsignedSmallInteger('duration_minutes')->nullable(); // expected duration if no fixed window (e.g. 60)
            $table->unsignedTinyInteger('sort_order')->default(0); // 1=lunch, 2=tea, 3=dinner etc for punch order
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_breaks');
    }
};
