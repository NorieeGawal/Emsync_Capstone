<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_records', function (Blueprint $table) {
            $table->unsignedInteger('workload_target')->nullable()->after('score');
            $table->unsignedInteger('workload_completed')->nullable()->after('workload_target');
        });
    }

    public function down(): void
    {
        Schema::table('kpi_records', function (Blueprint $table) {
            $table->dropColumn(['workload_target', 'workload_completed']);
        });
    }
};
