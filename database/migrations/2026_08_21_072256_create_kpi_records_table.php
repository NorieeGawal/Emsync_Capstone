<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kpi_indicator_id')->constrained('kpi_indicators')->cascadeOnDelete();
            $table->string('period'); // e.g. "2026-08"
            $table->decimal('score', 5, 2);
            $table->foreignId('encoded_by')->constrained('users')->cascadeOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_records');
    }
};