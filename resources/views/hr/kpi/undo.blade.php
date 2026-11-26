<x-layouts.dashboard title="Undo KPI Scores">
    <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-slate-500">KPI History</p>
            <h2 class="mt-1 text-xl font-bold text-slate-800">Undo KPI Scores</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $employee->name }} · {{ ucfirst($employee->role) }}</p>
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

    <form method="GET" action="{{ route('hr.kpi.undo', $employee) }}" class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4">
        <div>
            <label for="undo-period" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Review period</label>
            <input id="undo-period" type="month" name="period" value="{{ $period }}" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">View period</button>
    </form>

    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-800">Current scores</h3>
                <p class="mt-1 text-sm text-slate-500">Undoing a score restores its recorded previous value. Legacy scores without history will be removed and logged.</p>
            </div>
            @if($records->isNotEmpty())
                <form method="POST" action="{{ route('hr.kpi.undo-all', $employee) }}" onsubmit="return confirm('Undo every current KPI score for this employee and period?');" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period }}">
                    <div>
                        <label for="undo-all-reason" class="sr-only">Reason for undoing all current scores</label>
                        <input id="undo-all-reason" type="text" name="reason" maxlength="1000" required placeholder="Reason for undoing all" class="w-56 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <button type="submit" class="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800 hover:bg-red-100">Undo all current</button>
                </form>
            @endif
        </div>

        @if($records->isEmpty())
            <p class="rounded-xl bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">There are no current KPI scores for this period.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-3">KPI</th>
                            <th class="px-3 py-3">Current score</th>
                            <th class="px-3 py-3">Latest record</th>
                            <th class="px-3 py-3">Undo this score</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($records as $record)
                            @php($audit = $latestByIndicator->get($record->kpi_indicator_id))
                            <tr>
                                <td class="px-3 py-3 font-semibold text-slate-800">{{ $record->kpiIndicator?->name ?? 'Unknown KPI' }}</td>
                                <td class="px-3 py-3 text-slate-700">
                                    {{ number_format((float) $record->score, 2) }}
                                    @if($record->workload_target !== null)
                                        <span class="block text-xs text-slate-500">{{ $record->workload_completed }} completed / {{ $record->workload_target }} target</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-slate-600">
                                    @if($audit)
                                        <span class="block">{{ ucfirst($audit->action) }} by {{ $audit->actor_name }}</span>
                                        <span class="text-xs text-slate-500">{{ $audit->created_at->format('M j, Y g:i A') }} · {{ $audit->reason }}</span>
                                    @else
                                        <span class="text-xs text-amber-700">No saved history; legacy record</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <form method="POST" action="{{ route('hr.kpi.undo-score', $employee) }}" onsubmit="return confirm('Undo the current {{ $record->kpiIndicator?->name ?? 'KPI' }} score?');" class="flex flex-wrap items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="period" value="{{ $period }}">
                                        <input type="hidden" name="kpi_indicator_id" value="{{ $record->kpi_indicator_id }}">
                                        <label for="reason-{{ $record->id }}" class="sr-only">Undo reason</label>
                                        <input id="reason-{{ $record->id }}" type="text" name="reason" maxlength="1000" required placeholder="Reason" class="w-36 rounded-lg border border-slate-300 px-2.5 py-2 text-xs">
                                        <button type="submit" class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-900 hover:bg-amber-100">Undo score</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @php($deletedAudits = $latestByIndicator->filter(fn ($audit) => $audit->action === 'deleted' && ! $records->contains(fn ($record) => (int) $record->kpi_indicator_id === (int) $audit->kpi_indicator_id)))
        @if($deletedAudits->isNotEmpty())
            <div class="mt-5 border-t border-slate-200 pt-4">
                <h4 class="mb-3 text-sm font-bold text-slate-800">Deleted scores with saved history</h4>
                <div class="space-y-2">
                    @foreach($deletedAudits as $audit)
                        <form method="POST" action="{{ route('hr.kpi.undo-score', $employee) }}" onsubmit="return confirm('Restore the deleted {{ $audit->kpi_indicator_name }} score?');" class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-amber-50 p-3">
                            @csrf
                            <input type="hidden" name="period" value="{{ $period }}">
                            <input type="hidden" name="kpi_indicator_id" value="{{ $audit->kpi_indicator_id }}">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $audit->kpi_indicator_name }} · Deleted</p>
                                <p class="text-xs text-slate-600">Previous score: {{ $audit->original_value['score'] ?? '—' }} · {{ $audit->created_at->format('M j, Y g:i A') }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <label for="deleted-reason-{{ $audit->id }}" class="sr-only">Reason for restoring deleted score</label>
                                <input id="deleted-reason-{{ $audit->id }}" type="text" name="reason" maxlength="1000" required placeholder="Reason" class="w-36 rounded-lg border border-amber-200 bg-white px-2.5 py-2 text-xs">
                                <button type="submit" class="rounded-lg border border-amber-300 bg-white px-3 py-2 text-xs font-semibold text-amber-900 hover:bg-amber-100">Restore this score</button>
                            </div>
                        </form>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <h3 class="font-bold text-slate-800">Undo a full submission</h3>
        <p class="mb-4 mt-1 text-sm text-slate-500">Each submission is grouped by its original save action. Newer changes must be undone first.</p>

        @forelse($transactions as $transaction)
            <div class="mb-3 rounded-xl border border-slate-200 p-4 last:mb-0">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-slate-800">Submission · {{ $transaction['created_at']->format('M j, Y g:i A') }}</p>
                        <ul class="mt-2 space-y-1 text-sm text-slate-600">
                            @foreach($transaction['entries'] as $entry)
                                <li>
                                    {{ $entry->kpi_indicator_name }} — {{ ucfirst($entry->action) }} by {{ $entry->actor_name }}
                                    @if($entry->new_value)
                                        (score {{ $entry->new_value['score'] ?? '—' }})
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <form method="POST" action="{{ route('hr.kpi.undo-submission', $employee) }}" onsubmit="return confirm('Undo all scores in this submission?');" class="flex flex-wrap items-end gap-2">
                        @csrf
                        <input type="hidden" name="period" value="{{ $period }}">
                        <input type="hidden" name="transaction_id" value="{{ $transaction['id'] }}">
                        <div>
                            <label for="submission-reason-{{ $loop->index }}" class="sr-only">Reason for undoing this submission</label>
                            <input id="submission-reason-{{ $loop->index }}" type="text" name="reason" maxlength="1000" required placeholder="Reason" class="w-44 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <button type="submit" class="rounded-lg bg-amber-700 px-3 py-2 text-sm font-semibold text-white hover:bg-amber-800">Undo submission</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="rounded-xl bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No reversible submissions are recorded for this period.</p>
        @endforelse
    </section>
</x-layouts.dashboard>
