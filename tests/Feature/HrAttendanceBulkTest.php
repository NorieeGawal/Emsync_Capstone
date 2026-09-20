<?php

use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\Models\User;
use Carbon\Carbon;

test('hr can open the bulk attendance page', function () {
    $hr = User::factory()->create(['role' => 'hr']);

    $this->actingAs($hr)
        ->get(route('hr.attendance.bulk'))
        ->assertOk();
});

test('hr can save attendance for multiple employees at once', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employeeOne = User::factory()->create(['role' => 'employee']);
    $employeeTwo = User::factory()->create(['role' => 'employee']);

    $response = $this->actingAs($hr)
        ->post(route('hr.attendance.bulk.store'), [
            'date' => '2026-09-16',
            'entries' => [
                $employeeOne->id => [
                    'status' => 'present',
                    'time_in' => '08:00',
                    'time_out' => '17:00',
                ],
                $employeeTwo->id => [
                    'status' => 'late',
                    'time_in' => '09:15',
                    'time_out' => '17:30',
                ],
            ],
        ]);

    $response->assertRedirect(route('hr.attendance.bulk', ['date' => '2026-09-16']));
    $this->assertDatabaseHas('attendance_logs', [
        'employee_id' => $employeeOne->id,
        'date' => '2026-09-16',
        'status' => 'present',
    ]);
    $this->assertDatabaseHas('attendance_logs', [
        'employee_id' => $employeeTwo->id,
        'date' => '2026-09-16',
        'status' => 'late',
    ]);
    $timeIn = AttendanceLog::where('employee_id', $employeeOne->id)->where('date', '2026-09-16')->first()->time_in;
    expect(Carbon::parse($timeIn)->format('H:i'))->toBe('08:00');
});

test('hr can set the company attendance schedule', function () {
    $hr = User::factory()->create(['role' => 'hr']);

    $response = $this->actingAs($hr)->post(route('hr.attendance.settings.update'), [
        'scheduled_time_in' => '08:00',
        'scheduled_time_out' => '17:00',
    ]);

    $response->assertRedirect();
    expect(Carbon::parse(AttendanceSetting::current()->scheduled_time_in)->format('H:i'))->toBe('08:00');
    expect(Carbon::parse(AttendanceSetting::current()->scheduled_time_out)->format('H:i'))->toBe('17:00');
});

test('attendance status is automatically based on the scheduled time in', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($hr)->post(route('hr.attendance.settings.update'), [
        'scheduled_time_in' => '08:00',
        'scheduled_time_out' => '17:00',
    ]);

    $this->actingAs($hr)->post(route('hr.attendance.bulk.store'), [
        'date' => '2026-09-17',
        'entries' => [
            $employee->id => [
                'status' => 'present',
                'time_in' => '08:01',
                'time_out' => '17:00',
            ],
        ],
    ]);

    expect(AttendanceLog::where('employee_id', $employee->id)->first()->status)->toBe('late');
});

test('bulk attendance rejects users outside the employee and manager roles', function () {
    $hr = User::factory()->create(['role' => 'hr']);

    $this->actingAs($hr)
        ->post(route('hr.attendance.bulk.store'), [
            'date' => '2026-09-18',
            'entries' => [
                $hr->id => [
                    'status' => 'present',
                    'time_in' => '08:00',
                    'time_out' => '17:00',
                ],
            ],
        ])
        ->assertStatus(422);

    $this->assertDatabaseMissing('attendance_logs', [
        'employee_id' => $hr->id,
        'date' => '2026-09-18',
    ]);
});
