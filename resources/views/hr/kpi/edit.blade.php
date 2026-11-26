<x-layouts.dashboard title="HR Edit KPI Scores">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-slate-500">KPI Management</p>
            <h3 class="mt-1 text-[1.35rem] font-bold tracking-[-0.03em] text-slate-800">Edit KPI Scores</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $employee->name }} · {{ $employee->department->name ?? 'No department' }}</p>
        </div>
        <a href="{{ route('hr.kpi.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-800">Back to KPI management</a>
    </div>

    <div class="mx-auto grid w-full max-w-[64rem] grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_15rem]">
        <div class="rounded-[14px] border border-[#d9d4cd] bg-[#f4f1ec] shadow-[0_2px_0_rgba(17,17,17,0.02)]">
            <div class="flex flex-col gap-3 border-b border-slate-300 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-[#f4c212]"></span>
                    <span class="text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-slate-600">Performance Input</span>
                </div>
                <form action="{{ route('hr.kpi.edit', $employee) }}" method="GET" class="flex items-center gap-2">
                    <label for="period" class="text-xs font-semibold text-slate-600">Period</label>
                    <input id="period" type="month" name="period" value="{{ $period }}" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-800 shadow-sm outline-none focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" onchange="this.form.submit()">
                </form>
            </div>

            <div class="p-4 sm:p-5">
                @if(session('success'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
                @endif

                <form action="{{ route('hr.kpi.store', $employee) }}" method="POST" class="max-w-[38rem]">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period }}">

                    <div class="space-y-3">
                        @foreach($indicators as $indicator)
                            @if($indicator->name === 'Productivity')
                                <div class="min-w-0 rounded-xl border border-slate-200 bg-white px-3 py-3">
                                    <h4 class="mb-2 text-sm font-semibold text-slate-700">Productivity</h4>
                                    <div class="mb-3 rounded-lg bg-slate-50 px-3 py-2 text-xs leading-5 text-slate-600">
                                        <span class="font-semibold text-slate-700">Basis:</span>
                                        Completed workload ÷ Target workload × 100, capped at 100%.
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label for="workload-target" class="mb-1.5 block text-xs font-medium text-slate-600">Target workload</label>
                                            <input id="workload-target" type="number" min="1" step="1" name="workload_target" value="{{ old('workload_target', $records->get($indicator->id)?->workload_target) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" required>
                                            @error('workload_target') <p class="mt-1 text-right text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label for="workload-completed" class="mb-1.5 block text-xs font-medium text-slate-600">Completed workload</label>
                                            <input id="workload-completed" type="number" min="0" step="1" name="workload_completed" value="{{ old('workload_completed', $records->get($indicator->id)?->workload_completed) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" required>
                                            @error('workload_completed') <p class="mt-1 text-right text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-[minmax(0,1fr)_8rem] items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                                    <div>
                                        <label for="score-{{ $indicator->id }}" class="text-sm font-semibold text-slate-700">{{ $indicator->name }}</label>
                                        <p class="mt-1 text-xs leading-4 text-slate-500">
                                            @if($indicator->name === 'Work Quality')
                                                Basis: Accuracy and correctness of work; deduct for defects or rework.
                                            @elseif($indicator->name === 'Efficiency')
                                                Basis: Output achieved using available time and resources.
                                            @elseif($indicator->name === 'Timeliness')
                                                Basis: Completion and submission against deadlines.
                                            @else
                                                Basis: Enter the score based on the indicator's performance standard.
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <input id="score-{{ $indicator->id }}" type="number" step="0.01" min="0" max="100" name="scores[{{ $indicator->id }}]" value="{{ old('scores.' . $indicator->id, $records->get($indicator->id)?->score) }}" placeholder="0-100" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" required>
                                        @error('scores.' . $indicator->id) <p class="mt-1 text-right text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <label for="remarks" class="mb-2 block text-sm font-medium text-slate-700">Remarks (optional)</label>
                        <textarea id="remarks" name="remarks" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25">{{ old('remarks', $records->first()?->remarks) }}</textarea>
                    </div>

                    <div class="mt-5 flex gap-3">
                        <button type="submit" class="rounded-xl bg-[#0d3b66] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_4px_10px_rgba(13,59,102,0.18)] transition hover:bg-[#0b2e52]">Save Changes</button>
                        <a href="{{ route('hr.kpi.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <aside class="rounded-[14px] border border-slate-200 bg-[#f8f7f5] p-3 shadow-[0_10px_22px_rgba(15,23,42,0.04)]">
            <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Edit Guide</p>
            <div class="mt-3 space-y-3">
                <div class="rounded-xl bg-white p-3 shadow-sm">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Selected record</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">Scores for {{ \Carbon\Carbon::parse($period . '-01')->format('F Y') }} are loaded above.</p>
                </div>
                <div class="rounded-xl border border-dashed border-slate-300 bg-white/70 p-3">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Score range</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">Use a value from 0 to 100. Saving replaces the selected employee's existing scores for this period.</p>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.dashboard>
