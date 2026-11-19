<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class HrDashboardController extends Controller
{
    public function index(Request $request)
    {
        $departmentId = $request->query('department');
        $period = $request->query('period', Carbon::now()->format('Y-m'));

        $departments = Department::all();

               $search = $request->query('search');

        $employeesQuery = User::whereIn('role', ['employee', 'manager'])->with('department');
        if ($departmentId) {
            $employeesQuery->where('department_id', $departmentId);
        }
        if ($search) {
            $employeesQuery->where('name', 'like', "%{$search}%");
        }
        $employees = $employeesQuery->get();

        $indicatorNames = ['Productivity', 'Work Quality', 'Efficiency', 'Timeliness', 'Attendance', 'Punctuality'];

        // Per-employee overall score for the selected month
        $employeeSummaries = $employees->map(function ($employee) use ($period) {
            return [
                'employee' => $employee,
                'overallScore' => $employee->overallScore($period),
            ];
        });

        $companyAverage = $employeeSummaries->count() > 0
            ? round($employeeSummaries->avg('overallScore'), 2)
            : 0;

        // KPI breakdown: average per indicator, across selected employees, for the selected month
        $breakdown = [];
        foreach ($indicatorNames as $name) {
            $values = collect();
            foreach ($employees as $employee) {
                $s = $employee->kpiScoresForPeriod($period);
                if (isset($s[$name])) {
                    $values->push($s[$name]);
                }
            }
            $breakdown[$name] = $values->count() > 0 ? round($values->avg(), 2) : 0;
        }

        // Trend: trailing 3 months leading up to the selected month, one line per indicator
        $trendPeriods = [];
        for ($i = 2; $i >= 0; $i--) {
            $trendPeriods[] = Carbon::parse($period . '-01')->subMonths($i)->format('Y-m');
        }
        $trendLabels = collect($trendPeriods)->map(fn($p) => Carbon::parse($p . '-01')->format('M Y'))->toArray();

        $trendDatasets = [];
        foreach ($indicatorNames as $name) {
            $data = [];
            foreach ($trendPeriods as $p) {
                $values = collect();
                foreach ($employees as $employee) {
                    $s = $employee->kpiScoresForPeriod($p);
                    if (isset($s[$name])) {
                        $values->push($s[$name]);
                    }
                }
                $data[] = $values->count() > 0 ? round($values->avg(), 2) : 0;
            }
            $trendDatasets[$name] = $data;
        }

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $employeeSummaries = new LengthAwarePaginator(
            $employeeSummaries->forPage($currentPage, $perPage)->values(),
            $employeeSummaries->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('hr.dashboard', compact(
            'departments', 'departmentId', 'period', 'search',
            'employeeSummaries', 'companyAverage', 'breakdown',
            'trendLabels', 'trendDatasets'
        ));
    }
}