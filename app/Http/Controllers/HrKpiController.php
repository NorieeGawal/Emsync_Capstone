<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\KpiIndicator;
use App\Models\KpiRecord;
use App\Models\KpiScoreAudit;
use App\Models\User;
use App\Services\KpiScoreHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HrKpiController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'in:employee,manager'],
            'period' => ['nullable', 'date_format:Y-m'],
        ]);
        $search = $filters['search'] ?? null;
        $role = $filters['role'] ?? null;
        $period = $filters['period'] ?? now()->format('Y-m');

        $employees = User::whereIn('role', ['employee', 'manager'])
            ->with('department')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
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

    public function undoScores(Request $request, User $employee)
    {
        abort_unless(in_array($employee->role, ['employee', 'manager'], true), 404);
        $validated = $request->validate([
            'period' => ['nullable', 'date_format:Y-m'],
        ]);
        $period = $validated['period'] ?? now()->format('Y-m');
        $records = KpiRecord::with('kpiIndicator')
            ->where('employee_id', $employee->id)
            ->where('period', $period)
            ->orderBy('kpi_indicator_id')
            ->get();
        $activeAudits = KpiScoreAudit::where('employee_id', $employee->id)
            ->where('period', $period)
            ->whereIn('action', ['created', 'updated', 'deleted'])
            ->whereNull('reversed_at')
            ->orderByDesc('id')
            ->get();
        $latestByIndicator = $activeAudits->groupBy('kpi_indicator_id')
            ->map(fn ($entries) => $entries->first());
        $transactions = $activeAudits->groupBy('transaction_id')
            ->filter(fn ($entries) => $entries->every(
                fn (KpiScoreAudit $entry): bool => $latestByIndicator->get($entry->kpi_indicator_id)?->id === $entry->id
            ))
            ->map(fn ($entries, string $transactionId): array => [
                'id' => $transactionId,
                'entries' => $entries->sortBy('kpi_indicator_name')->values(),
                'created_at' => $entries->max('created_at'),
            ])
            ->sortByDesc('created_at')
            ->values();

        return view('hr.kpi.undo', compact('employee', 'period', 'records', 'latestByIndicator', 'transactions'));
    }

    public function undoScore(Request $request, User $employee, KpiScoreHistoryService $history)
    {
        abort_unless(in_array($employee->role, ['employee', 'manager'], true), 404);
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'kpi_indicator_id' => ['required', 'integer', 'exists:kpi_indicators,id'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $history->undoScore(
            $employee,
            $validated['period'],
            (int) $validated['kpi_indicator_id'],
            $request->user(),
            $validated['reason'],
        );

        return redirect()
            ->route('hr.kpi.undo', [$employee, 'period' => $validated['period']])
            ->with('success', 'The selected KPI score was undone. KPI totals are recalculated automatically.');
    }

    public function undoSubmission(Request $request, User $employee, KpiScoreHistoryService $history)
    {
        abort_unless(in_array($employee->role, ['employee', 'manager'], true), 404);
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'transaction_id' => ['required', 'uuid'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $count = $history->undoSubmission(
            $employee,
            $validated['period'],
            $validated['transaction_id'],
            $request->user(),
            $validated['reason'],
        );

        return redirect()
            ->route('hr.kpi.undo', [$employee, 'period' => $validated['period']])
            ->with('success', "{$count} KPI score(s) from that submission were undone. KPI totals are recalculated automatically.");
    }

    public function undoAllScores(Request $request, User $employee, KpiScoreHistoryService $history)
    {
        abort_unless(in_array($employee->role, ['employee', 'manager'], true), 404);
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $count = $history->undoAllCurrentScores(
            $employee,
            $validated['period'],
            $request->user(),
            $validated['reason'],
        );

        return redirect()
            ->route('hr.kpi.undo', [$employee, 'period' => $validated['period']])
            ->with('success', "{$count} current KPI score(s) were undone. KPI totals are recalculated automatically.");
    }

    public function create(User $employee)
    {
        $indicators = KpiIndicator::whereIn('name', $this->manualIndicatorNames())->get();

        return view('hr.kpi.create', compact('employee', 'indicators'));
    }

    public function deleteScores(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'date_format:Y-m'],
        ]);
        $period = $validated['period'] ?? now()->format('Y-m');
        $employees = User::whereIn('role', ['employee', 'manager'])
            ->with('department')
            ->orderBy('name')
            ->get();

        return view('hr.kpi.delete', compact('employees', 'period'));
    }

    public function destroyScores(Request $request, KpiScoreHistoryService $history)
    {
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $employeeIds = array_map('intval', $validated['employee_ids']);
        $eligibleEmployeeIds = User::whereIn('role', ['employee', 'manager'])
            ->whereIn('id', $employeeIds)
            ->pluck('id');

        abort_if(
            $eligibleEmployeeIds->count() !== count($employeeIds),
            422,
            'Only employees and managers can have KPI scores deleted.'
        );

        $employees = User::whereIn('id', $eligibleEmployeeIds)->get();
        $deletedCount = DB::transaction(function () use ($employees, $validated, $history): int {
            return $employees->sum(fn (User $employee): int => $history->deleteForEmployeePeriod(
                $employee,
                $validated['period'],
                auth()->user(),
                $validated['reason'],
            ));
        });

        return redirect()
            ->route('hr.kpi.index', ['period' => $validated['period']])
            ->with('success', "{$deletedCount} KPI score record(s) deleted for the selected review period.");
    }

    public function deleteSavedEntry(Request $request, User $employee)
    {
        abort_unless(in_array($employee->role, ['employee', 'manager'], true), 404);
        $validated = $request->validate([
            'period' => ['nullable', 'date_format:Y-m'],
        ]);
        $period = $validated['period'] ?? now()->format('Y-m');
        $records = KpiRecord::with('kpiIndicator')
            ->where('employee_id', $employee->id)
            ->where('period', $period)
            ->orderBy('kpi_indicator_id')
            ->get();
        $scoreSummary = $records->map(function (KpiRecord $record): string {
            $indicator = $record->kpiIndicator?->name ?? 'Unknown KPI';
            $workload = $record->workload_target !== null
                ? " ({$record->workload_completed} completed / {$record->workload_target} target)"
                : '';

            return "{$indicator}: ".number_format((float) $record->score, 2).$workload;
        })->implode('; ');
        $entryConfirmation = "Delete the saved KPI entry for {$employee->name} for {$period}? Affected scores: ".($scoreSummary ?: 'none').'.';

        return view('hr.kpi.delete-entry', compact('employee', 'period', 'records', 'entryConfirmation'));
    }

    public function destroySavedEntry(Request $request, User $employee, KpiScoreHistoryService $history)
    {
        abort_unless(in_array($employee->role, ['employee', 'manager'], true), 404);
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $deletedCount = $history->deleteSavedEntry(
            $employee,
            $validated['period'],
            $request->user(),
            $validated['reason'],
        );

        return redirect()
            ->route('hr.kpi.saved-entry.delete', [$employee, 'period' => $validated['period']])
            ->with('success', "{$deletedCount} KPI score(s) deleted for {$employee->name}.");
    }

    public function destroySavedScore(Request $request, User $employee, KpiScoreHistoryService $history)
    {
        abort_unless(in_array($employee->role, ['employee', 'manager'], true), 404);
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'kpi_indicator_id' => ['required', 'integer', 'exists:kpi_indicators,id'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $history->deleteSavedCriterion(
            $employee,
            $validated['period'],
            (int) $validated['kpi_indicator_id'],
            $request->user(),
            $validated['reason'],
        );

        return redirect()
            ->route('hr.kpi.saved-entry.delete', [$employee, 'period' => $validated['period']])
            ->with('success', 'The selected KPI score was deleted.');
    }

    public function edit(Request $request, User $employee)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'date_format:Y-m'],
        ]);
        $period = $validated['period'] ?? now()->format('Y-m');
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

    public function storeBulk(Request $request, KpiScoreHistoryService $history)
    {
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'kpi_indicator_id' => ['required', 'integer', 'exists:kpi_indicators,id'],
            'remarks' => ['nullable', 'string'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $indicator = KpiIndicator::whereIn('name', $this->manualIndicatorNames())
            ->find($validated['kpi_indicator_id']);
        abort_if(! $indicator, 422, 'One or more selected KPIs are invalid.');

        $workload = [];
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

        $employees = User::whereIn('role', ['employee', 'manager'])
            ->whereIn('id', $validated['employee_ids'])
            // Enforce department boundaries on the server, not just through the employee picker UI.
            ->when(auth()->user()->role === 'manager', fn ($query) => $query->where('department_id', auth()->user()->department_id))
            ->get()
            ->keyBy('id');

        abort_if($employees->count() !== count($validated['employee_ids']), 422, 'One or more selected users cannot receive KPI scores.');

        // A shared ID groups all scores in this bulk submission for audit and undo history.
        $transactionId = (string) Str::uuid();
        DB::transaction(function () use ($validated, $employees, $indicator, $workload, $history, $transactionId): void {
            foreach ($employees as $employee) {
                $recordValues = $indicator->name === 'Productivity'
                    ? [
                        'score' => KpiRecord::productivityScore(
                            (int) $workload['workload_target'][$employee->id],
                            (int) $workload['workload_completed'][$employee->id]
                        ),
                        'workload_target' => $workload['workload_target'][$employee->id],
                        'workload_completed' => $workload['workload_completed'][$employee->id],
                        'remarks' => $validated['remarks'] ?? null,
                    ]
                    : [
                        'score' => $validated['score'],
                        'remarks' => $validated['remarks'] ?? null,
                    ];

                $history->save(
                    $employee,
                    $indicator,
                    $validated['period'],
                    $recordValues,
                    auth()->user(),
                    $transactionId,
                    $validated['reason'] ?? null,
                );
            }
        });

        $savedMessage = $indicator->name === 'Productivity'
            ? "{$employees->count()} productivity workload record(s) saved."
            : "{$employees->count()} KPI score(s) saved for {$indicator->name}.";

        return redirect()->back()->with('success', $savedMessage);
    }

    public function store(Request $request, User $employee, KpiScoreHistoryService $history)
    {
        abort_unless(in_array($employee->role, ['employee', 'manager'], true), 404);
        $manualIndicators = KpiIndicator::whereIn('name', $this->manualIndicatorNames())->get();
        $productivityIndicator = $manualIndicators->firstWhere('name', 'Productivity');
        $scoredIndicators = $manualIndicators->reject(fn ($indicator) => $indicator->name === 'Productivity');

        $rules = [
            'period' => ['required', 'date_format:Y-m'],
            'scores' => ['required', 'array'],
            'remarks' => ['nullable', 'string'],
            'reason' => ['nullable', 'string', 'max:1000'],
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

        $transactionId = (string) Str::uuid();
        DB::transaction(function () use ($validated, $employee, $scoredIndicators, $productivityIndicator, $history, $transactionId): void {
            foreach ($validated['scores'] as $indicatorId => $score) {
                $indicator = $scoredIndicators->firstWhere('id', (int) $indicatorId);
                $history->save(
                    $employee,
                    $indicator,
                    $validated['period'],
                    [
                        'score' => $score,
                        'remarks' => $validated['remarks'] ?? null,
                    ],
                    auth()->user(),
                    $transactionId,
                    $validated['reason'] ?? null,
                );
            }

            if ($productivityIndicator) {
                $target = (int) $validated['workload_target'];
                $completed = (int) $validated['workload_completed'];

                $history->save(
                    $employee,
                    $productivityIndicator,
                    $validated['period'],
                    [
                        'score' => KpiRecord::productivityScore($target, $completed),
                        'workload_target' => $target,
                        'workload_completed' => $completed,
                        'remarks' => $validated['remarks'] ?? null,
                    ],
                    auth()->user(),
                    $transactionId,
                    $validated['reason'] ?? null,
                );
            }
        });

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
