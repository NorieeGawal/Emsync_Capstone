<?php

use App\Models\AttendanceLog;
use App\Models\User;

test('employee dashboard paginates recent attendance ten records at a time', function () {
    $employee = User::factory()->create([
        'role' => 'employee',
        'email_verified_at' => now(),
    ]);

    foreach (range(1, 11) as $day) {
        AttendanceLog::create([
            'employee_id' => $employee->id,
            'date' => sprintf('2026-09-%02d', $day),
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
            'status' => 'present',
        ]);
    }

    $this->actingAs($employee)
        ->get(route('employee.dashboard', ['period' => '2026-09']))
        ->assertOk()
        ->assertSee('Sep 11')
        ->assertDontSee('Sep 01')
        ->assertSee('attendance_page=2');

    $this->get(route('employee.dashboard', [
        'period' => '2026-09',
        'attendance_page' => 2,
    ]))
        ->assertOk()
        ->assertSee('Sep 01')
        ->assertDontSee('Sep 11');
});
