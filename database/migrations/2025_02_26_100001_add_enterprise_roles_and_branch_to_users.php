<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('is_active')->constrained('locations')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->after('location_id')->constrained('employees')->nullOnDelete();
        });

        // Migrate existing 'admin' to 'branch_admin'; create super_admin via seeder
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'admin')
            ->update(['role' => 'branch_admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropForeign(['employee_id']);
        });
    }
};
