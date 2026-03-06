<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
<<<<<<< HEAD
            $table->integer('expiration');
=======
            $table->integer('expiration')->index();
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
<<<<<<< HEAD
            $table->integer('expiration');
=======
            $table->integer('expiration')->index();
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
