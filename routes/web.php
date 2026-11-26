<?php

use App\Http\Controllers\DashboardNotificationController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\HrAttendanceController;
use App\Http\Controllers\HrDashboardController;
use App\Http\Controllers\HrDepartmentController;
use App\Http\Controllers\HrEmployeeController;
use App\Http\Controllers\HrEmployeeDetailController;
use App\Http\Controllers\HrKpiController;
use App\Http\Controllers\HrReportController;
use App\Http\Controllers\ManagerDashboardController;
use App\Http\Controllers\ManagerEmployeeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing');

Route::get('/dashboard', function () {
    return match (auth()->user()->role) {
        'hr' => redirect()->route('hr.dashboard'),
        'manager' => redirect()->route('manager.dashboard'),
        'employee' => redirect()->route('employee.dashboard'),
        default => abort(403),
    };
})
    ->middleware(['auth', 'verified', 'prevent-back-history'])
    ->name('dashboard');

// Role middleware on each group is the authorization boundary for every route inside it.
Route::middleware(['auth', 'verified', 'role:hr', 'prevent-back-history'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/dashboard', [HrDashboardController::class, 'index'])->name('dashboard');
    Route::get('/employees/archived', [HrEmployeeController::class, 'archived'])->name('employees.archived');
    Route::post('/employees/{employee}/restore', [HrEmployeeController::class, 'restore'])->name('employees.restore');
    Route::resource('employees', HrEmployeeController::class)->parameters(['employees' => 'employee'])->except(['show']);
    Route::get('/kpi', [HrKpiController::class, 'index'])->name('kpi.index');
    Route::get('/kpi/bulk', [HrKpiController::class, 'bulk'])->name('kpi.bulk');
    Route::post('/kpi/bulk', [HrKpiController::class, 'storeBulk'])->name('kpi.bulk.store');
    Route::get('/kpi/delete-scores', [HrKpiController::class, 'deleteScores'])->name('kpi.scores.delete');
    Route::post('/kpi/delete-scores', [HrKpiController::class, 'destroyScores'])->name('kpi.scores.destroy');
    Route::get('/kpi/{employee}/delete-saved-entry', [HrKpiController::class, 'deleteSavedEntry'])->name('kpi.saved-entry.delete');
    Route::post('/kpi/{employee}/delete-saved-entry', [HrKpiController::class, 'destroySavedEntry'])->name('kpi.saved-entry.destroy');
    Route::post('/kpi/{employee}/delete-saved-score', [HrKpiController::class, 'destroySavedScore'])->name('kpi.saved-score.destroy');
    Route::get('/kpi/{employee}/undo', [HrKpiController::class, 'undoScores'])->name('kpi.undo');
    Route::post('/kpi/{employee}/undo-score', [HrKpiController::class, 'undoScore'])->name('kpi.undo-score');
    Route::post('/kpi/{employee}/undo-submission', [HrKpiController::class, 'undoSubmission'])->name('kpi.undo-submission');
    Route::post('/kpi/{employee}/undo-all', [HrKpiController::class, 'undoAllScores'])->name('kpi.undo-all');
    Route::get('/kpi/{employee}/edit', [HrKpiController::class, 'edit'])->name('kpi.edit');
    Route::get('/kpi/{employee}/create', [HrKpiController::class, 'create'])->name('kpi.create');
    Route::post('/kpi/{employee}', [HrKpiController::class, 'store'])->name('kpi.store');
    Route::get('/attendance', [HrAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/bulk', [HrAttendanceController::class, 'bulk'])->name('attendance.bulk');
    Route::post('/attendance/settings', [HrAttendanceController::class, 'updateSettings'])->name('attendance.settings.update');
    Route::post('/attendance/bulk', [HrAttendanceController::class, 'storeBulk'])->name('attendance.bulk.store');
    Route::get('/attendance/{employee}', [HrAttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/{employee}', [HrAttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/employees/{employee}/profile', [HrEmployeeDetailController::class, 'show'])->name('employees.show');
    Route::post('/employees/{employee}/notes', [HrEmployeeDetailController::class, 'storeNote'])->name('employees.notes.store');
    Route::get('/reports/employee/{employee}', [HrReportController::class, 'employeeReport'])->name('reports.employee');
    Route::get('/reports/summary', [HrReportController::class, 'summaryReport'])->name('reports.summary');
    Route::get('/departments', [HrDepartmentController::class, 'index'])->name('departments.index');
    Route::post('/departments', [HrDepartmentController::class, 'store'])->name('departments.store');
    Route::delete('/departments/{department}', [HrDepartmentController::class, 'destroy'])->name('departments.destroy');
});

Route::middleware(['auth', 'verified', 'role:manager', 'prevent-back-history'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/employees', [ManagerEmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}/profile', [ManagerEmployeeController::class, 'show'])->name('employees.show');
    Route::post('/employees/{employee}/notes', [ManagerEmployeeController::class, 'storeNote'])->name('employees.notes.store');
    Route::get('/kpi/bulk', [HrKpiController::class, 'bulk'])->name('kpi.bulk');
    Route::post('/kpi/bulk', [HrKpiController::class, 'storeBulk'])->name('kpi.bulk.store');
});

Route::middleware(['auth', 'verified', 'role:employee', 'prevent-back-history'])->group(function () {
    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
});
Route::middleware(['auth', 'prevent-back-history'])->group(function () {
    Route::post('/notifications/read', [DashboardNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [DashboardNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
