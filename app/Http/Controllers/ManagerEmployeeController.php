<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PerformanceNote;
use Carbon\Carbon;

class ManagerEmployeeController extends Controller
{
    public function index()
    {
        $manager = auth()->user();

        $employees = User::where('role', 'employee')
            ->where('department_id', $manager->department_id)
            ->paginate(15);

        return view('manager.employees.index', compact('employees'));
    }

    public function show(Request $request, User $employee)
    {
        $manager = auth()->user();

        // Security check: manager can only view employees in their own department
        if ($employee->department_id !== $manager->department_id || $employee->role !== 'employee') {
            abort(403, 'You can only view employees in your own department.');
        }

        $validated = $request->validate([
            'period' => ['nullable', 'date_format:Y-m'],
        ]);
        $period = $validated['period'] ?? Carbon::now()->format('Y-m');

        $scores = $employee->kpiScoresForPeriod($period);
        $productivityRecord = $employee->productivityRecordForPeriod($period);
        $overallScore = $employee->overallScore($period);
        $recentLogs = $employee->attendanceLogs()->orderBy('date', 'desc')->take(5)->get();
        $notes = $employee->performanceNotes()->with('author')->latest()->get();

        return view('manager.employees.show', compact('employee', 'period', 'scores', 'productivityRecord', 'overallScore', 'recentLogs', 'notes'));
    }

    public function storeNote(Request $request, User $employee)
    {
        $manager = auth()->user();

        if ($employee->department_id !== $manager->department_id || $employee->role !== 'employee') {
            abort(403, 'You can only leave feedback for employees in your own department.');
        }

        $validated = $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        PerformanceNote::create([
            'employee_id' => $employee->id,
            'author_id' => auth()->id(),
            'note' => $validated['note'],
        ]);

        return redirect()->route('manager.employees.show', $employee)->with('success', 'Feedback added.');
    }
}