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
        ->get(route('hr.kpi.index', ['period' => '2026-09']))
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
        'notification_key' => 'kpi-incomplete-'.now()->format('Y-m'),
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

test('productivity score is calculated from completed workload and target', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $productivity = createManualKpiIndicator('Productivity');
    $efficiency = createManualKpiIndicator('Efficiency');

    $this->actingAs($hr)->post(route('hr.kpi.store', $employee), [
        'period' => '2026-09',
        'scores' => [$efficiency->id => 84],
        'workload_target' => 20,
        'workload_completed' => 18,
    ])->assertRedirect(route('hr.kpi.index'));

    $record = KpiRecord::where('employee_id', $employee->id)
        ->where('kpi_indicator_id', $productivity->id)
        ->where('period', '2026-09')
        ->firstOrFail();

    expect((float) $record->score)->toBe(90.0)
        ->and($record->workload_target)->toBe(20)
        ->and($record->workload_completed)->toBe(18)
        ->and(KpiRecord::productivityScore(10, 12))->toBe(100.0);

    $this->actingAs($hr)
        ->get(route('hr.employees.show', [$employee, 'period' => '2026-09']))
        ->assertOk()
        ->assertSee('18 completed of 20 target');
});

test('individual and bulk KPI forms request workload counts instead of a productivity score', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $productivity = createManualKpiIndicator('Productivity');
    createManualKpiIndicator('Efficiency');

    $this->actingAs($hr)
        ->get(route('hr.kpi.create', $employee))
        ->assertOk()
        ->assertSee('name="workload_target"', false)
        ->assertSee('name="workload_completed"', false)
        ->assertDontSee('name="scores['.$productivity->id.']"', false);

    $this->get(route('hr.kpi.bulk'))
        ->assertOk()
        ->assertSee('data-indicator-name="Productivity"', false)
        ->assertSee('name="workload_target['.$employee->id.']"', false)
        ->assertSee('name="workload_completed['.$employee->id.']"', false);
});

test('productivity cannot be assigned a bulk score without workload details', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $productivity = createManualKpiIndicator('Productivity');

    $this->actingAs($hr)->from(route('hr.kpi.bulk'))->post(route('hr.kpi.bulk.store'), [
        'period' => '2026-09',
        'employee_ids' => [$employee->id],
        'kpi_indicator_id' => $productivity->id,
        'score' => 90,
    ])->assertRedirect(route('hr.kpi.bulk'))
        ->assertSessionHasErrors(['workload_target', 'workload_completed']);

    $this->assertDatabaseMissing('kpi_records', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $productivity->id,
        'period' => '2026-09',
    ]);
});

test('bulk productivity calculates a separate score for each employee workload', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employeeOne = User::factory()->create(['role' => 'employee']);
    $employeeTwo = User::factory()->create(['role' => 'employee']);
    $productivity = createManualKpiIndicator('Productivity');

    $this->actingAs($hr)->post(route('hr.kpi.bulk.store'), [
        'period' => '2026-09',
        'employee_ids' => [$employeeOne->id, $employeeTwo->id],
        'kpi_indicator_id' => $productivity->id,
        'workload_target' => [
            $employeeOne->id => 20,
            $employeeTwo->id => 10,
        ],
        'workload_completed' => [
            $employeeOne->id => 18,
            $employeeTwo->id => 5,
        ],
    ])->assertRedirect();

    expect((float) KpiRecord::where('employee_id', $employeeOne->id)->where('period', '2026-09')->firstOrFail()->score)->toBe(90.0)
        ->and((float) KpiRecord::where('employee_id', $employeeTwo->id)->where('period', '2026-09')->firstOrFail()->score)->toBe(50.0);
});

test('productivity workload requires a positive target', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $productivity = createManualKpiIndicator('Productivity');
    $efficiency = createManualKpiIndicator('Efficiency');

    $this->actingAs($hr)->from(route('hr.kpi.create', $employee))
        ->post(route('hr.kpi.store', $employee), [
            'period' => '2026-09',
            'scores' => [$efficiency->id => 84],
            'workload_target' => 0,
            'workload_completed' => 18,
        ])
        ->assertRedirect(route('hr.kpi.create', $employee))
        ->assertSessionHasErrors('workload_target');

    $this->assertDatabaseMissing('kpi_records', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $productivity->id,
        'period' => '2026-09',
    ]);
});
