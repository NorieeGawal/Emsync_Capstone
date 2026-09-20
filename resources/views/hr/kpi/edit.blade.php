<x-layouts.dashboard title="Edit KPI Scores">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-slate-500">KPI Management</p>
            <h3 class="mt-1 text-[1.35rem] font-bold tracking-[-0.03em] text-slate-800">Edit KPI Scores</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $employee->name }} · {{ $employee->department->name ?? 'No department' }}</p>
        </div>
        <a href="{{ route('hr.kpi.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-800">Back to KPI management</a>
    </div>

    <div class="grid max-w-[64rem] grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_15rem]">
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
                            <div class="grid grid-cols-[minmax(0,1fr)_8rem] items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                                <label for="score-{{ $indicator->id }}" class="text-sm font-semibold text-slate-700">{{ $indicator->name }}</label>
                                <div>
                                    <input id="score-{{ $indicator->id }}" type="number" step="0.01" min="0" max="100" name="scores[{{ $indicator->id }}]" value="{{ old('scores.' . $indicator->id, $records->get($indicator->id)?->score) }}" placeholder="0-100" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none transition focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" required>
                                    @error('scores.' . $indicator->id) <p class="mt-1 text-right text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
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
