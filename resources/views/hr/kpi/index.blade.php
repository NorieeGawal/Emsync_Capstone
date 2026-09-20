<x-layouts.dashboard title="KPI Management">
    <div x-data="{ modalEmployee: null }" @keydown.escape.window="modalEmployee = null">
    <div class="mb-6">
        <div>
            <p class="mt-1 text-sm text-slate-600">Select an employee or manager to encode individual scores, or use bulk entry.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('hr.kpi.index') }}" class="mb-5 flex flex-wrap items-center gap-3">
        <div class="relative max-w-sm">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name..." class="w-full rounded-xl border-slate-300 bg-slate-50 py-2 pl-4 pr-10 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">
            <button type="submit" aria-label="Search employees" class="absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-xs text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                &#128269;
            </button>
        </div>
        <a href="{{ route('hr.kpi.bulk') }}" class="inline-flex items-center justify-center rounded-xl bg-[#111111] px-4 py-2.5 text-sm font-bold text-white shadow-[0_7px_15px_rgba(17,17,17,0.18)] transition hover:-translate-y-0.5 hover:bg-[#232a30] sm:ml-auto">
            Bulk Entry
        </a>
        @if($search)
            <a href="{{ route('hr.kpi.index') }}" class="text-sm font-medium text-slate-500 hover:text-red-600">Clear</a>
        @endif
    </form>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(0,1.7fr)_minmax(190px,0.52fr)] xl:items-start">
        <div class="max-h-[calc(100vh-18rem)] overflow-hidden rounded-[20px] border border-slate-200 bg-white shadow-[0_10px_22px_rgba(15,23,42,0.05)]">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Department</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $employee)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-800">{{ $employee->name }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-[0.68rem] font-medium
                                    @if($employee->role === 'manager') bg-[#f4c212]/20 text-slate-800
                                    @elseif($employee->role === 'hr') bg-slate-200 text-slate-700
                                    @else bg-slate-100 text-slate-700 @endif">
                                    {{ ucfirst($employee->role) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $employee->department->name ?? '—' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('hr.kpi.create', $employee) }}" class="inline-flex items-center rounded-lg bg-[#111111] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-700">Encode</a>
                                    <button type="button" @click="modalEmployee = {{ $employee->id }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-[#f4c212] hover:bg-[#fff9df]">Edit KPI</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">No employees or managers yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <aside class="rounded-[20px] border border-slate-200 bg-[#f8f7f5] p-3 shadow-[0_10px_22px_rgba(15,23,42,0.04)]">
            <div class="mb-3">
                <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Setup Guide</p>
            </div>

            <div class="space-y-3">
                <div class="rounded-2xl bg-white p-3 shadow-sm">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Before scoring</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">
                        Confirm the department, role, and review period before encoding performance values.
                    </p>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-3">
                    <p class="mb-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Best practice</p>
                    <p class="text-sm leading-5 text-slate-600">
                        Keep monthly KPIs consistent so reviews remain comparable across teams and departments.
                    </p>
                </div>

                <div class="rounded-2xl bg-[#111111] p-3 text-white shadow-sm">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-300">Checklist</p>
                    <ul class="mt-2 space-y-2 text-sm text-slate-200">
                        <li>• Correct employee selected</li>
                        <li>• Review period verified</li>
                        <li>• Score entered before save</li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>

    @foreach($employees as $employee)
        @php($employeeRecords = $records->get($employee->id, collect())->keyBy('kpi_indicator_id'))
        <div x-cloak x-show="modalEmployee === {{ $employee->id }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/45 p-4" @click.self="modalEmployee = null">
            <div x-show="modalEmployee === {{ $employee->id }}" x-transition:enter="transition ease-out duration-250" x-transition:enter-start="translate-y-2 scale-[0.98] opacity-0 shadow-[0_4px_16px_rgba(15,23,42,0.08)]" x-transition:enter-end="translate-y-0 scale-100 opacity-100 shadow-[0_24px_70px_rgba(15,23,42,0.28)]" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0 scale-100 opacity-100 shadow-[0_24px_70px_rgba(15,23,42,0.28)]" x-transition:leave-end="translate-y-2 scale-[0.98] opacity-0 shadow-[0_4px_16px_rgba(15,23,42,0.08)]" class="max-h-[min(90vh,44rem)] w-full max-w-xl overflow-y-auto rounded-2xl border border-slate-200 bg-[#f4f1ec] shadow-[0_24px_70px_rgba(15,23,42,0.28)]">
                <div class="flex items-start justify-between border-b border-slate-300 px-5 py-4">
                    <div>
                        <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-slate-500">Edit Individual KPI</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-800">{{ $employee->name }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $employee->department->name ?? 'No department' }}</p>
                    </div>
                    <button type="button" @click="modalEmployee = null" aria-label="Close edit KPI form" class="flex h-8 w-8 items-center justify-center rounded-full text-xl text-slate-500 transition hover:bg-white hover:text-slate-800">&times;</button>
                </div>

                <form action="{{ route('hr.kpi.store', $employee) }}" method="POST" class="p-5">
                    @csrf
                    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_10rem] sm:items-end">
                        <div>
                            <p class="text-sm leading-5 text-slate-600">Edit the existing scores for this employee. Saving updates the selected review period.</p>
                        </div>
                        <div>
                            <label for="period-{{ $employee->id }}" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Period</label>
                            <input id="period-{{ $employee->id }}" type="month" name="period" value="{{ $period }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" required>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($indicators as $indicator)
                            <div class="grid grid-cols-[minmax(0,1fr)_8rem] items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                                <label for="modal-score-{{ $employee->id }}-{{ $indicator->id }}" class="text-sm font-semibold text-slate-700">{{ $indicator->name }}</label>
                                <input id="modal-score-{{ $employee->id }}-{{ $indicator->id }}" type="number" step="0.01" min="0" max="100" name="scores[{{ $indicator->id }}]" value="{{ $employeeRecords->get($indicator->id)?->score }}" placeholder="0-100" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" required>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <label for="modal-remarks-{{ $employee->id }}" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Remarks</label>
                        <textarea id="modal-remarks-{{ $employee->id }}" name="remarks" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25">{{ $employeeRecords->first()?->remarks }}</textarea>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" @click="modalEmployee = null" class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-white">Cancel</button>
                        <button type="submit" class="rounded-xl bg-[#0d3b66] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0b2e52]">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-layouts.dashboard>
