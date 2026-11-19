<x-layouts.dashboard title="HR Add Employee">
    <div class="mx-auto mb-4 flex max-w-6xl flex-col gap-2 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <h3 class="mt-1 text-2xl font-bold tracking-[-0.03em] text-slate-800">Add Employee</h3>
            <p class="mt-1 text-sm text-slate-500">Create a new employee record.</p>
        </div>

        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm">
            <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
            New profile
        </div>
    </div>

    <div class="mx-auto grid max-w-6xl grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.7fr)_minmax(220px,0.8fr)] xl:items-start">
        <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-[0_12px_28px_rgba(15,23,42,0.05)] sm:p-5">
            <form action="{{ route('hr.employees.store') }}" method="POST" class="space-y-3">
                @csrf

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Full Name</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 transition focus:border-[#f4c212] focus:bg-white focus:ring-2 focus:ring-[#f4c212]/20"
                            required
                        >
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 transition focus:border-[#f4c212] focus:bg-white focus:ring-2 focus:ring-[#f4c212]/20"
                            required
                        >
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Temporary Password</label>
                        <input
                            type="password"
                            name="password"
                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 transition focus:border-[#f4c212] focus:bg-white focus:ring-2 focus:ring-[#f4c212]/20"
                            required
                        >
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Role</label>
                        <select
                            name="role"
                            class="w-full rounded-xl border-slate-300 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 transition focus:border-[#f4c212] focus:bg-white focus:ring-2 focus:ring-[#f4c212]/20"
                            required
                        >
                            <option value="">— Select —</option>
                            <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager / Team Leader</option>
                            <option value="hr" {{ old('role') == 'hr' ? 'selected' : '' }}>HR Personnel</option>
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
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-1 sm:flex-row">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#111111] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#232a30]">
                        Save Employee
                    </button>
                    <a href="{{ route('hr.employees.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <aside class="rounded-[22px] border border-slate-200 bg-[#f8f7f5] p-4 shadow-[0_12px_28px_rgba(15,23,42,0.04)]">
            <div class="mb-3">
                <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Setup Guide</p>
            </div>

            <div class="space-y-3">
                <div class="rounded-2xl bg-white p-3 shadow-sm">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Before saving</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">
                        Confirm the employee’s role and department so access matches their responsibilities.
                    </p>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-3">
                    <p class="mb-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Tip</p>
                    <p class="text-sm leading-5 text-slate-600">
                        Use a strong default password and remind the user to change it on first login.
                    </p>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.dashboard>