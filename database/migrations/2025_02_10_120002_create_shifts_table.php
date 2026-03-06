<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Day", "Night"
            $table->time('start_time'); // e.g. 08:00
            $table->time('end_time');   // e.g. 19:00 or 08:00 for night (next day)
            $table->boolean('is_night_shift')->default(false); // true if end_time is next day (e.g. 7pm-8am)
            $table->unsignedSmallInteger('grace_minutes')->default(0); // late grace
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
