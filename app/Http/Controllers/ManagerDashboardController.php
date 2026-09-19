<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class ManagerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $manager = auth()->user();
        $period = $request->query('period', Carbon::now()->format('Y-m'));

        $employees = User::where('role', 'employee')
            ->where('department_id', $manager->department_id)
            ->get();

        $employeeScores = $employees->map(function ($employee) use ($period) {
            return [
                'employee' => $employee,
                'overallScore' => $employee->overallScore($period),
            ];
        });

        $departmentAverage = $employeeScores->count() > 0
            ? round($employeeScores->avg('overallScore'), 2)
            : 0;

        // Department average trend over the last 3 months
        $trendLabels = [];
        $trendScores = [];
        for ($i = 2; $i >= 0; $i--) {
            $p = Carbon::parse($period . '-01')->subMonths($i)->format('Y-m');
            $trendLabels[] = Carbon::parse($p . '-01')->format('M Y');

            $monthScores = $employees->map(fn($e) => $e->overallScore($p));
            $trendScores[] = $monthScores->count() > 0 ? round($monthScores->avg(), 2) : 0;
        }

        return view('manager.dashboard', compact(
            'manager', 'period', 'employeeScores', 'departmentAverage',
            'trendLabels', 'trendScores'
        ));
    }
}