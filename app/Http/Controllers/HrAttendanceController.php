<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;

class HrAttendanceController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('hr.attendance.bulk', [
            'date' => $request->query('date', now()->toDateString()),
            'department' => $request->query('department'),
            'search' => $request->query('search'),
        ]);
    }

    public function bulk(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $departmentId = $request->query('department');
        $search = $request->query('search');

        $departments = Department::all();

        $employees = User::whereIn('role', ['employee', 'manager'])
            ->with('department')
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->when($search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        $existingLogs = AttendanceLog::where('date', $date)
            ->get()
            ->keyBy(fn ($log) => $log->employee_id);
        $settings = AttendanceSetting::current();

        return view('hr.attendance.bulk', compact('date', 'departmentId', 'search', 'departments', 'employees', 'existingLogs', 'settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'scheduled_time_in' => ['required', 'date_format:H:i'],
            'scheduled_time_out' => ['required', 'date_format:H:i', 'after:scheduled_time_in'],
        ]);

        AttendanceSetting::current()->update($validated);

        return redirect()->back()->with('success', 'Attendance schedule updated.');
    }

    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'entries' => 'required|array',
            'entries.*.status' => 'required|in:present,late,absent,excused',
            'entries.*.time_in' => 'nullable|date_format:H:i',
            'entries.*.time_out' => 'nullable|date_format:H:i',
        ]);

        foreach ($validated['entries'] as $employeeId => $entry) {
            $status = $entry['status'];
            if (in_array($status, ['present', 'late'], true) && !empty($entry['time_in'])) {
                $status = $entry['time_in'] > AttendanceSetting::current()->scheduled_time_in ? 'late' : 'present';
            }

            AttendanceLog::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date' => $validated['date'],
                ],
                [
                    'status' => $status,
                    'time_in' => $entry['time_in'] ?? null,
                    'time_out' => $entry['time_out'] ?? null,
                ]
            );
        }

        return redirect()->route('hr.attendance.bulk', ['date' => $validated['date']])->with('success', 'Attendance saved for all selected employees.');
    }

    public function create(User $employee)
    {
        $logs = $employee->attendanceLogs()->orderBy('date', 'desc')->take(10)->get();
        $settings = AttendanceSetting::current();
        return view('hr.attendance.create', compact('employee', 'logs', 'settings'));
    }

    public function store(Request $request, User $employee)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:present,late,absent,excused',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
        ]);

        if (in_array($validated['status'], ['present', 'late'], true) && !empty($validated['time_in'])) {
            $validated['status'] = $validated['time_in'] > AttendanceSetting::current()->scheduled_time_in ? 'late' : 'present';
        }

        AttendanceLog::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $validated['date'],
            ],
            [
                'status' => $validated['status'],
                'time_in' => $validated['time_in'] ?? null,
                'time_out' => $validated['time_out'] ?? null,
            ]
        );

        return redirect()->route('hr.attendance.create', $employee)->with('success', 'Attendance log saved.');
    }
}