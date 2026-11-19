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
        $role = $request->query('role');
        $period = $request->query('period', now()->format('Y-m'));

        $employees = User::whereIn('role', ['employee', 'manager'])
            ->with('department')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->get();
        $indicators = KpiIndicator::whereIn('name', $this->manualIndicatorNames())
            ->get()
            ->sortBy(fn ($indicator) => array_search($indicator->name, $this->manualIndicatorNames(), true))
            ->values();
        $records = KpiRecord::whereIn('employee_id', $employees->pluck('id'))
            ->where('period', $period)
            ->get()
            ->groupBy('employee_id');

        return view('hr.kpi.index', compact('employees', 'search', 'role', 'period', 'indicators', 'records'));
    }

    public function create(User $employee)
    {
        $indicators = KpiIndicator::whereIn('name', $this->manualIndicatorNames())->get();

        return view('hr.kpi.create', compact('employee', 'indicators'));
    }

    public function edit(Request $request, User $employee)
    {
        $period = $request->query('period', now()->format('Y-m'));
        $indicators = KpiIndicator::whereIn('name', $this->manualIndicatorNames())->get();
        $records = KpiRecord::where('employee_id', $employee->id)
            ->where('period', $period)
            ->get()
            ->keyBy('kpi_indicator_id');

        return view('hr.kpi.edit', compact('employee', 'indicators', 'period', 'records'));
    }

    public function bulk(Request $request)
    {
        $departmentId = $request->query('department');
        $search = $request->query('search');

        $departments = Department::when(auth()->user()->role === 'manager', fn ($query) => $query->whereKey(auth()->user()->department_id))
            ->orderBy('name')
            ->get();
        $employees = User::whereIn('role', ['employee', 'manager'])
            ->with('department')
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->when(auth()->user()->role === 'manager', fn ($query) => $query->where('department_id', auth()->user()->department_id))
            ->when($search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();
        $indicators = KpiIndicator::whereIn('name', $this->bulkIndicatorNames())->orderBy('name')->get();

        return view('hr.kpi.bulk', compact('departments', 'departmentId', 'search', 'employees', 'indicators'));
    }

    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'kpi_indicator_id' => ['required', 'integer', 'exists:kpi_indicators,id'],
            'remarks' => ['nullable', 'string'],
        ]);

        $indicator = KpiIndicator::whereIn('name', $this->manualIndicatorNames())
            ->find($validated['kpi_indicator_id']);
        abort_if(! $indicator, 422, 'One or more selected KPIs are invalid.');

        if ($indicator->name === 'Productivity') {
            $workload = $request->validate([
                'workload_target' => ['required', 'array'],
                'workload_target.*' => ['required', 'integer', 'min:1', 'max:2147483647'],
                'workload_completed' => ['required', 'array'],
                'workload_completed.*' => ['required', 'integer', 'min:0', 'max:2147483647'],
            ]);

            $selectedEmployeeIds = array_map('intval', $validated['employee_ids']);
            $targetEmployeeIds = array_map('intval', array_keys($workload['workload_target']));
            $completedEmployeeIds = array_map('intval', array_keys($workload['workload_completed']));
            sort($selectedEmployeeIds);
            sort($targetEmployeeIds);
            sort($completedEmployeeIds);

            abort_if(
                $selectedEmployeeIds !== $targetEmployeeIds || $selectedEmployeeIds !== $completedEmployeeIds,
                422,
                'Enter a workload target and completed count for every selected employee.'
            );
        } else {
            $validated += $request->validate([
                'score' => ['required', 'numeric', 'min:0', 'max:100'],
            ]);
        }

        $employeeIds = User::whereIn('role', ['employee', 'manager'])
            ->whereIn('id', $validated['employee_ids'])
            ->when(auth()->user()->role === 'manager', fn ($query) => $query->where('department_id', auth()->user()->department_id))
            ->pluck('id');

        abort_if($employeeIds->count() !== count($validated['employee_ids']), 422, 'One or more selected users cannot receive KPI scores.');

        foreach ($employeeIds as $employeeId) {
            $recordValues = $indicator->name === 'Productivity'
                ? [
                    'score' => KpiRecord::productivityScore(
                        (int) $workload['workload_target'][$employeeId],
                        (int) $workload['workload_completed'][$employeeId]
                    ),
                    'workload_target' => $workload['workload_target'][$employeeId],
                    'workload_completed' => $workload['workload_completed'][$employeeId],
                    'encoded_by' => auth()->id(),
                    'remarks' => $validated['remarks'] ?? null,
                ]
                : [
                    'score' => $validated['score'],
                    'encoded_by' => auth()->id(),
                    'remarks' => $validated['remarks'] ?? null,
                ];

            KpiRecord::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'kpi_indicator_id' => $indicator->id,
                    'period' => $validated['period'],
                ],
                $recordValues
            );
        }

        $savedMessage = $indicator->name === 'Productivity'
            ? "{$employeeIds->count()} productivity workload record(s) saved."
            : "{$employeeIds->count()} KPI score(s) saved for {$indicator->name}.";

        return redirect()->back()->with('success', $savedMessage);
    }

    public function store(Request $request, User $employee)
    {
        $manualIndicators = KpiIndicator::whereIn('name', $this->manualIndicatorNames())->get();
        $productivityIndicator = $manualIndicators->firstWhere('name', 'Productivity');
        $scoredIndicators = $manualIndicators->reject(fn ($indicator) => $indicator->name === 'Productivity');

        $rules = [
            'period' => ['required', 'date_format:Y-m'],
            'scores' => ['required', 'array'],
            'remarks' => ['nullable', 'string'],
        ];

        foreach ($scoredIndicators as $indicator) {
            $rules["scores.{$indicator->id}"] = ['required', 'numeric', 'min:0', 'max:100'];
        }

        if ($productivityIndicator) {
            $rules += [
                'workload_target' => ['required', 'integer', 'min:1', 'max:2147483647'],
                'workload_completed' => ['required', 'integer', 'min:0', 'max:2147483647'],
            ];
        }

        $validated = $request->validate($rules);

        $expectedScoreIds = $scoredIndicators->pluck('id')->map(fn ($id) => (int) $id)->all();
        $providedScoreIds = array_map('intval', array_keys($validated['scores']));

        abort_if(
            count($providedScoreIds) !== count($expectedScoreIds)
                || array_diff($providedScoreIds, $expectedScoreIds)
                || array_diff($expectedScoreIds, $providedScoreIds),
            422,
            'One or more selected KPIs are invalid.'
        );

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

        if ($productivityIndicator) {
            $target = (int) $validated['workload_target'];
            $completed = (int) $validated['workload_completed'];

            KpiRecord::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'kpi_indicator_id' => $productivityIndicator->id,
                    'period' => $validated['period'],
                ],
                [
                    'score' => KpiRecord::productivityScore($target, $completed),
                    'workload_target' => $target,
                    'workload_completed' => $completed,
                    'encoded_by' => auth()->id(),
                    'remarks' => $validated['remarks'] ?? null,
                ]
            );
        }

        return redirect()->route('hr.kpi.index')->with('success', "KPI scores saved for {$employee->name}.");
    }

    private function bulkIndicatorNames(): array
    {
        return $this->manualIndicatorNames();
    }

    private function manualIndicatorNames(): array
    {
        return ['Productivity', 'Work Quality', 'Efficiency', 'Timeliness'];
    }
}