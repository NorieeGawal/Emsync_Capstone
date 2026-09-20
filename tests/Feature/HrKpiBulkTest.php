<?php

use App\Models\Department;
use App\Models\KpiIndicator;
use App\Models\KpiRecord;
use App\Models\User;

function createManualKpiIndicator(string $name): KpiIndicator
{
    return KpiIndicator::create(['name' => $name]);
}

test('hr can view KPI management', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $indicator = createManualKpiIndicator('Efficiency');
    KpiRecord::create([
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $indicator->id,
        'period' => '2026-09',
        'score' => 88,
        'encoded_by' => $hr->id,
    ]);

    $this->actingAs($hr)
        ->get(route('hr.kpi.index'))
        ->assertOk()
        ->assertSee('KPI Management')
        ->assertSee('Edit KPI')
        ->assertSee('value="88"', false);
});

    test('hr sees pending KPI notifications in the dashboard layout', function () {
        $hr = User::factory()->create(['role' => 'hr']);
        User::factory()->create(['role' => 'employee']);
        createManualKpiIndicator('Efficiency');

        $this->actingAs($hr)
        ->get(route('hr.kpi.index'))
        ->assertOk()
        ->assertSee('Notifications')
        ->assertSee('KPI scores incomplete')
        ->assertSee('pending item(s)');
    });

test('hr can mark a KPI notification as read', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    User::factory()->create(['role' => 'employee']);
    createManualKpiIndicator('Efficiency');

    $this->actingAs($hr)
        ->get(route('hr.kpi.index'))
        ->assertSee('KPI scores incomplete');

    $this->post(route('notifications.read'), [
        'notification_key' => 'kpi-incomplete-2026-09',
        'redirect_to' => route('hr.kpi.index'),
    ])->assertRedirect(route('hr.kpi.index'));

    $this->flushSession();

    $this->actingAs($hr)
        ->get(route('hr.kpi.index'))
        ->assertOk()
        ->assertDontSee('KPI scores incomplete')
        ->assertDontSee('1 pending item(s)');
});

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

    test('hr can view the unfiltered bulk KPI entry page', function () {
        $hr = User::factory()->create(['role' => 'hr']);
        createManualKpiIndicator('Efficiency');
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($hr)
        ->get(route('hr.kpi.bulk'))
        ->assertOk()
        ->assertSee('Bulk KPI Entry')
        ->assertSee($employee->name);
    });

    test('hr can open the individual KPI encode page', function () {
        $hr = User::factory()->create(['role' => 'hr']);
        $employee = User::factory()->create(['role' => 'employee']);
        createManualKpiIndicator('Efficiency');

        $this->actingAs($hr)
        ->get(route('hr.kpi.create', $employee))
        ->assertOk()
        ->assertSee('Encode KPI Scores')
        ->assertSee($employee->name);
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

test('manager bulk KPI entry is limited to the manager department', function () {
    $department = Department::create(['name' => 'Production']);
    $otherDepartment = Department::create(['name' => 'Marketing']);
    $manager = User::factory()->create(['role' => 'manager', 'department_id' => $department->id]);
    $teamEmployee = User::factory()->create(['role' => 'employee', 'department_id' => $department->id]);
    $otherEmployee = User::factory()->create(['role' => 'employee', 'department_id' => $otherDepartment->id]);
    $indicator = createManualKpiIndicator('Efficiency');

    $this->actingAs($manager)
        ->get(route('manager.kpi.bulk'))
        ->assertOk()
        ->assertSee($teamEmployee->name)
        ->assertDontSee($otherEmployee->name)
        ->assertSee('Production')
        ->assertDontSee('Marketing');

    $this->actingAs($manager)
        ->post(route('manager.kpi.bulk.store'), [
            'period' => '2026-09',
            'employee_ids' => [$otherEmployee->id],
            'kpi_indicator_id' => $indicator->id,
            'score' => 90,
        ])
        ->assertStatus(422);

    $this->assertDatabaseMissing('kpi_records', [
        'employee_id' => $otherEmployee->id,
        'period' => '2026-09',
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

test('hr can open an employee KPI editor with the selected period scores', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $indicator = createManualKpiIndicator('Efficiency');

    KpiRecord::create([
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $indicator->id,
        'period' => '2026-09',
        'score' => 82,
        'encoded_by' => $hr->id,
    ]);

    $this->actingAs($hr)
        ->get(route('hr.kpi.edit', [$employee, 'period' => '2026-09']))
        ->assertOk()
        ->assertSee($employee->name)
        ->assertSee('value="82"', false);
});

test('hr can update one employee KPI scores from the editor', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $indicator = createManualKpiIndicator('Efficiency');

    KpiRecord::create([
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $indicator->id,
        'period' => '2026-09',
        'score' => 82,
        'encoded_by' => $hr->id,
    ]);

    $this->actingAs($hr)->post(route('hr.kpi.store', $employee), [
        'period' => '2026-09',
        'scores' => [$indicator->id => 97],
    ])->assertRedirect(route('hr.kpi.index'));

    expect(KpiRecord::where('employee_id', $employee->id)->where('period', '2026-09')->count())->toBe(1);
    expect((float) KpiRecord::where('employee_id', $employee->id)->where('period', '2026-09')->first()->score)->toBe(97.0);
});
