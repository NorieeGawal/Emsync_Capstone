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

test('attendance pages use explicit 24-hour time fields', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($hr)
        ->get(route('hr.attendance.bulk'))
        ->assertOk()
        ->assertSee('type="text" name="scheduled_time_in"', false)
        ->assertSee('type="text" name="scheduled_time_out"', false)
        ->assertSee('type="text" name="entries['.$employee->id.'][time_in]"', false)
        ->assertSee('type="text" name="entries['.$employee->id.'][time_out]"', false)
        ->assertSee('pattern="([01][0-9]|2[0-3]):[0-5][0-9]"', false)
        ->assertDontSee('type="time"', false);

    $this->get(route('hr.attendance.create', $employee))
        ->assertOk()
        ->assertSee('type="text" name="time_in"', false)
        ->assertSee('type="text" name="time_out"', false)
        ->assertSee('Use 24-hour time (HH:MM).');
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

test('attendance status uses the schedule boundary in bulk entries', function () {
    $hr = User::factory()->create(['role' => 'hr']);
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($hr)->post(route('hr.attendance.settings.update'), [
        'scheduled_time_in' => '08:00',
        'scheduled_time_out' => '17:00',
    ]);

    foreach ([
        '2026-09-17' => ['07:30', 'present'],
        '2026-09-18' => ['08:00', 'present'],
        '2026-09-19' => ['08:01', 'late'],
    ] as $date => [$timeIn, $expectedStatus]) {
        $this->actingAs($hr)->post(route('hr.attendance.bulk.store'), [
            'date' => $date,
            'entries' => [
                $employee->id => [
                    'status' => 'present',
                    'time_in' => $timeIn,
                    'time_out' => '17:00',
                ],
            ],
        ]);

        expect(AttendanceLog::where('employee_id', $employee->id)->where('date', $date)->first()->status)
            ->toBe($expectedStatus);
    }
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
