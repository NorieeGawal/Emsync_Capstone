<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;
use App\Models\KpiIndicator;
use App\Models\KpiRecord;

class HrKpiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $employees = User::whereIn('role', ['employee', 'manager'])
            ->with('department')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->get();

        return view('hr.kpi.index', compact('employees', 'search'));
    }

    public function create(User $employee)
    {
        $indicators = KpiIndicator::whereIn('name', $this->manualIndicatorNames())->get();

        return view('hr.kpi.create', compact('employee', 'indicators'));
    }

    public function bulk(Request $request)
    {
        $departmentId = $request->query('department');
        $search = $request->query('search');

        $departments = Department::orderBy('name')->get();
        $employees = User::whereIn('role', ['employee', 'manager'])
            ->with('department')
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->when($search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();
        $indicators = KpiIndicator::whereIn('name', $this->manualIndicatorNames())->orderBy('name')->get();

        return view('hr.kpi.bulk', compact('departments', 'departmentId', 'search', 'employees', 'indicators'));
    }

    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'kpi_indicator_id' => ['required', 'integer', 'exists:kpi_indicators,id'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'remarks' => ['nullable', 'string'],
        ]);

        $indicator = KpiIndicator::whereIn('name', $this->manualIndicatorNames())
            ->findOrFail($validated['kpi_indicator_id']);
        $employeeIds = User::whereIn('role', ['employee', 'manager'])
            ->whereIn('id', $validated['employee_ids'])
            ->pluck('id');

        abort_if($employeeIds->count() !== count($validated['employee_ids']), 422, 'One or more selected users cannot receive KPI scores.');

        foreach ($employeeIds as $employeeId) {
            KpiRecord::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'kpi_indicator_id' => $indicator->id,
                    'period' => $validated['period'],
                ],
                [
                    'score' => $validated['score'],
                    'encoded_by' => auth()->id(),
                    'remarks' => $validated['remarks'] ?? null,
                ]
            );
        }

        return redirect()->back()->with('success', "{$employeeIds->count()} KPI score(s) saved for {$indicator->name}.");
    }

    public function store(Request $request, User $employee)
    {
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'scores' => ['required', 'array'],
            'scores.*' => ['required', 'numeric', 'min:0', 'max:100'],
            'remarks' => ['nullable', 'string'],
        ]);

        $indicators = KpiIndicator::whereIn('name', $this->manualIndicatorNames())
            ->whereIn('id', array_keys($validated['scores']))
            ->pluck('id');

        abort_if($indicators->count() !== count($validated['scores']), 422, 'One or more selected KPIs are invalid.');

        foreach ($validated['scores'] as $indicatorId => $score) {
            KpiRecord::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'kpi_indicator_id' => $indicatorId,
                    'period' => $validated['period'],
                ],
                [
                    'score' => $score,
                    'encoded_by' => auth()->id(),
                    'remarks' => $validated['remarks'] ?? null,
                ]
            );
        }

        return redirect()->route('hr.kpi.index')->with('success', "KPI scores saved for {$employee->name}.");
    }

    private function manualIndicatorNames(): array
    {
        return ['Productivity', 'Work Quality', 'Efficiency', 'Timeliness'];
    }
}