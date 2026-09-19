<?php

use App\Models\Department;
use App\Models\KpiIndicator;
use App\Models\KpiRecord;
use App\Models\User;

function createManualKpiIndicator(string $name): KpiIndicator
{
    return KpiIndicator::create(['name' => $name]);
}

test('hr can view filtered bulk KPI entry', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $department = Department::create(['name' => 'Production']);
    $employee = User::factory()->create(['role' => 'employee', 'department_id' => $department->id]);
    createManualKpiIndicator('Efficiency');

    $this->actingAs($hr)
        ->get(route('hr.kpi.bulk', ['department' => $department->id]))
        ->assertOk()
        ->assertSee($employee->name)
        ->assertSee('Production');
});

test('hr can apply one KPI score to multiple employees', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $indicator = createManualKpiIndicator('Efficiency');
    $employeeOne = User::factory()->create(['role' => 'employee']);
    $employeeTwo = User::factory()->create(['role' => 'manager']);

    $response = $this->actingAs($hr)->post(route('hr.kpi.bulk.store'), [
        'period' => '2026-09',
        'employee_ids' => [$employeeOne->id, $employeeTwo->id],
        'kpi_indicator_id' => $indicator->id,
        'score' => 90,
        'remarks' => 'Monthly bulk entry',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $employeeOne->id,
        'kpi_indicator_id' => $indicator->id,
        'period' => '2026-09',
        'score' => 90,
        'encoded_by' => $hr->id,
    ]);
    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $employeeTwo->id,
        'kpi_indicator_id' => $indicator->id,
        'period' => '2026-09',
        'score' => 90,
    ]);
});

test('bulk KPI entry updates an existing record instead of duplicating it', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $indicator = createManualKpiIndicator('Efficiency');
    $employee = User::factory()->create(['role' => 'employee']);

    KpiRecord::create([
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $indicator->id,
        'period' => '2026-09',
        'score' => 70,
        'encoded_by' => $hr->id,
    ]);

    $this->actingAs($hr)->post(route('hr.kpi.bulk.store'), [
        'period' => '2026-09',
        'employee_ids' => [$employee->id],
        'kpi_indicator_id' => $indicator->id,
        'score' => 95,
    ]);

    expect(KpiRecord::where('employee_id', $employee->id)->where('period', '2026-09')->count())->toBe(1);
    expect((float) KpiRecord::where('employee_id', $employee->id)->where('period', '2026-09')->first()->score)->toBe(95.0);
});
