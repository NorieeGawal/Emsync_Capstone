<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class HrReportController extends Controller
{
    public function employeeReport(Request $request, User $employee)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'date_format:Y-m'],
        ]);
        $period = $validated['period'] ?? Carbon::now()->format('Y-m');

        $trendPeriods = [];
        for ($i = 2; $i >= 0; $i--) {
            $trendPeriods[] = Carbon::parse($period . '-01')->subMonths($i)->format('Y-m');
        }
        $employee = User::withMetricsForPeriods($trendPeriods)->findOrFail($employee->getKey());

        $scores = $employee->kpiScoresForPeriod($period);
        $productivityRecord = $employee->productivityRecordForPeriod($period);
        $overallScore = $employee->overallScore($period);
        $notes = $employee->performanceNotes()->with('author')->latest()->take(5)->get();

        $trendLabels = [];
        $trendScores = [];
        foreach ($trendPeriods as $p) {
            $trendLabels[] = Carbon::parse($p . '-01')->format('M Y');
            $trendScores[] = $employee->overallScore($p);
        }

        $pdf = Pdf::loadView('hr.reports.employee', compact(
            'employee', 'period', 'scores', 'productivityRecord', 'overallScore', 'notes',
            'trendLabels', 'trendScores'
        ));

        return $pdf->download('performance-report-' . str($employee->name)->slug() . '-' . $period . '.pdf');
    }

    public function summaryReport(Request $request)
    {
        $validated = $request->validate([
            'department' => ['nullable', 'integer', 'exists:departments,id'],
            'period' => ['nullable', 'date_format:Y-m'],
        ]);
        $departmentId = $validated['department'] ?? null;
        $period = $validated['period'] ?? Carbon::now()->format('Y-m');

        $selectedDepartment = $departmentId ? Department::find($departmentId) : null;

        $employeesQuery = User::whereIn('role', ['employee', 'manager'])
            ->with('department')
            ->withMetricsForPeriods([$period]);
        if ($departmentId) {
            $employeesQuery->where('department_id', $departmentId);
        }
        $employees = $employeesQuery->get();

        $employeeSummaries = $employees->map(function ($employee) use ($period) {
            return [
                'employee' => $employee,
                'overallScore' => $employee->overallScore($period),
            ];
        })->sortByDesc('overallScore')->values();

        $companyAverage = $employeeSummaries->count() > 0
            ? round($employeeSummaries->avg('overallScore'), 2)
            : 0;

        $indicatorNames = ['Productivity', 'Work Quality', 'Efficiency', 'Timeliness', 'Attendance', 'Punctuality'];
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

        $pdf = Pdf::loadView('hr.reports.summary', compact(
            'selectedDepartment', 'period', 'employeeSummaries', 'companyAverage', 'breakdown'
        ));

        $filename = 'summary-report-' . ($selectedDepartment ? str($selectedDepartment->name)->slug() : 'all-departments') . '-' . $period . '.pdf';

        return $pdf->download($filename);
    }
}