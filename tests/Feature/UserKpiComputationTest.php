<?php

use App\Models\AttendanceLog;
use App\Models\KpiIndicator;
use App\Models\KpiRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function createComputationIndicator(string $name): KpiIndicator
{
    return KpiIndicator::create(['name' => $name]);
}

test('user KPI computation orders scores and calculates the weighted total', function () {
    $employee = User::factory()->create(['role' => 'employee']);
    $period = '2026-09';
    $indicators = collect([
        'Productivity' => createComputationIndicator('Productivity'),
        'Efficiency' => createComputationIndicator('Efficiency'),
        'Work Quality' => createComputationIndicator('Work Quality'),
        'Timeliness' => createComputationIndicator('Timeliness'),
    ]);

    $scores = [
        'Productivity' => 80,
        'Efficiency' => 90,
        'Work Quality' => 70,
        'Timeliness' => 100,
    ];

    foreach ($scores as $name => $score) {
        KpiRecord::create([
            'employee_id' => $employee->id,
            'kpi_indicator_id' => $indicators[$name]->id,
            'period' => $period,
            'score' => $score,
            'encoded_by' => $employee->id,
        ]);
    }

    AttendanceLog::insert([
        [
            'employee_id' => $employee->id,
            'date' => '2026-09-01',
            'status' => 'present',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'employee_id' => $employee->id,
            'date' => '2026-09-02',
            'status' => 'late',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'employee_id' => $employee->id,
            'date' => '2026-09-03',
            'status' => 'absent',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'employee_id' => $employee->id,
            'date' => '2026-09-04',
            'status' => 'excused',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    expect($employee->kpiScoresForPeriod($period))->toMatchArray([
        'Productivity' => '80.00',
        'Efficiency' => '90.00',
        'Work Quality' => '70.00',
        'Timeliness' => '100.00',
        'Attendance' => 50.0,
        'Punctuality' => 50.0,
    ]);
    expect($employee->overallScore($period))->toBe(73.5);
});

test('user KPI computation returns zero for a period without records', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    expect($employee->kpiScoresForPeriod('2026-09'))->toBe([
        'Productivity' => 0,
        'Efficiency' => 0,
        'Work Quality' => 0,
        'Timeliness' => 0,
        'Attendance' => 0.0,
        'Punctuality' => 0.0,
    ]);
    expect($employee->overallScore('2026-09'))->toBe(0.0);
});

test('preloaded period metrics calculate scores without per-employee queries', function () {
    $indicator = createComputationIndicator('Productivity');
    $employees = User::factory()->count(3)->create(['role' => 'employee']);

    foreach ($employees as $employee) {
        KpiRecord::create([
            'employee_id' => $employee->id,
            'kpi_indicator_id' => $indicator->id,
            'period' => '2026-09',
            'score' => 80,
            'encoded_by' => $employee->id,
        ]);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'date' => '2026-09-01',
            'status' => 'present',
        ]);
    }

    $queryCount = 0;
    DB::listen(function () use (&$queryCount): void {
        $queryCount++;
    });

    $scores = User::whereIn('id', $employees->pluck('id'))
        ->withMetricsForPeriods(['2026-09'])
        ->get()
        ->map(fn (User $employee): float => $employee->overallScore('2026-09'));

    expect($scores->all())->toBe([42.0, 42.0, 42.0])
        ->and($queryCount)->toBeLessThanOrEqual(4);
});
