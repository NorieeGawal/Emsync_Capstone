<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:hr'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\HrDashboardController::class, 'index'])->name('dashboard');
    Route::resource('employees', App\Http\Controllers\HrEmployeeController::class)->parameters(['employees' => 'employee'])->except(['show']);
    Route::get('/kpi', [App\Http\Controllers\HrKpiController::class, 'index'])->name('kpi.index');
    Route::get('/kpi/bulk', [App\Http\Controllers\HrKpiController::class, 'bulk'])->name('kpi.bulk');
    Route::post('/kpi/bulk', [App\Http\Controllers\HrKpiController::class, 'storeBulk'])->name('kpi.bulk.store');
    Route::get('/kpi/{employee}/edit', [App\Http\Controllers\HrKpiController::class, 'edit'])->name('kpi.edit');
    Route::get('/kpi/{employee}/create', [App\Http\Controllers\HrKpiController::class, 'create'])->name('kpi.create');
    Route::post('/kpi/{employee}', [App\Http\Controllers\HrKpiController::class, 'store'])->name('kpi.store');
    Route::get('/attendance', [App\Http\Controllers\HrAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/bulk', [App\Http\Controllers\HrAttendanceController::class, 'bulk'])->name('attendance.bulk');
    Route::post('/attendance/settings', [App\Http\Controllers\HrAttendanceController::class, 'updateSettings'])->name('attendance.settings.update');
    Route::post('/attendance/bulk', [App\Http\Controllers\HrAttendanceController::class, 'storeBulk'])->name('attendance.bulk.store');
    Route::get('/attendance/{employee}', [App\Http\Controllers\HrAttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/{employee}', [App\Http\Controllers\HrAttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/employees/{employee}/profile', [App\Http\Controllers\HrEmployeeDetailController::class, 'show'])->name('employees.show');
    Route::post('/employees/{employee}/notes', [App\Http\Controllers\HrEmployeeDetailController::class, 'storeNote'])->name('employees.notes.store');
    Route::get('/reports/employee/{employee}', [App\Http\Controllers\HrReportController::class, 'employeeReport'])->name('reports.employee');
    Route::get('/reports/summary', [App\Http\Controllers\HrReportController::class, 'summaryReport'])->name('reports.summary');
    Route::get('/departments', [App\Http\Controllers\HrDepartmentController::class, 'index'])->name('departments.index');
    Route::post('/departments', [App\Http\Controllers\HrDepartmentController::class, 'store'])->name('departments.store');
    Route::delete('/departments/{department}', [App\Http\Controllers\HrDepartmentController::class, 'destroy'])->name('departments.destroy');
});

Route::middleware(['auth', 'verified', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\ManagerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/employees', [App\Http\Controllers\ManagerEmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}/profile', [App\Http\Controllers\ManagerEmployeeController::class, 'show'])->name('employees.show');
    Route::post('/employees/{employee}/notes', [App\Http\Controllers\ManagerEmployeeController::class, 'storeNote'])->name('employees.notes.store');
    Route::get('/kpi/bulk', [App\Http\Controllers\HrKpiController::class, 'bulk'])->name('kpi.bulk');
    Route::post('/kpi/bulk', [App\Http\Controllers\HrKpiController::class, 'storeBulk'])->name('kpi.bulk.store');
});

Route::middleware(['auth', 'verified', 'role:employee'])->group(function () {
    Route::get('/employee/dashboard', [App\Http\Controllers\EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
});
Route::middleware('auth')->group(function () {
    Route::post('/notifications/read', [App\Http\Controllers\DashboardNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\DashboardNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';