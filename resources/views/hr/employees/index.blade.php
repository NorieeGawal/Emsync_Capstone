<x-layouts.dashboard title="HR Employees" header-subtitle="Manage employee, manager, and HR accounts.">

    <form method="GET" action="{{ route('hr.employees.index') }}" class="mb-6 flex flex-wrap items-center gap-3 rounded-[22px] border border-slate-200 bg-white/80 p-3 shadow-[0_12px_30px_rgba(15,23,42,0.04)] backdrop-blur-sm">
        <div class="relative w-full max-w-sm">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name..." class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 py-2 pl-4 pr-10 text-sm text-slate-700 shadow-sm transition focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20">
            <button type="submit" aria-label="Search employees" class="absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="6.5"></circle>
                    <path d="m16 16 4 4"></path>
                </svg>
            </button>
        </div>
        <a href="{{ route('hr.employees.archived') }}" class="ml-auto inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
            Archived Accounts
        </a>
        <a href="{{ route('hr.employees.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#111111] px-4 py-2.5 text-sm font-bold text-white shadow-[0_10px_18px_rgba(17,17,17,0.14)] transition hover:-translate-y-0.5 hover:bg-[#232a30]">
            Add Account
        </a>
        @if($search)
            <a href="{{ route('hr.employees.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-red-200 hover:text-red-600">Clear</a>
        @endif
    </form>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(0,1.7fr)_minmax(190px,0.52fr)] xl:items-stretch xl:h-[calc(100vh-13.5rem)]">
        <div class="flex h-full min-h-0 flex-col overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-[0_18px_38px_rgba(15,23,42,0.05)]">
            <div class="scroll-surface min-h-0 flex-1 overflow-y-auto">
            <table class="w-full text-left text-sm">
                <thead class="sticky top-0 z-10 bg-[#f8f9fb] text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500 shadow-[inset_0_-1px_0_rgba(148,163,184,0.22)]">
                    <tr>
                        <th class="px-5 py-3.5">Name</th>
                        <th class="px-5 py-3.5">Role</th>
                        <th class="px-5 py-3.5">Department</th>
                        <th class="px-5 py-3.5">Email</th>
                        <th class="px-5 py-3.5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $employee)
                        <tr class="group transition-colors hover:bg-[#fffdf3]">
                            <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $employee->name }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-1.5 text-[0.7rem] font-semibold ring-1 ring-inset ring-black/[0.04]
                                    @if($employee->role === 'hr') bg-slate-200 text-slate-700
                                    @elseif($employee->role === 'manager') bg-[#f4c212]/25 text-slate-800
                                    @else bg-slate-100 text-slate-700 @endif">
                                    {{ $employee->role === 'hr' ? 'HR' : ucfirst($employee->role) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $employee->department->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $employee->email }}</td>
                            <td class="px-5 py-3.5">
                                <div class="grid grid-cols-[2.5rem_2.5rem_auto] items-center gap-3 opacity-90 transition-opacity group-hover:opacity-100">
                                    @if($employee->role !== 'hr')
                                        <a href="{{ route('hr.employees.show', $employee) }}" class="w-10 text-xs font-semibold text-slate-700 transition hover:text-[#0d3b66] hover:underline">View</a>
                                    @else
                                        <span class="w-10" aria-hidden="true"></span>
                                    @endif
                                    <a href="{{ route('hr.employees.edit', $employee) }}" class="w-10 text-xs font-semibold text-slate-700 transition hover:text-[#0d3b66] hover:underline">Edit</a>
                                    <form id="delete-form-{{ $employee->id }}" action="{{ route('hr.employees.destroy', $employee) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="openDeleteModal('{{ $employee->id }}', '{{ $employee->name }}')" class="inline-flex h-9 items-center rounded-lg bg-[#111111] px-3 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-red-700 hover:shadow-md">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">No accounts yet.</td>
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
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Before creating</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">
                        Assign each account to the correct role and department so access, reporting, and approvals stay accurate.
                    </p>
                </div>

                <div class="flex-1 rounded-[16px] border border-dashed border-slate-300 bg-white/70 p-3 shadow-[0_8px_18px_rgba(15,23,42,0.02)]">
                    <p class="mb-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Best practice</p>
                    <p class="text-sm leading-5 text-slate-600">
                        Keep manager and employee records aligned with the department structure for cleaner performance tracking.
                    </p>
                </div>

                <div class="flex-1 rounded-[16px] bg-[#111111] p-3 text-white shadow-[0_12px_22px_rgba(17,17,17,0.18)]">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-300">Checklist</p>
                    <ul class="mt-2 space-y-2 text-sm text-slate-200">
                        <li>• Confirm role is correct</li>
                        <li>• Match assigned department</li>
                        <li>• Verify email is active</li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>

    <div class="mt-4">
        {{ $employees->links() }}
    </div>

    <x-confirm-modal id="delete-modal" title="Archive Account" message="This account will be moved to Archived Accounts. It can be restored for one month before permanent deletion." />

    <script>
        let deleteFormId = null;

        function openDeleteModal(employeeId, name) {
            deleteFormId = 'delete-form-' + employeeId;
            const modal = document.getElementById('delete-modal');
            modal.querySelector('h3').textContent = 'Remove ' + name + '?';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        document.getElementById('delete-modal-confirm').addEventListener('click', function () {
            if (deleteFormId) {
                document.getElementById(deleteFormId).submit();
            }
        });
    </script>
</x-layouts.dashboard>