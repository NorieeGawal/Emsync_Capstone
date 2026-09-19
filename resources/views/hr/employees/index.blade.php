<x-layouts.dashboard title="Employees">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="mt-1 text-2xl font-bold tracking-[-0.03em] text-slate-800">Workforce Directory</h3>
            <p class="mt-1 text-sm text-slate-500">Manage employee, manager, and HR accounts.</p>
        </div>
        <a href="{{ route('hr.employees.create') }}" class="inline-flex items-center justify-center self-start rounded-xl bg-[#111111] px-4 py-2.5 text-sm font-bold text-white shadow-[0_7px_15px_rgba(17,17,17,0.18)] transition hover:-translate-y-0.5 hover:bg-[#232a30] sm:self-center">
            Add Account
        </a>
    </div>

    <form method="GET" action="{{ route('hr.employees.index') }}" class="mb-5 flex flex-col items-start gap-3 sm:flex-row sm:items-center">
        <div class="relative max-w-sm">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name..." class="w-full rounded-xl border-slate-300 bg-slate-50 py-2 pl-4 pr-10 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">
            <button type="submit" aria-label="Search employees" class="absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-xs text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                &#128269;
            </button>
        </div>
        @if($search)
            <a href="{{ route('hr.employees.index') }}" class="text-sm font-medium text-slate-500 hover:text-red-600">Clear</a>
        @endif
    </form>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(0,1.7fr)_minmax(190px,0.52fr)] xl:items-start">
        <div class="max-h-[calc(100vh-17rem)] overflow-hidden rounded-[20px] border border-slate-200 bg-white shadow-[0_10px_22px_rgba(15,23,42,0.05)]">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Department</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $employee)
                        <tr>
                            <td class="px-5 py-4 font-semibold text-slate-800">{{ $employee->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-xs
                                    @if($employee->role === 'hr') bg-slate-200 text-slate-700
                                    @elseif($employee->role === 'manager') bg-[#f4c212]/25 text-slate-800
                                    @else bg-slate-100 text-slate-700 @endif">
                                    {{ $employee->role === 'hr' ? 'HR' : ucfirst($employee->role) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $employee->department->name ?? '—' }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $employee->email }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($employee->role !== 'hr')
                                        <a href="{{ route('hr.employees.show', $employee) }}" class="text-xs font-semibold text-slate-700 hover:underline">View</a>
                                    @endif
                                    <a href="{{ route('hr.employees.edit', $employee) }}" class="text-xs font-semibold text-slate-700 hover:underline">Edit</a>
                                    <form id="delete-form-{{ $employee->id }}" action="{{ route('hr.employees.destroy', $employee) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="openDeleteModal('{{ $employee->id }}', '{{ $employee->name }}')" class="rounded-lg bg-[#111111] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700">Delete</button>
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

        <aside class="rounded-[20px] border border-slate-200 bg-[#f8f7f5] p-3 shadow-[0_10px_22px_rgba(15,23,42,0.04)]">
            <div class="mb-3">
                <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Setup Guide</p>
            </div>

            <div class="space-y-3">
                <div class="rounded-2xl bg-white p-3 shadow-sm">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Before creating</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">
                        Assign each account to the correct role and department so access, reporting, and approvals stay accurate.
                    </p>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-3">
                    <p class="mb-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Best practice</p>
                    <p class="text-sm leading-5 text-slate-600">
                        Keep manager and employee records aligned with the department structure for cleaner performance tracking.
                    </p>
                </div>

                <div class="rounded-2xl bg-[#111111] p-3 text-white shadow-sm">
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
    <x-confirm-modal id="delete-modal" title="Remove Account" message="Are you sure you want to remove this account? This action cannot be undone." />

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