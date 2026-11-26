<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_score_audits', function (Blueprint $table): void {
            $table->id();
            $table->uuid('transaction_id')->index();
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_name');
            $table->string('period', 7);
            $table->foreignId('kpi_indicator_id')->nullable()->constrained('kpi_indicators')->nullOnDelete();
            $table->string('kpi_indicator_name');
            $table->string('action', 20);
            $table->json('original_value')->nullable();
            $table->json('new_value')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name');
            $table->text('reason');
            $table->foreignId('reverses_audit_id')->nullable()->constrained('kpi_score_audits')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'period', 'kpi_indicator_id', 'reversed_at'], 'kpi_score_audits_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_score_audits');
    }
};
