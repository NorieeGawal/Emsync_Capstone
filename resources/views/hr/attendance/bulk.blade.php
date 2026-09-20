<x-layouts.dashboard title="Attendance">
    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(0,1.7fr)_minmax(190px,0.52fr)] xl:items-start">
        <div class="space-y-3">
            <form method="POST" action="{{ route('hr.attendance.settings.update') }}" class="rounded-[20px] border border-slate-200 bg-white p-4 shadow-[0_10px_22px_rgba(15,23,42,0.05)]">
                @csrf
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Company Attendance Schedule</h3>
                        <p class="text-xs text-slate-500">Set the official working hours for the team.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Time In</label>
                            <input type="time" name="scheduled_time_in" value="{{ old('scheduled_time_in', \Carbon\Carbon::parse($settings->scheduled_time_in)->format('H:i')) }}" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]" required>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Time Out</label>
                            <input type="time" name="scheduled_time_out" value="{{ old('scheduled_time_out', \Carbon\Carbon::parse($settings->scheduled_time_out)->format('H:i')) }}" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]" required>
                        </div>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#111111] px-4 py-2.5 text-sm font-bold text-white shadow-[0_7px_15px_rgba(17,17,17,0.18)] transition hover:bg-[#232a30]">Save Schedule</button>
                </div>
                @error('scheduled_time_in') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                @error('scheduled_time_out') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </form>

            <form method="GET" action="{{ route('hr.attendance.bulk') }}" class="rounded-[20px] border border-slate-200 bg-white p-4 shadow-[0_10px_22px_rgba(15,23,42,0.05)]">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Date</label>
                        <input type="date" name="date" value="{{ $date }}" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Department</label>
                        <select name="department" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ (string)$departmentId === (string)$department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Search</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search employee" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">
                    </div>
                </div>
                <div class="mt-4 flex gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#111111] px-4 py-2.5 text-sm font-bold text-white shadow-[0_7px_15px_rgba(17,17,17,0.18)] transition hover:bg-[#232a30]">Apply</button>
                    <a href="{{ route('hr.attendance.bulk') }}" class="inline-flex items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100">Clear</a>
                </div>
            </form>

            <form method="POST" action="{{ route('hr.attendance.bulk.store') }}" class="rounded-[20px] border border-slate-200 bg-white shadow-[0_10px_22px_rgba(15,23,42,0.05)]">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">

                <div class="overflow-hidden rounded-[20px]">
                    <div class="max-h-[calc(100vh-24rem)] overflow-y-auto">
                        <table class="w-full min-w-[800px] text-left text-sm">
                            <thead class="bg-slate-50 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">
                                <tr>
                                    <th class="px-4 py-3">Employee</th>
                                    <th class="px-4 py-3">Department</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Time In</th>
                                    <th class="px-4 py-3">Time Out</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($employees as $employee)
                                    @php
                                        $log = $existingLogs->get($employee->id);
                                        $status = old("entries.{$employee->id}.status", $log?->status ?? 'present');
                                        $timeIn = old("entries.{$employee->id}.time_in", $log?->time_in ? \Carbon\Carbon::parse($log->time_in)->format('H:i') : '');
                                        $timeOut = old("entries.{$employee->id}.time_out", $log?->time_out ? \Carbon\Carbon::parse($log->time_out)->format('H:i') : '');
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $employee->name }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $employee->department->name ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <select name="entries[{{ $employee->id }}][status]" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">
                                                <option value="present" {{ $status === 'present' ? 'selected' : '' }}>Present</option>
                                                <option value="late" {{ $status === 'late' ? 'selected' : '' }}>Late</option>
                                                <option value="absent" {{ $status === 'absent' ? 'selected' : '' }}>Absent</option>
                                                <option value="excused" {{ $status === 'excused' ? 'selected' : '' }}>Excused</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="time" name="entries[{{ $employee->id }}][time_in]" value="{{ $timeIn }}" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="time" name="entries[{{ $employee->id }}][time_out]" value="{{ $timeOut }}" class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">No employees match your filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($employees->count())
                    <div class="mt-6 flex justify-end px-4 pb-4">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#111111] px-5 py-2.5 text-sm font-bold text-white shadow-[0_7px_15px_rgba(17,17,17,0.18)] transition hover:bg-[#232a30]">Save Attendance</button>
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
                        Verify the date, department filter, and scheduled time before finalizing attendance entries.
                    </p>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-3">
                    <p class="mb-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Best practice</p>
                    <p class="text-sm leading-5 text-slate-600">
                        Keep attendance updates consistent by checking late, absent, and excused statuses before saving.
                    </p>
                </div>

                <div class="rounded-2xl bg-[#111111] p-3 text-white shadow-sm">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-300">Checklist</p>
                    <ul class="mt-2 space-y-2 text-sm text-slate-200">
                        <li>• Shift time reviewed</li>
                        <li>• Status updated</li>
                        <li>• Final save confirmed</li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.dashboard>
