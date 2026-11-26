<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'date_format:Y-m'],
        ]);
        $period = $validated['period'] ?? Carbon::now()->format('Y-m');

        $trendPeriods = [];
        for ($i = 2; $i >= 0; $i--) {
            $trendPeriods[] = Carbon::parse($period . '-01')->subMonths($i)->format('Y-m');
        }

        $employee = User::withMetricsForPeriods($trendPeriods)->findOrFail($request->user()->id);

        $scores = $employee->kpiScoresForPeriod($period);
        $productivityRecord = $employee->productivityRecordForPeriod($period);
        $overallScore = $employee->overallScore($period);

        $recentLogs = $employee->attendanceLogs()
            ->orderByDesc('date')
            ->paginate(10, ['*'], 'attendance_page')
            ->withQueryString();

        $notes = $employee->performanceNotes()->with('author')->latest()->get();

        // Build trend data for the last 3 months (including current)
        $trendLabels = [];
        $trendScores = [];
        foreach ($trendPeriods as $p) {
            $trendLabels[] = Carbon::parse($p . '-01')->format('M Y');
            $trendScores[] = $employee->overallScore($p);
        }

        return view('employee.dashboard', compact(
            'employee', 'period', 'scores', 'productivityRecord', 'overallScore', 'recentLogs',
            'trendLabels', 'trendScores', 'notes'
        ));
    }
}