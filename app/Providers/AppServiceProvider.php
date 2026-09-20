<?php

namespace App\Providers;

use App\Models\AttendanceLog;
use App\Models\KpiIndicator;
use App\Models\KpiRecord;
use App\Models\NotificationRead;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.layouts.dashboard', function ($view): void {
            $user = Auth::user();

            if (!$user) {
                $view->with('headerNotifications', collect());
                return;
            }

            $notifications = collect();
            $period = now()->format('Y-m');
            $readNotifications = NotificationRead::where('user_id', $user->id)
                ->pluck('notification_key')
                ->all();

            $staffQuery = User::whereIn('role', ['employee', 'manager']);

            if ($user->role === 'manager') {
                $staffQuery->where('department_id', $user->department_id);
            } elseif ($user->role === 'employee') {
                $staffQuery->whereKey($user->id);
            }

            $staff = $staffQuery->get(['id', 'name', 'department_id', 'created_at']);
            $indicatorIds = KpiIndicator::whereIn('name', ['Productivity', 'Work Quality', 'Efficiency', 'Timeliness'])
                ->pluck('id');

            $kpiCounts = KpiRecord::whereIn('employee_id', $staff->pluck('id'))
                ->where('period', $period)
                ->whereIn('kpi_indicator_id', $indicatorIds)
                ->selectRaw('employee_id, COUNT(DISTINCT kpi_indicator_id) as score_count')
                ->groupBy('employee_id')
                ->pluck('score_count', 'employee_id');
            $missingKpiCount = $staff->filter(fn (User $employee): bool => (int) $kpiCounts->get($employee->id, 0) < $indicatorIds->count())->count();

            if ($missingKpiCount > 0 && $user->role !== 'employee') {
                $kpiRoute = $user->role === 'manager' ? 'manager.kpi.bulk' : 'hr.kpi.index';
                $notifications->push([
                    'key' => "kpi-incomplete-{$period}",
                    'title' => 'KPI scores incomplete',
                    'message' => "{$missingKpiCount} employee(s) need KPI scores for ".now()->format('F Y').'.',
                    'href' => route($kpiRoute),
                    'tone' => 'amber',
                ]);
            }

            $attendanceEmployeeIds = AttendanceLog::whereIn('employee_id', $staff->pluck('id'))
                ->whereDate('date', today())
                ->pluck('employee_id')
                ->unique();
            $missingAttendanceCount = $staff->pluck('id')->diff($attendanceEmployeeIds)->count();

            if ($missingAttendanceCount > 0 && $user->role !== 'employee') {
                $attendanceRoute = $user->role === 'manager' ? 'manager.dashboard' : 'hr.attendance.bulk';
                $notifications->push([
                    'key' => 'attendance-missing-'.today()->toDateString(),
                    'title' => 'Attendance missing',
                    'message' => "{$missingAttendanceCount} employee(s) have no attendance record today.",
                    'href' => route($attendanceRoute),
                    'tone' => 'red',
                ]);
            }

            $unassignedCount = $staff->whereNull('department_id')->count();

            if ($unassignedCount > 0 && $user->role !== 'employee') {
                $notifications->push([
                    'key' => 'department-assignment-needed',
                    'title' => 'Department assignment needed',
                    'message' => "{$unassignedCount} employee(s) are not assigned to a department.",
                    'href' => route($user->role === 'manager' ? 'manager.employees.index' : 'hr.employees.index'),
                    'tone' => 'slate',
                ]);
            }

            $recentAccountCount = $staff->filter(fn (User $employee): bool => $employee->created_at?->greaterThanOrEqualTo(now()->subDay()))->count();

            if ($recentAccountCount > 0 && $user->role === 'hr') {
                $notifications->push([
                    'key' => 'new-employee-accounts-'.today()->toDateString(),
                    'title' => 'New employee account',
                    'message' => "{$recentAccountCount} account(s) were added in the last 24 hours.",
                    'href' => route('hr.employees.index'),
                    'tone' => 'blue',
                ]);
            }

            $notifications = $notifications
                ->reject(fn (array $notification): bool => in_array($notification['key'], $readNotifications, true))
                ->take(5)
                ->values();

            $view->with('headerNotifications', $notifications);
        });
    }
}
