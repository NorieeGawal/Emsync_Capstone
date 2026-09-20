<?php

use App\Models\AttendanceLog;
use App\Models\KpiIndicator;
use App\Models\KpiRecord;
use App\Models\User;

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
        'Attendance' => 0.0,
        'Punctuality' => 0.0,
    ]);
    expect($employee->overallScore('2026-09'))->toBe(0.0);
});
