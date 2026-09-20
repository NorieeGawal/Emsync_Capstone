<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table): void {
            $table->index(['employee_id', 'date']);
        });

        Schema::table('kpi_records', function (Blueprint $table): void {
            $table->index(['employee_id', 'period', 'kpi_indicator_id']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index(['role', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table): void {
            $table->dropIndex(['attendance_logs_employee_id_date_index']);
        });

        Schema::table('kpi_records', function (Blueprint $table): void {
            $table->dropIndex(['kpi_records_employee_id_period_kpi_indicator_id_index']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['users_role_department_id_index']);
        });
    }
};