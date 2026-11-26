<x-layouts.dashboard title="HR KPI Management" header-subtitle="Select an employee or manager to encode individual scores, or use bulk entry.">
    <div x-data="{ modalEmployee: null }" @keydown.escape.window="modalEmployee = null">

    <form method="GET" action="{{ route('hr.kpi.index') }}" class="mb-6 flex flex-wrap items-center gap-3 rounded-[22px] border border-slate-200 bg-white/80 p-3 shadow-[0_12px_30px_rgba(15,23,42,0.04)] backdrop-blur-sm">
        <div class="relative w-full max-w-sm">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name..." class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 py-2 pl-4 pr-10 text-sm text-slate-700 shadow-sm transition focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20">
            <button type="submit" aria-label="Search employees" class="absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-xs text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                &#128269;
            </button>
        </div>

        <div class="relative min-w-[150px]">
            <label for="roleFilter" class="sr-only">Filter by role</label>
            <select id="roleFilter" name="role" class="h-11 w-full appearance-none rounded-xl border border-slate-300 bg-slate-50 px-3 pr-9 text-sm text-slate-700 shadow-sm transition focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20">
                <option value="">All roles</option>
                <option value="employee" {{ $role === 'employee' ? 'selected' : '' }}>Employee</option>
                <option value="manager" {{ $role === 'manager' ? 'selected' : '' }}>Manager</option>
            </select>
            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0l-4.25-4.51a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd"></path></svg>
            </span>
        </div>

        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#111111] px-4 py-2.5 text-sm font-bold text-white shadow-[0_10px_18px_rgba(17,17,17,0.14)] transition hover:-translate-y-0.5 hover:bg-[#232a30]">
            Apply
        </button>

        <a href="{{ route('hr.kpi.bulk') }}" class="ml-auto inline-flex items-center justify-center rounded-xl bg-[#111111] px-4 py-2.5 text-sm font-bold text-white shadow-[0_10px_18px_rgba(17,17,17,0.14)] transition hover:-translate-y-0.5 hover:bg-[#232a30]">
            Bulk Entry
        </a>
        @if($search || $role)
            <a href="{{ route('hr.kpi.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-red-200 hover:text-red-600">Clear</a>
        @endif
    </form>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(0,1fr)_250px] xl:items-stretch xl:h-[calc(100vh-13.5rem)]">
        <div class="flex h-full min-h-0 flex-col self-stretch overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-[0_18px_38px_rgba(15,23,42,0.05)]">
            <div class="scroll-surface flex-1 overflow-y-auto">
            <table class="w-full text-left text-sm">
                <thead class="sticky top-0 z-10 bg-[#f8f9fb] text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500 shadow-[inset_0_-1px_0_rgba(148,163,184,0.22)]">
                    <tr>
                        <th class="px-5 py-3.5">Name</th>
                        <th class="px-5 py-3.5">Role</th>
                        <th class="px-5 py-3.5">Department</th>
                        <th class="px-5 py-3.5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $employee)
                        <tr class="group transition-colors hover:bg-[#fffdf3]">
                            <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $employee->name }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-1.5 text-[0.7rem] font-semibold ring-1 ring-inset ring-black/[0.04]
                                    @if($employee->role === 'manager') bg-[#f4c212]/20 text-slate-800
                                    @elseif($employee->role === 'hr') bg-slate-200 text-slate-700
                                    @else bg-slate-100 text-slate-700 @endif">
                                    {{ ucfirst($employee->role) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $employee->department->name ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap items-center gap-2 opacity-90 transition-opacity group-hover:opacity-100">
                                    <a href="{{ route('hr.kpi.create', $employee) }}" class="inline-flex h-9 items-center rounded-lg bg-[#111111] px-3.5 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-700 hover:shadow-md">Encode</a>
                                    <button type="button" @click="modalEmployee = {{ $employee->id }}" class="inline-flex h-9 items-center rounded-lg border border-slate-300 bg-white px-3.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-[#f4c212] hover:bg-[#fff9df] hover:shadow-md">Edit KPI</button>
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
        </div>

        <aside class="flex h-full min-h-0 w-full flex-col self-stretch overflow-hidden rounded-[22px] border border-slate-200 bg-[#f8f7f5] p-2.5 shadow-[0_18px_38px_rgba(15,23,42,0.04)]">
            <div class="mb-2 shrink-0">
                <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Setup Guide</p>
            </div>

            <div class="scroll-surface flex flex-1 flex-col gap-2.5 overflow-y-auto pr-1">
                <div class="flex-1 rounded-[16px] bg-white p-3 shadow-[0_8px_18px_rgba(15,23,42,0.04)]">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Before scoring</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">
                        Confirm the department, role, and review period before encoding performance values.
                    </p>
                </div>

                <div class="flex-1 rounded-[16px] border border-dashed border-slate-300 bg-white/70 p-3 shadow-[0_8px_18px_rgba(15,23,42,0.02)]">
                    <p class="mb-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Best practice</p>
                    <p class="text-sm leading-5 text-slate-600">
                        Keep monthly KPIs consistent so reviews remain comparable across teams and departments.
                    </p>
                </div>

                <div class="flex-1 rounded-[16px] bg-[#111111] p-3 text-white shadow-[0_12px_22px_rgba(17,17,17,0.2)]">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-300">Checklist</p>
                    <ul class="mt-2 space-y-2 text-sm text-slate-200">
                        <li>• Correct employee selected</li>
                        <li>• Review period verified</li>
                        <li>• Target and completed workload recorded</li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>

    <div class="mt-4">
        {{ $employees->links() }}
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
                            @if($indicator->name === 'Productivity')
                                <div class="min-w-0 rounded-xl border border-slate-200 bg-white px-3 py-3">
                                    <h4 class="mb-2 text-sm font-semibold text-slate-700">Productivity</h4>
                                    <div class="mb-3 rounded-lg bg-slate-50 px-3 py-2 text-xs leading-5 text-slate-600">
                                        <span class="font-semibold text-slate-700">Basis:</span>
                                        Completed workload ÷ Target workload × 100, capped at 100%.
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label for="modal-workload-target-{{ $employee->id }}" class="mb-1.5 block text-xs font-medium text-slate-600">Target workload</label>
                                            <input id="modal-workload-target-{{ $employee->id }}" type="number" min="1" step="1" name="workload_target" value="{{ $employeeRecords->get($indicator->id)?->workload_target }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" required>
                                            @error('workload_target') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label for="modal-workload-completed-{{ $employee->id }}" class="mb-1.5 block text-xs font-medium text-slate-600">Completed workload</label>
                                            <input id="modal-workload-completed-{{ $employee->id }}" type="number" min="0" step="1" name="workload_completed" value="{{ $employeeRecords->get($indicator->id)?->workload_completed }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" required>
                                            @error('workload_completed') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-[minmax(0,1fr)_8rem] items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                                    <div>
                                        <label for="modal-score-{{ $employee->id }}-{{ $indicator->id }}" class="text-sm font-semibold text-slate-700">{{ $indicator->name }}</label>
                                        <p class="mt-1 text-xs leading-4 text-slate-500">
                                            @if($indicator->name === 'Work Quality')
                                                Basis: Accuracy and correctness; deduct for defects or rework.
                                            @elseif($indicator->name === 'Efficiency')
                                                Basis: Output achieved using available time and resources.
                                            @elseif($indicator->name === 'Timeliness')
                                                Basis: Completion and submission against deadlines.
                                            @else
                                                Basis: Enter the score based on the indicator's performance standard.
                                            @endif
                                        </p>
                                    </div>
                                    <input id="modal-score-{{ $employee->id }}-{{ $indicator->id }}" type="number" step="0.01" min="0" max="100" name="scores[{{ $indicator->id }}]" value="{{ $employeeRecords->get($indicator->id)?->score }}" placeholder="0-100" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25" required>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <label for="modal-remarks-{{ $employee->id }}" class="mb-1.5 block text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Remarks</label>
                        <textarea id="modal-remarks-{{ $employee->id }}" name="remarks" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none focus:border-[#f4c212] focus:ring-2 focus:ring-[#f4c212]/25">{{ $employeeRecords->first()?->remarks }}</textarea>
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-2">
                        <button type="button" @click="const form = $el.closest('form'); form.querySelectorAll('input[name=&quot;workload_target&quot;], input[name=&quot;workload_completed&quot;], input[name^=&quot;scores[&quot;], textarea[name=&quot;remarks&quot;]').forEach(field => field.value = '')" class="rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:border-red-300 hover:bg-red-50">Clear All</button>
                        <div class="flex gap-2">
                            <button type="button" @click="modalEmployee = null" class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-white">Cancel</button>
                            <button type="submit" class="rounded-xl bg-[#0d3b66] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0b2e52]">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-layouts.dashboard>
