<x-layouts.dashboard title="Edit Employee">
    <div class="mb-4 flex flex-col gap-2 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Workforce Directory</p>
            <h3 class="mt-1 text-2xl font-bold tracking-[-0.03em] text-slate-800">Edit Employee</h3>
            <p class="mt-1 text-sm text-slate-500">Update role, department, and contact information.</p>
        </div>

        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm">
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            Active account
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.7fr)_minmax(220px,0.8fr)] xl:items-start">
        <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-[0_12px_28px_rgba(15,23,42,0.05)] sm:p-5">
            <form action="{{ route('hr.employees.update', $employee) }}" method="POST" class="space-y-3">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Full Name</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $employee->name) }}"
                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 transition focus:border-[#f4c212] focus:bg-white focus:ring-2 focus:ring-[#f4c212]/20"
                            required
                        >
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $employee->email) }}"
                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 transition focus:border-[#f4c212] focus:bg-white focus:ring-2 focus:ring-[#f4c212]/20"
                            required
                        >
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Role</label>
                        <select
                            name="role"
                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 transition focus:border-[#f4c212] focus:bg-white focus:ring-2 focus:ring-[#f4c212]/20"
                            required
                        >
                            <option value="employee" {{ old('role', $employee->role) == 'employee' ? 'selected' : '' }}>Employee</option>
                            <option value="manager" {{ old('role', $employee->role) == 'manager' ? 'selected' : '' }}>Manager / Team Leader</option>
                            <option value="hr" {{ old('role', $employee->role) == 'hr' ? 'selected' : '' }}>HR Personnel</option>
                        </select>
                        @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Department</label>
                        <select
                            name="department_id"
                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 transition focus:border-[#f4c212] focus:bg-white focus:ring-2 focus:ring-[#f4c212]/20"
                        >
                            <option value="">— None —</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-1 sm:flex-row">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#111111] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#232a30]">
                        Update Employee
                    </button>
                    <a href="{{ route('hr.employees.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <aside class="rounded-[22px] border border-slate-200 bg-[#f8f7f5] p-4 shadow-[0_12px_28px_rgba(15,23,42,0.04)]">
            <div class="mb-3">
                <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Account Summary</p>
            </div>

            <div class="space-y-3">
                <div class="rounded-2xl bg-white p-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#f4c212] text-sm font-black text-[#111111]">
                            {{ Str::upper(Str::substr($employee->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ $employee->name }}</p>
                            <p class="text-[11px] text-slate-500">{{ $employee->email }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-3 shadow-sm">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Role</span>
                        <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-700">
                            {{ $employee->role === 'hr' ? 'HR' : ucfirst($employee->role) }}
                        </span>
                    </div>

                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Department</span>
                        <span class="text-xs font-medium text-slate-700">
                            {{ $employee->department?->name ?? 'Unassigned' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Status</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Active
                        </span>
                    </div>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-3">
                    <p class="mb-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Quick note</p>
                    <p class="text-sm leading-5 text-slate-600">
                        Keep permissions aligned with the employee’s role and department to maintain clean access control.
                    </p>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.dashboard>