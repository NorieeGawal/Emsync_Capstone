<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PerformanceNote;
use Carbon\Carbon;

class HrEmployeeDetailController extends Controller
{
    public function show(Request $request, User $employee)
    {
        $period = $request->query('period', Carbon::now()->format('Y-m'));

        $scores = $employee->kpiScoresForPeriod($period);
        $overallScore = $employee->overallScore($period);
        $recentLogs = $employee->attendanceLogs()->orderBy('date', 'desc')->take(5)->get();
        $notes = $employee->performanceNotes()->with('author')->latest()->get();

        return view('hr.employees.show', compact('employee', 'period', 'scores', 'overallScore', 'recentLogs', 'notes'));
    }

    public function storeNote(Request $request, User $employee)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        PerformanceNote::create([
            'employee_id' => $employee->id,
            'author_id' => auth()->id(),
            'note' => $validated['note'],
        ]);

        return redirect()->route('hr.employees.show', $employee)->with('success', 'Note added.');
    }
}