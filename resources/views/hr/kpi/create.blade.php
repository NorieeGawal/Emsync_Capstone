<x-layouts.dashboard title="HR Encode KPI Scores">
    <div class="mb-6 flex items-start justify-between gap-3">
        <div>
            <h3 class="text-[1.05rem] font-bold tracking-[-0.03em] text-slate-800">Encode KPI Scores</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $employee->name }} {{ $employee->department->name ?? 'No department' }}</p>
        </div>
        <a href="{{ route('hr.kpi.index') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-[#f4c212] hover:bg-[#fff9df] hover:text-slate-900 hover:shadow-md">
            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
            </svg>
            <span>Back to KPI management</span>
        </a>
    </div>

    <div class="mx-auto max-w-[52rem] rounded-[14px] border border-[#d9d4cd] bg-[#f4f1ec] p-0 shadow-[0_2px_0_rgba(17,17,17,0.02)]">
        <div class="border-b border-slate-300 bg-[#f4f1ec] px-4 py-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-[#f4c212]"></span>
                    <span class="text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-slate-600">Performance Input</span>
                </div>
            </div>
        </div>

        <div class="bg-[#f4f1ec] p-4 sm:p-5">
            <form action="{{ route('hr.kpi.store', $employee) }}" method="POST" class="w-full">
                @csrf

                <div class="mb-5">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Period</label>
                    <input type="month" name="period" value="{{ old('period', now()->format('Y-m')) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-base text-slate-800 shadow-sm outline-none transition focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" required>
                    @error('period') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-4">
                    @foreach($indicators as $indicator)
                        @if($indicator->name === 'Productivity')
                            <div class="min-w-0 rounded-xl border border-slate-200 bg-white px-4 py-3.5">
                                <h4 class="mb-2 text-sm font-semibold text-slate-700">Productivity</h4>
                                <div class="mb-3 rounded-lg bg-slate-50 px-3 py-2 text-xs leading-5 text-slate-600">
                                    <span class="font-semibold text-slate-700">Basis:</span>
                                    Completed workload ÷ Target workload × 100, capped at 100%. Example: 18 ÷ 20 × 100 = 90%.
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label for="workload-target" class="mb-1.5 block text-sm font-medium text-slate-700">Target workload</label>
                                        <input id="workload-target" type="number" min="1" step="1" name="workload_target" value="{{ old('workload_target') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-base text-slate-800 shadow-sm outline-none transition focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" required>
                                        @error('workload_target') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="workload-completed" class="mb-1.5 block text-sm font-medium text-slate-700">Completed workload</label>
                                        <input id="workload-completed" type="number" min="0" step="1" name="workload_completed" value="{{ old('workload_completed') }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-base text-slate-800 shadow-sm outline-none transition focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" required>
                                        @error('workload_completed') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        @else
                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">{{ $indicator->name }} (0-100)</label>
                                <p class="mb-2 text-xs leading-5 text-slate-500">
                                    @if($indicator->name === 'Work Quality')
                                        Basis: Accuracy and correctness of completed work. Score 100 for error-free output and deduct for defects or rework.
                                    @elseif($indicator->name === 'Efficiency')
                                        Basis: Output achieved using the available time and resources. Score 100 for full, resource-efficient completion.
                                    @elseif($indicator->name === 'Timeliness')
                                        Basis: Completion and submission against deadlines. Score 100 for on-time delivery; deduct for delays.
                                    @else
                                        Basis: Enter the score based on the indicator's performance standard.
                                    @endif
                                </p>
                                <input type="number" step="0.01" min="0" max="100" name="scores[{{ $indicator->id }}]" value="{{ old('scores.' . $indicator->id) }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-base text-slate-800 shadow-sm outline-none transition focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" required>
                                @error('scores.' . $indicator->id) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="mt-5">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Remarks (optional)</label>
                    <textarea name="remarks" rows="3" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-base text-slate-800 shadow-sm outline-none transition focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25">{{ old('remarks') }}</textarea>
                </div>

                <div class="mt-6 flex items-center justify-between gap-3">
                    <button type="button" @click="$el.closest('form').querySelectorAll('input[name=&quot;workload_target&quot;], input[name=&quot;workload_completed&quot;], input[name^=&quot;scores[&quot;], textarea[name=&quot;remarks&quot;]').forEach(field => field.value = '')" class="rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:border-red-300 hover:bg-red-50">Clear All</button>
                    <div class="flex gap-3">
                        <button type="submit" class="rounded-xl bg-[#0d3b66] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_4px_10px_rgba(13,59,102,0.18)] transition hover:bg-[#0b2e52]">Save Scores</button>
                        <a href="{{ route('hr.kpi.index') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
