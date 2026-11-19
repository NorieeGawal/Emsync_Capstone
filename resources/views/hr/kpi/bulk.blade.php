<x-layouts.dashboard title="HR Bulk KPI Entry">
    @php
        $bulkRoute = auth()->user()->role === 'manager' ? 'manager.kpi.bulk' : 'hr.kpi.bulk';
        $bulkStoreRoute = auth()->user()->role === 'manager' ? 'manager.kpi.bulk.store' : 'hr.kpi.bulk.store';
    @endphp

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-3 xl:min-h-0 xl:flex-1 xl:grid-cols-[minmax(0,1.7fr)_minmax(190px,0.52fr)] xl:items-stretch">
        <div class="space-y-3 xl:flex xl:h-full xl:min-h-0 xl:flex-col">
            <form method="GET" action="{{ route($bulkRoute) }}" class="rounded-[20px] border border-slate-200 bg-white p-4 shadow-[0_10px_22px_rgba(15,23,42,0.05)]">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                    <div>
                        <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Department</label>
                        <select name="department" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ (string) $departmentId === (string) $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Search</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search employee" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">
                    </div>
                    <div class="flex flex-wrap items-end gap-3">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#111111] px-4 py-2.5 text-sm font-bold text-white shadow-[0_7px_15px_rgba(17,17,17,0.18)] transition hover:bg-[#232a30]">Filter</button>
                        <a href="{{ route($bulkRoute) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100">Clear</a>
                        @if(auth()->user()->role === 'hr')
                            <a href="{{ route('hr.kpi.index') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-[#f4c212] hover:bg-[#fff9df]">Edit individual KPI</a>
                        @endif
                    </div>
                </div>
            </form>

            @php($selectedIndicatorName = $indicators->firstWhere('id', (int) old('kpi_indicator_id'))?->name ?? '')
            <form method="POST" action="{{ route($bulkStoreRoute) }}" x-data="{ selected: [], employeeIds: @js($employees->pluck('id')->map(fn ($id) => (string) $id)->values()->all()), selectedIndicator: @js($selectedIndicatorName) }" class="rounded-[20px] border border-slate-200 bg-white p-4 shadow-[0_10px_22px_rgba(15,23,42,0.05)] xl:flex xl:min-h-0 xl:flex-1 xl:flex-col">
                @csrf
                <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Period</label>
                        <input type="month" name="period" value="{{ old('period', now()->format('Y-m')) }}" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]" required>
                        @error('period') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">KPI</label>
                        <select name="kpi_indicator_id" x-on:change="selectedIndicator = $event.target.options[$event.target.selectedIndex].dataset.indicatorName" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]" required>
                            <option value="">Select KPI</option>
                            @foreach($indicators as $indicator)
                                <option value="{{ $indicator->id }}" data-indicator-name="{{ $indicator->name }}" {{ (string) old('kpi_indicator_id') === (string) $indicator->id ? 'selected' : '' }}>{{ $indicator->name }}</option>
                            @endforeach
                        </select>
                        @error('kpi_indicator_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div x-cloak x-show="selectedIndicator !== 'Productivity'">
                        <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Score (0–100)</label>
                        <input type="number" name="score" value="{{ old('score') }}" min="0" max="100" step="0.01" x-bind:required="selectedIndicator !== 'Productivity'" x-bind:disabled="selectedIndicator === 'Productivity'" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">
                        @error('score') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Remarks (optional)</label>
                    <textarea name="remarks" rows="2" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">{{ old('remarks') }}</textarea>
                </div>

                @error('employee_ids')
                    <p class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>
                @enderror

                <div class="mt-5 overflow-hidden rounded-[18px] border border-slate-200 bg-white xl:flex xl:min-h-0 xl:flex-1 xl:flex-col">
                    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" @change="selected = $event.target.checked ? employeeIds : []" class="rounded border-slate-300 text-[#111111] focus:ring-[#f4c212]">
                            Select all
                        </label>
                        <span class="text-[0.7rem] font-medium uppercase tracking-[0.12em] text-slate-500">{{ $employees->count() }} shown</span>
                    </div>
                    <div class="overflow-y-auto xl:min-h-0 xl:flex-1">
                        <table class="w-full min-w-[620px] text-left text-sm">
                            <thead class="bg-slate-50 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">
                                <tr>
                                    <th class="w-12 px-4 py-3"></th>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Role</th>
                                    <th class="px-4 py-3">Department</th>
                                    <th x-cloak x-show="selectedIndicator === 'Productivity'" class="px-4 py-3">Target workload</th>
                                    <th x-cloak x-show="selectedIndicator === 'Productivity'" class="px-4 py-3">Completed workload</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($employees as $employee)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" x-model="selected" class="rounded border-slate-300 text-[#111111] focus:ring-[#f4c212]">
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $employee->name }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ ucfirst($employee->role) }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $employee->department->name ?? '—' }}</td>
                                        <td x-cloak x-show="selectedIndicator === 'Productivity'" class="px-4 py-3">
                                            <input type="number" name="workload_target[{{ $employee->id }}]" min="1" max="2147483647" step="1" value="{{ old('workload_target.' . $employee->id) }}" x-bind:required="selectedIndicator === 'Productivity' && selected.includes('{{ $employee->id }}')" x-bind:disabled="selectedIndicator !== 'Productivity' || !selected.includes('{{ $employee->id }}')" aria-label="Target workload for {{ $employee->name }}" class="w-28 rounded-lg border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">
                                            @error('workload_target.' . $employee->id) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td x-cloak x-show="selectedIndicator === 'Productivity'" class="px-4 py-3">
                                            <input type="number" name="workload_completed[{{ $employee->id }}]" min="0" max="2147483647" step="1" value="{{ old('workload_completed.' . $employee->id) }}" x-bind:required="selectedIndicator === 'Productivity' && selected.includes('{{ $employee->id }}')" x-bind:disabled="selectedIndicator !== 'Productivity' || !selected.includes('{{ $employee->id }}')" aria-label="Completed workload for {{ $employee->name }}" class="w-28 rounded-lg border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">
                                            @error('workload_completed.' . $employee->id) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td x-bind:colspan="selectedIndicator === 'Productivity' ? 6 : 4" class="px-4 py-8 text-center text-sm text-slate-400">No employees match your filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($employees->count())
                    <div class="mt-6 flex justify-end xl:mt-3">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#111111] px-5 py-2.5 text-sm font-bold text-white shadow-[0_7px_15px_rgba(17,17,17,0.18)] transition hover:bg-[#232a30]">Save KPI Score</button>
                    </div>
                @endif
            </form>
        </div>

        <aside class="rounded-[20px] border border-slate-200 bg-[#f8f7f5] p-3 shadow-[0_10px_22px_rgba(15,23,42,0.04)] xl:flex xl:h-full xl:flex-col">
            <div class="mb-3 xl:shrink-0">
                <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Setup Guide</p>
            </div>

            <div class="space-y-3 xl:flex xl:min-h-0 xl:flex-1 xl:flex-col">
                <div class="rounded-2xl bg-white p-3 shadow-sm xl:flex-1">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Before saving</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">
                        Choose the correct KPI, review period, and employee set before applying the final score.
                    </p>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-3 xl:flex-1">
                    <p class="mb-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Note</p>
                    <p class="text-sm leading-5 text-slate-600">
                        For Productivity, enter a separate target and completed count for each selected employee.
                    </p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">
                        Bulk updates are best when the same performance standard is being applied to a consistent group.
                    </p>
                </div>

                <div class="rounded-2xl bg-[#111111] p-3 text-white shadow-sm xl:flex-1">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-300">Checklist</p>
                    <ul class="mt-2 space-y-2 text-sm text-slate-200">
                        <li>• KPI selected</li>
                        <li>• Period confirmed</li>
                        <li>• Employees marked</li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.dashboard>
