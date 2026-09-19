<x-layouts.dashboard title="Bulk KPI Entry">
    @php
        $bulkRoute = auth()->user()->role === 'manager' ? 'manager.kpi.bulk' : 'hr.kpi.bulk';
        $bulkStoreRoute = auth()->user()->role === 'manager' ? 'manager.kpi.bulk.store' : 'hr.kpi.bulk.store';
    @endphp

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="mt-1 text-2xl font-bold tracking-[-0.03em] text-slate-800">Bulk KPI Entry</h3>
            <p class="mt-1 text-sm text-slate-500">Apply one score to multiple employees in a single review cycle.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(0,1.7fr)_minmax(190px,0.52fr)] xl:items-start">
        <div class="space-y-3">
            <form method="GET" action="{{ route($bulkRoute) }}" class="rounded-[20px] border border-slate-200 bg-white p-4 shadow-[0_10px_22px_rgba(15,23,42,0.05)]">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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
                    <div class="flex items-end gap-3">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#111111] px-4 py-2.5 text-sm font-bold text-white shadow-[0_7px_15px_rgba(17,17,17,0.18)] transition hover:bg-[#232a30]">Filter</button>
                        <a href="{{ route($bulkRoute) }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100">Clear</a>
                    </div>
                </div>
            </form>

            <form method="POST" action="{{ route($bulkStoreRoute) }}" x-data="{ selected: [] }" class="rounded-[20px] border border-slate-200 bg-white p-4 shadow-[0_10px_22px_rgba(15,23,42,0.05)]">
                @csrf
                <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Period</label>
                        <input type="month" name="period" value="{{ old('period', now()->format('Y-m')) }}" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]" required>
                        @error('period') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">KPI</label>
                        <select name="kpi_indicator_id" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]" required>
                            <option value="">Select KPI</option>
                            @foreach($indicators as $indicator)
                                <option value="{{ $indicator->id }}" {{ (string) old('kpi_indicator_id') === (string) $indicator->id ? 'selected' : '' }}>{{ $indicator->name }}</option>
                            @endforeach
                        </select>
                        @error('kpi_indicator_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Score (0–100)</label>
                        <input type="number" name="score" value="{{ old('score') }}" min="0" max="100" step="0.01" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]" required>
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

                <div class="mt-5 overflow-hidden rounded-[18px] border border-slate-200 bg-white">
                    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" @change="selected = $event.target.checked ? {{ $employees->pluck('id')->values()->toJson() }} : []" class="rounded border-slate-300 text-[#111111] focus:ring-[#f4c212]">
                            Select all
                        </label>
                        <span class="text-[0.7rem] font-medium uppercase tracking-[0.12em] text-slate-500">{{ $employees->count() }} shown</span>
                    </div>
                    <div class="max-h-[calc(100vh-28rem)] overflow-y-auto">
                        <table class="w-full min-w-[620px] text-left text-sm">
                            <thead class="bg-slate-50 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">
                                <tr>
                                    <th class="w-12 px-4 py-3"></th>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Role</th>
                                    <th class="px-4 py-3">Department</th>
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-400">No employees match your filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($employees->count())
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#111111] px-5 py-2.5 text-sm font-bold text-white shadow-[0_7px_15px_rgba(17,17,17,0.18)] transition hover:bg-[#232a30]">Save KPI Score</button>
                    </div>
                @endif
            </form>
        </div>

        <aside class="rounded-[20px] border border-slate-200 bg-[#f8f7f5] p-3 shadow-[0_10px_22px_rgba(15,23,42,0.04)]">
            <div class="mb-3">
                <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Setup Guide</p>
            </div>

            <div class="space-y-3">
                <div class="rounded-2xl bg-white p-3 shadow-sm">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Before saving</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">
                        Choose the correct KPI, review period, and employee set before applying the final score.
                    </p>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-3">
                    <p class="mb-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Note</p>
                    <p class="text-sm leading-5 text-slate-600">
                        Bulk updates are best when the same performance standard is being applied to a consistent group.
                    </p>
                </div>

                <div class="rounded-2xl bg-[#111111] p-3 text-white shadow-sm">
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
