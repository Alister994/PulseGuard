<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_weekly_offs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sunday, 1=Monday, ... 6=Saturday
            $table->timestamps();
            $table->unique(['department_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_weekly_offs');
    }
};
