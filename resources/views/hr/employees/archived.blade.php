<x-layouts.dashboard title="Archived Accounts" header-subtitle="Restore an account within one month before it is permanently deleted.">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-[22px] border border-slate-200 bg-white/80 p-3 shadow-[0_12px_30px_rgba(15,23,42,0.04)]">
        <a href="{{ route('hr.employees.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            Back to Employees
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-[0_18px_38px_rgba(15,23,42,0.05)]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#f8f9fb] text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Name</th>
                        <th class="px-5 py-3.5">Role</th>
                        <th class="px-5 py-3.5">Department</th>
                        <th class="px-5 py-3.5">Email</th>
                        <th class="px-5 py-3.5">Archived</th>
                        <th class="px-5 py-3.5">Permanently deleted</th>
                        <th class="px-5 py-3.5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $employee)
                        <tr>
                            <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $employee->name }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $employee->role === 'hr' ? 'HR' : ucfirst($employee->role) }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $employee->department->name ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $employee->email }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $employee->deleted_at->format('M j, Y') }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $employee->deleted_at->copy()->addMonth()->format('M j, Y') }}</td>
                            <td class="px-5 py-3.5">
                                <form action="{{ route('hr.employees.restore', $employee->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex h-9 items-center rounded-lg bg-[#111111] px-3 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                        Restore
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">There are no archived accounts.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $employees->links() }}
    </div>
</x-layouts.dashboard>
