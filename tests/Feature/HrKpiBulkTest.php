<?php

use App\Models\Department;
use App\Models\KpiIndicator;
use App\Models\KpiRecord;
use App\Models\KpiScoreAudit;
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
        ->assertSee('Delete KPI Scores')
        ->assertDontSee('Undo KPI')
        ->assertDontSee('Delete Saved KPI Entry')
        ->assertSee('value="88"', false);
});

test('hr can delete selected users KPI scores for one period while preserving other periods', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $manager = User::factory()->create(['role' => 'manager']);
    $unselectedEmployee = User::factory()->create(['role' => 'employee']);
    $indicator = createManualKpiIndicator('Efficiency');
    $secondIndicator = createManualKpiIndicator('Work Quality');

    foreach ([$employee, $manager, $unselectedEmployee] as $user) {
        KpiRecord::create([
            'employee_id' => $user->id,
            'kpi_indicator_id' => $indicator->id,
            'period' => '2026-09',
            'score' => 80,
            'encoded_by' => $hr->id,
        ]);
    }

    KpiRecord::create([
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $secondIndicator->id,
        'period' => '2026-09',
        'score' => 90,
        'encoded_by' => $hr->id,
    ]);

    KpiRecord::create([
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $indicator->id,
        'period' => '2026-08',
        'score' => 70,
        'encoded_by' => $hr->id,
    ]);

    $this->actingAs($hr)
        ->get(route('hr.kpi.scores.delete', ['period' => '2026-09']))
        ->assertOk()
        ->assertSee($employee->name)
        ->assertSee($manager->name)
        ->assertSee('Select all')
        ->assertDontSee('Undo KPI')
        ->assertSee('Delete KPI')
        ->assertSee(route('hr.kpi.saved-entry.delete', [$employee, 'period' => '2026-09']), false);

    $this->post(route('hr.kpi.scores.destroy'), [
        'period' => '2026-09',
        'employee_ids' => [$employee->id, $manager->id],
        'reason' => 'Correcting the monthly KPI submission',
    ])->assertRedirect(route('hr.kpi.index', ['period' => '2026-09']));

    $this->assertDatabaseMissing('kpi_records', [
        'employee_id' => $employee->id,
        'period' => '2026-09',
    ]);
    $this->assertDatabaseMissing('kpi_records', [
        'employee_id' => $manager->id,
        'period' => '2026-09',
    ]);
    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $employee->id,
        'period' => '2026-08',
    ]);
    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $unselectedEmployee->id,
        'period' => '2026-09',
    ]);
});

test('hr can restore a period-scoped deletion for one employee from its submission history', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $manager = User::factory()->create(['role' => 'manager']);
    $indicator = createManualKpiIndicator('Efficiency');

    foreach ([$employee, $manager] as $user) {
        KpiRecord::create([
            'employee_id' => $user->id,
            'kpi_indicator_id' => $indicator->id,
            'period' => '2026-09',
            'score' => 80,
            'encoded_by' => $hr->id,
        ]);
    }

    $this->actingAs($hr)->post(route('hr.kpi.scores.destroy'), [
        'period' => '2026-09',
        'employee_ids' => [$employee->id, $manager->id],
        'reason' => 'Remove incorrect period entries',
    ])->assertRedirect();

    $transactionId = KpiScoreAudit::where('employee_id', $employee->id)
        ->where('period', '2026-09')
        ->where('action', 'deleted')
        ->value('transaction_id');

    $this->get(route('hr.kpi.undo', [$employee, 'period' => '2026-09']))
        ->assertOk()
        ->assertSee('Undo a full submission')
        ->assertSee('Efficiency');

    $this->post(route('hr.kpi.undo-submission', $employee), [
        'period' => '2026-09',
        'transaction_id' => $transactionId,
        'reason' => 'Restore the employee record only',
    ])->assertRedirect();

    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $indicator->id,
        'period' => '2026-09',
        'score' => 80,
    ]);
    $this->assertDatabaseMissing('kpi_records', [
        'employee_id' => $manager->id,
        'period' => '2026-09',
    ]);
});

test('hr can individually restore one deleted KPI criterion', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $efficiency = createManualKpiIndicator('Efficiency');
    $quality = createManualKpiIndicator('Work Quality');

    foreach ([$efficiency, $quality] as $indicator) {
        KpiRecord::create([
            'employee_id' => $employee->id,
            'kpi_indicator_id' => $indicator->id,
            'period' => '2026-09',
            'score' => 80,
            'encoded_by' => $hr->id,
        ]);
    }

    $this->actingAs($hr)->post(route('hr.kpi.scores.destroy'), [
        'period' => '2026-09',
        'employee_ids' => [$employee->id],
        'reason' => 'Remove incorrect period entries',
    ])->assertRedirect();

    $this->get(route('hr.kpi.undo', [$employee, 'period' => '2026-09']))
        ->assertOk()
        ->assertSee('Deleted scores with saved history')
        ->assertSee('Restore this score');

    $this->post(route('hr.kpi.undo-score', $employee), [
        'period' => '2026-09',
        'kpi_indicator_id' => $efficiency->id,
        'reason' => 'Restore efficiency only',
    ])->assertRedirect();

    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $efficiency->id,
        'period' => '2026-09',
        'score' => 80,
    ]);
    $this->assertDatabaseMissing('kpi_records', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $quality->id,
        'period' => '2026-09',
    ]);
});

test('manager cannot access KPI score deletion', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($manager)
        ->get(route('hr.kpi.scores.delete'))
        ->assertForbidden();
});

test('hr edits are audited and an individual undo restores only that KPI record', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $efficiency = createManualKpiIndicator('Efficiency');
    $quality = createManualKpiIndicator('Work Quality');

    $this->actingAs($hr)->post(route('hr.kpi.store', $employee), [
        'period' => '2026-09',
        'scores' => [$efficiency->id => 80, $quality->id => 70],
    ])->assertRedirect();

    $this->post(route('hr.kpi.store', $employee), [
        'period' => '2026-09',
        'scores' => [$efficiency->id => 95, $quality->id => 70],
        'reason' => 'Corrected efficiency score',
    ])->assertRedirect();

    $this->assertDatabaseHas('kpi_score_audits', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $efficiency->id,
        'action' => 'updated',
        'reason' => 'Corrected efficiency score',
        'actor_id' => $hr->id,
    ]);

    $this->get(route('hr.kpi.undo', [$employee, 'period' => '2026-09']))
        ->assertOk()
        ->assertSee('Undo KPI Scores')
        ->assertSee('95.00')
        ->assertSee('Undo submission');

    $this->post(route('hr.kpi.undo-score', $employee), [
        'period' => '2026-09',
        'kpi_indicator_id' => $efficiency->id,
        'reason' => 'Restore the previous score',
    ])->assertRedirect(route('hr.kpi.undo', [$employee, 'period' => '2026-09']));

    expect((float) KpiRecord::where('employee_id', $employee->id)->where('kpi_indicator_id', $efficiency->id)->value('score'))->toBe(80.0)
        ->and((float) KpiRecord::where('employee_id', $employee->id)->where('kpi_indicator_id', $quality->id)->value('score'))->toBe(70.0);
});

test('hr can undo a complete KPI submission without affecting another period', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $efficiency = createManualKpiIndicator('Efficiency');
    $quality = createManualKpiIndicator('Work Quality');

    $this->actingAs($hr)->post(route('hr.kpi.store', $employee), [
        'period' => '2026-09',
        'scores' => [$efficiency->id => 80, $quality->id => 70],
    ])->assertRedirect();

    KpiRecord::create([
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $efficiency->id,
        'period' => '2026-08',
        'score' => 60,
        'encoded_by' => $hr->id,
    ]);
    $transactionId = KpiScoreAudit::where('employee_id', $employee->id)
        ->where('period', '2026-09')
        ->where('action', 'created')
        ->value('transaction_id');

    $this->post(route('hr.kpi.undo-submission', $employee), [
        'period' => '2026-09',
        'transaction_id' => $transactionId,
        'reason' => 'The submission was entered for the wrong month',
    ])->assertRedirect(route('hr.kpi.undo', [$employee, 'period' => '2026-09']));

    $this->assertDatabaseMissing('kpi_records', [
        'employee_id' => $employee->id,
        'period' => '2026-09',
    ]);
    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $efficiency->id,
        'period' => '2026-08',
        'score' => 60,
    ]);
    expect(KpiScoreAudit::where('employee_id', $employee->id)->where('action', 'undo')->count())->toBe(2);
});

test('hr can remove a legacy score when no prior history exists', function () {
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

    $this->actingAs($hr)->post(route('hr.kpi.undo-score', $employee), [
        'period' => '2026-09',
        'kpi_indicator_id' => $indicator->id,
        'reason' => 'Remove legacy entry encoded in error',
    ])->assertRedirect();

    $this->assertDatabaseMissing('kpi_records', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $indicator->id,
        'period' => '2026-09',
    ]);
    $this->assertDatabaseHas('kpi_score_audits', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $indicator->id,
        'action' => 'undo',
        'reason' => 'Remove legacy entry encoded in error',
    ]);
});

test('hr cannot undo an older submission over a newer KPI edit', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $indicator = createManualKpiIndicator('Efficiency');

    $this->actingAs($hr)->post(route('hr.kpi.store', $employee), [
        'period' => '2026-09',
        'scores' => [$indicator->id => 80],
    ])->assertRedirect();
    $originalTransaction = KpiScoreAudit::where('employee_id', $employee->id)->value('transaction_id');

    $this->post(route('hr.kpi.store', $employee), [
        'period' => '2026-09',
        'scores' => [$indicator->id => 95],
        'reason' => 'Updated after review',
    ])->assertRedirect();

    $this->from(route('hr.kpi.undo', [$employee, 'period' => '2026-09']))
        ->post(route('hr.kpi.undo-submission', $employee), [
            'period' => '2026-09',
            'transaction_id' => $originalTransaction,
            'reason' => 'Attempt to undo old submission',
        ])
        ->assertRedirect(route('hr.kpi.undo', [$employee, 'period' => '2026-09']))
        ->assertSessionHasErrors('transaction_id');

    expect((float) KpiRecord::where('employee_id', $employee->id)->where('period', '2026-09')->value('score'))->toBe(95.0);
});

test('manager cannot access HR KPI undo pages or actions', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($manager)
        ->get(route('hr.kpi.undo', $employee))
        ->assertForbidden();

    $this->post(route('hr.kpi.undo-all', $employee), [
        'period' => '2026-09',
        'reason' => 'Not authorized',
    ])->assertForbidden();
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
        'redirect_to' => route('hr.kpi.index', absolute: false),
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
        'reason' => 'Correcting the entered score',
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
        'reason' => 'Correcting the entered score',
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

test('hr can delete one employees saved KPI entry and re-encode it without touching other records', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $otherEmployee = User::factory()->create(['role' => 'employee']);
    $efficiency = createManualKpiIndicator('Efficiency');
    $quality = createManualKpiIndicator('Work Quality');

    foreach ([[$employee, $efficiency, 80], [$employee, $quality, 70], [$otherEmployee, $efficiency, 90]] as [$owner, $indicator, $score]) {
        KpiRecord::create([
            'employee_id' => $owner->id,
            'kpi_indicator_id' => $indicator->id,
            'period' => '2026-09',
            'score' => $score,
            'encoded_by' => $hr->id,
        ]);
    }

    KpiRecord::create([
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $efficiency->id,
        'period' => '2026-08',
        'score' => 60,
        'encoded_by' => $hr->id,
    ]);

    $this->actingAs($hr)
        ->get(route('hr.kpi.saved-entry.delete', [$employee, 'period' => '2026-09']))
        ->assertOk()
        ->assertSee($employee->name)
        ->assertSee('2026-09')
        ->assertSee('Efficiency: 80.00')
        ->assertSee('Delete entire KPI entry')
        ->assertSee('Delete score');

    expect($employee->overallScore('2026-09'))->toBe(25.5);

    $this->post(route('hr.kpi.saved-entry.destroy', $employee), [
        'period' => '2026-09',
        'reason' => 'Incorrect review entry',
    ])->assertRedirect(route('hr.kpi.saved-entry.delete', [$employee, 'period' => '2026-09']));

    $this->assertDatabaseMissing('kpi_records', [
        'employee_id' => $employee->id,
        'period' => '2026-09',
    ]);
    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $efficiency->id,
        'period' => '2026-08',
    ]);
    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $otherEmployee->id,
        'kpi_indicator_id' => $efficiency->id,
        'period' => '2026-09',
    ]);
    expect($employee->fresh()->overallScore('2026-09'))->toBe(0.0);

    $audits = KpiScoreAudit::where('employee_id', $employee->id)
        ->where('period', '2026-09')
        ->where('action', 'deleted')
        ->get();
    expect($audits)->toHaveCount(2);
    $this->assertDatabaseHas('kpi_score_audits', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $efficiency->id,
        'period' => '2026-09',
        'action' => 'deleted',
        'actor_id' => $hr->id,
        'reason' => 'Incorrect review entry',
    ]);
    expect($audits->first()->original_value)->not->toBeNull()
        ->and($audits->first()->created_at)->not->toBeNull();

    $this->post(route('hr.kpi.store', $employee), [
        'period' => '2026-09',
        'scores' => [$efficiency->id => 95, $quality->id => 88],
    ])->assertRedirect(route('hr.kpi.index'));

    expect(KpiRecord::where('employee_id', $employee->id)->where('period', '2026-09')->count())->toBe(2)
        ->and((float) KpiRecord::where('employee_id', $employee->id)->where('kpi_indicator_id', $efficiency->id)->where('period', '2026-09')->value('score'))->toBe(95.0);
});

test('hr can delete only one saved KPI criterion for the intended employee and period', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);
    $otherEmployee = User::factory()->create(['role' => 'employee']);
    $efficiency = createManualKpiIndicator('Efficiency');
    $quality = createManualKpiIndicator('Work Quality');

    foreach ([[$employee, $efficiency, '2026-09', 80], [$employee, $quality, '2026-09', 70], [$employee, $efficiency, '2026-08', 60], [$otherEmployee, $efficiency, '2026-09', 90]] as [$owner, $indicator, $period, $score]) {
        KpiRecord::create([
            'employee_id' => $owner->id,
            'kpi_indicator_id' => $indicator->id,
            'period' => $period,
            'score' => $score,
            'encoded_by' => $hr->id,
        ]);
    }

    $otherOnlyIndicator = createManualKpiIndicator('Productivity');
    KpiRecord::create([
        'employee_id' => $otherEmployee->id,
        'kpi_indicator_id' => $otherOnlyIndicator->id,
        'period' => '2026-09',
        'score' => 50,
        'encoded_by' => $hr->id,
    ]);

    expect($employee->overallScore('2026-09'))->toBe(25.5);

    $this->actingAs($hr)->from(route('hr.kpi.saved-entry.delete', [$employee, 'period' => '2026-09']))
        ->post(route('hr.kpi.saved-score.destroy', $employee), [
            'period' => '2026-09',
            'kpi_indicator_id' => $efficiency->id,
            'reason' => '',
        ])
        ->assertRedirect(route('hr.kpi.saved-entry.delete', [$employee, 'period' => '2026-09']))
        ->assertSessionHasErrors('reason');

    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $efficiency->id,
        'period' => '2026-09',
    ]);

    $this->actingAs($hr)->post(route('hr.kpi.saved-score.destroy', $employee), [
        'period' => '2026-09',
        'kpi_indicator_id' => $efficiency->id,
        'reason' => 'Incorrect efficiency score',
    ])->assertRedirect(route('hr.kpi.saved-entry.delete', [$employee, 'period' => '2026-09']));

    $this->assertDatabaseMissing('kpi_records', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $efficiency->id,
        'period' => '2026-09',
    ]);
    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $quality->id,
        'period' => '2026-09',
    ]);
    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $employee->id,
        'kpi_indicator_id' => $efficiency->id,
        'period' => '2026-08',
    ]);
    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $otherEmployee->id,
        'kpi_indicator_id' => $efficiency->id,
        'period' => '2026-09',
    ]);
    expect($employee->fresh()->overallScore('2026-09'))->toBe(17.5);
    $this->assertDatabaseHas('kpi_score_audits', [
        'employee_id' => $employee->id,
        'period' => '2026-09',
        'kpi_indicator_id' => $efficiency->id,
        'action' => 'deleted',
        'actor_id' => $hr->id,
        'reason' => 'Incorrect efficiency score',
    ]);

    $this->from(route('hr.kpi.saved-entry.delete', [$employee, 'period' => '2026-09']))
        ->post(route('hr.kpi.saved-score.destroy', $employee), [
            'period' => '2026-09',
            'kpi_indicator_id' => $efficiency->id,
            'reason' => 'Duplicate delete request',
        ])
        ->assertRedirect(route('hr.kpi.saved-entry.delete', [$employee, 'period' => '2026-09']))
        ->assertSessionHasErrors('kpi_indicator_id');

    $this->from(route('hr.kpi.saved-entry.delete', [$employee, 'period' => '2026-09']))
        ->post(route('hr.kpi.saved-score.destroy', $employee), [
            'period' => '2026-09',
            'kpi_indicator_id' => $otherOnlyIndicator->id,
            'reason' => 'Attempt to target another employees criterion',
        ])
        ->assertRedirect(route('hr.kpi.saved-entry.delete', [$employee, 'period' => '2026-09']))
        ->assertSessionHasErrors('kpi_indicator_id');

    $this->assertDatabaseHas('kpi_records', [
        'employee_id' => $otherEmployee->id,
        'kpi_indicator_id' => $otherOnlyIndicator->id,
        'period' => '2026-09',
    ]);
});

test('only HR can use saved KPI deletion actions', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($manager)
        ->get(route('hr.kpi.saved-entry.delete', [$employee, 'period' => '2026-09']))
        ->assertForbidden();

    $this->post(route('hr.kpi.saved-entry.destroy', $employee), [
        'period' => '2026-09',
        'reason' => 'Not authorized',
    ])->assertForbidden();
});
