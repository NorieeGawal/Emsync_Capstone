<x-layouts.dashboard title="Delete KPI">
    <div class="mx-auto max-w-5xl">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-slate-500">KPI Management</p>
                <h2 class="mt-1 text-xl font-bold text-slate-800">Delete KPI</h2>
                <p class="mt-1 text-sm text-slate-600">{{ $employee->name }} · {{ ucfirst($employee->role) }}</p>
            </div>
            <a href="{{ route('hr.kpi.index', ['period' => $period]) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back to KPI management</a>
        </div>

        @if(session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('hr.kpi.saved-entry.delete', $employee) }}" class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4">
            <div>
                <label for="delete-entry-period" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Review period</label>
                <input id="delete-entry-period" type="month" name="period" value="{{ $period }}" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">View period</button>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-red-100 bg-red-50 px-5 py-4">
                <h3 class="font-bold text-red-900">Review period: {{ $period }}</h3>
                <p class="mt-1 text-sm text-red-800">Delete only scores belonging to this employee and period. Deletions are recorded in KPI history.</p>
            </div>

            @if($records->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-slate-500">No saved KPI scores exist for this employee and period.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">KPI criterion</th>
                                <th class="px-4 py-3">Saved score</th>
                                <th class="px-4 py-3">Delete one criterion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($records as $record)
                                @php
                                    $criterionName = $record->kpiIndicator?->name ?? 'Unknown KPI';
                                    $workloadDescription = $record->workload_target !== null
                                        ? " ({$record->workload_completed} completed / {$record->workload_target} target)"
                                        : '';
                                    $criterionConfirmation = "Delete {$criterionName} score for {$employee->name} for {$period}? Affected score: ".number_format((float) $record->score, 2).$workloadDescription.'.';
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $criterionName }}</td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ number_format((float) $record->score, 2) }}
                                        @if($record->workload_target !== null)
                                            <span class="block text-xs text-slate-500">{{ $record->workload_completed }} completed / {{ $record->workload_target }} target</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <form method="POST" action="{{ route('hr.kpi.saved-score.destroy', $employee) }}" onsubmit="return confirm(this.dataset.confirmMessage);" data-confirm-message="{{ $criterionConfirmation }}" class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="period" value="{{ $period }}">
                                            <input type="hidden" name="kpi_indicator_id" value="{{ $record->kpi_indicator_id }}">
                                            <label for="criterion-reason-{{ $record->id }}" class="sr-only">Reason for deleting {{ $criterionName }}</label>
                                            <input id="criterion-reason-{{ $record->id }}" type="text" name="reason" maxlength="1000" required placeholder="Deletion reason" class="w-40 rounded-lg border border-slate-300 px-2.5 py-2 text-xs">
                                            <button type="submit" class="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-xs font-semibold text-red-800 hover:bg-red-100">Delete score</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route('hr.kpi.saved-entry.destroy', $employee) }}" onsubmit="return confirm(this.dataset.confirmMessage);" data-confirm-message="{{ $entryConfirmation }}" class="border-t border-slate-200 bg-slate-50 p-4">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period }}">
                    <label for="entry-delete-reason" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-600">Reason for deleting the entire saved KPI entry</label>
                    <div class="flex flex-wrap items-center gap-3">
                        <input id="entry-delete-reason" type="text" name="reason" maxlength="1000" required placeholder="Enter deletion reason" class="min-w-[16rem] flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm">
                        <button type="submit" class="rounded-lg bg-red-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-800">Delete entire KPI entry</button>
                    </div>
                </form>
            @endif
        </section>

        <div class="mt-5 flex justify-end">
            <a href="{{ route('hr.kpi.create', $employee) }}" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Encode corrected KPI scores</a>
        </div>
    </div>
</x-layouts.dashboard>
