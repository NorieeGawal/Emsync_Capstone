<x-layouts.dashboard title="Delete KPI Scores" header-subtitle="Select employees and managers to remove KPI scores for a single review period. Other periods will remain unchanged.">
    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mx-auto max-w-5xl overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-[0_18px_38px_rgba(15,23,42,0.05)]">
        <div class="border-b border-red-100 bg-red-50 px-5 py-4">
            <h2 class="text-lg font-bold text-red-800">Delete KPI Scores</h2>
            <p class="mt-1 text-sm leading-5 text-red-700">
                This removes every KPI score for the selected people in the chosen month. Scores from other months are preserved.
            </p>
        </div>

        <form method="POST" action="{{ route('hr.kpi.scores.destroy') }}" x-data="{ selected: [], period: @js($period), employeeIds: @js($employees->pluck('id')->map(fn ($id) => (string) $id)->values()->all()) }" onsubmit="return confirm('Delete all KPI scores for the selected people in this review period? Their previous values will be recorded in KPI history.');">
            @csrf
            <div class="flex flex-wrap items-end gap-3 border-b border-slate-200 p-4">
                <div class="w-full max-w-xs">
                    <label for="delete-kpi-period" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Review period</label>
                    <input id="delete-kpi-period" type="month" name="period" x-model="period" value="{{ old('period', $period) }}" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-700 shadow-sm focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20" required>
                    @error('period') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <p class="text-sm text-slate-500">{{ $employees->count() }} employees and managers listed</p>
            </div>

            <div class="px-4 pt-4">
                <label for="delete-reason" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Deletion reason</label>
                <input id="delete-reason" type="text" name="reason" value="{{ old('reason') }}" maxlength="1000" required class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-700 shadow-sm focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20">
                @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @error('employee_ids')
                <p class="mx-4 mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>
            @enderror

            @error('employee_ids.*')
                <p class="mx-4 mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>
            @enderror

            <div class="max-h-[min(60vh,36rem)] overflow-auto">
                <table class="w-full min-w-[800px] table-fixed text-left text-sm">
                    <colgroup>
                        <col class="w-[14%]">
                        <col class="w-[20%]">
                        <col class="w-[13%]">
                        <col class="w-[25%]">
                        <col class="w-[28%]">
                    </colgroup>
                    <thead class="sticky top-0 z-10 bg-[#f8f9fb] text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500 shadow-[inset_0_-1px_0_rgba(148,163,184,0.22)]">
                        <tr>
                            <th class="px-5 py-3.5 align-middle">
                                <label class="inline-flex items-center gap-2 whitespace-nowrap text-xs normal-case tracking-normal">
                                    <input type="checkbox" aria-label="Select all employees and managers" x-bind:checked="employeeIds.length > 0 && selected.length === employeeIds.length" @change="selected = $event.target.checked ? [...employeeIds] : []" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                    <span>Select all</span>
                                </label>
                            </th>
                            <th class="px-5 py-3.5 align-middle">Name</th>
                            <th class="px-5 py-3.5 align-middle">Role</th>
                            <th class="px-5 py-3.5 align-middle">Department</th>
                            <th class="px-5 py-3.5 align-middle">Delete individual entry</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($employees as $employee)
                            <tr class="group transition-colors hover:bg-[#fffdf3]">
                                <td class="px-5 py-3.5 align-middle">
                                    <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" x-model="selected" aria-label="Select {{ $employee->name }}" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                </td>
                                <td class="px-5 py-3.5 align-middle font-semibold text-slate-800">{{ $employee->name }}</td>
                                <td class="px-5 py-3.5 align-middle">
                                    <span class="inline-flex rounded-full px-2.5 py-1.5 text-[0.7rem] font-semibold ring-1 ring-inset ring-black/[0.04] {{ $employee->role === 'manager' ? 'bg-[#f4c212]/20 text-slate-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ ucfirst($employee->role) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 align-middle text-slate-600">{{ $employee->department->name ?? '—' }}</td>
                                <td class="px-5 py-3.5 align-middle">
                                    <div class="flex min-h-9 items-center justify-start">
                                        <a href="{{ route('hr.kpi.saved-entry.delete', [$employee, 'period' => $period]) }}" x-bind:href="'{{ route('hr.kpi.saved-entry.delete', $employee) }}?period=' + encodeURIComponent(period)" class="inline-flex h-9 items-center rounded-lg border border-red-300 bg-red-50 px-3.5 text-xs font-semibold text-red-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-red-100">Delete KPI</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">No employees or managers are available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4">
                <a href="{{ route('hr.kpi.index', ['period' => $period]) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                    Cancel
                </a>
                <button type="submit" x-bind:disabled="selected.length === 0" class="inline-flex items-center justify-center rounded-xl bg-red-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-50">
                    Delete Selected Scores (<span x-text="selected.length"></span>)
                </button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
