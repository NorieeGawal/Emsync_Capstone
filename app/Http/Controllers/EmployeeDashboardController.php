<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request)
    {
        $employee = auth()->user();
        $period = $request->query('period', Carbon::now()->format('Y-m'));

        $scores = $employee->kpiScoresForPeriod($period);
        $productivityRecord = $employee->productivityRecordForPeriod($period);
        $overallScore = $employee->overallScore($period);

        $recentLogs = $employee->attendanceLogs()->orderBy('date', 'desc')->take(5)->get();

        $notes = $employee->performanceNotes()->with('author')->latest()->get();

        // Build trend data for the last 3 months (including current)
        $trendLabels = [];
        $trendScores = [];
        for ($i = 2; $i >= 0; $i--) {
            $p = Carbon::parse($period . '-01')->subMonths($i)->format('Y-m');
            $trendLabels[] = Carbon::parse($p . '-01')->format('M Y');
            $trendScores[] = $employee->overallScore($p);
        }

        return view('employee.dashboard', compact(
            'employee', 'period', 'scores', 'productivityRecord', 'overallScore', 'recentLogs',
            'trendLabels', 'trendScores', 'notes'
        ));
    }
}