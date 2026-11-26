<x-layouts.dashboard title="HR Attendance Bulk Update">
    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-3 xl:h-[calc(100vh-7rem)] xl:grid-cols-[minmax(0,1fr)_250px] xl:items-stretch">
        <div class="flex h-full min-h-0 flex-col gap-3 self-stretch xl:flex-1">
            <form method="POST" action="{{ route('hr.attendance.settings.update') }}" class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-[0_18px_38px_rgba(15,23,42,0.05)] sm:p-5">
                @csrf
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Company Attendance Schedule</h3>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <div class="flex items-center gap-2">
                            <label class="text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Time In</label>
                            <input type="text" name="scheduled_time_in" value="{{ old('scheduled_time_in', \Carbon\Carbon::parse($settings->scheduled_time_in)->format('H:i')) }}" placeholder="HH:MM" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="Enter time in 24-hour HH:MM format, from 00:00 to 23:59." class="h-11 w-[120px] rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-700 shadow-sm focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20" required>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Time Out</label>
                            <input type="text" name="scheduled_time_out" value="{{ old('scheduled_time_out', \Carbon\Carbon::parse($settings->scheduled_time_out)->format('H:i')) }}" placeholder="HH:MM" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="Enter time in 24-hour HH:MM format, from 00:00 to 23:59." class="h-11 w-[120px] rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-700 shadow-sm focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20" required>
                        </div>
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-[#111111] px-4 text-sm font-bold text-white shadow-[0_10px_18px_rgba(17,17,17,0.14)] transition hover:bg-[#232a30]">Save Schedule</button>
                    </div>
                </div>
                @error('scheduled_time_in') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                @error('scheduled_time_out') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </form>

            <form method="GET" action="{{ route('hr.attendance.bulk') }}" class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-[0_18px_38px_rgba(15,23,42,0.05)] sm:p-5">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.5fr)_auto] md:items-end">
                    <div>
                        <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Date</label>
                        <input type="date" name="date" value="{{ $date }}" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 shadow-sm focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Department</label>
                        <select name="department" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 shadow-sm focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20">
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
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search employee" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 shadow-sm focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20">
                    </div>
                    <div class="flex items-center gap-3 md:justify-end">
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-[#111111] px-4 text-sm font-bold text-white shadow-[0_10px_18px_rgba(17,17,17,0.14)] transition hover:bg-[#232a30]">Apply</button>
                        <a href="{{ route('hr.attendance.bulk') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:bg-slate-100">Clear</a>
                    </div>
                </div>
            </form>

            <form method="POST" action="{{ route('hr.attendance.bulk.store') }}" data-scheduled-time-in="{{ \Carbon\Carbon::parse($settings->scheduled_time_in)->format('H:i') }}" class="flex min-h-0 flex-1 flex-col rounded-[22px] border border-slate-200 bg-white shadow-[0_18px_38px_rgba(15,23,42,0.05)]">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">

                <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-[22px]">
                    <div class="scroll-surface min-h-0 flex-1 overflow-y-auto">
                        <table class="w-full min-w-[800px] text-left text-sm">
                            <thead class="sticky top-0 z-10 bg-[#f8f9fb] text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500 shadow-[inset_0_-1px_0_rgba(148,163,184,0.22)]">
                                <tr>
                                    <th class="px-4 py-3">Employee</th>
                                    <th class="px-4 py-3">Department</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Time In (24-hour)</th>
                                    <th class="px-4 py-3">Time Out (24-hour)</th>
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
                                    <tr class="group transition-colors hover:bg-[#fffdf3]">
                                        <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $employee->name }}</td>
                                        <td class="px-4 py-2.5 text-slate-600">{{ $employee->department->name ?? '—' }}</td>
                                        <td class="px-4 py-2.5">
                                            <select name="entries[{{ $employee->id }}][status]" title="Automatically set from Time In: at or before schedule is Present; after schedule is Late." class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 shadow-sm focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20">
                                                <option value="present" {{ $status === 'present' ? 'selected' : '' }}>Present</option>
                                                <option value="late" {{ $status === 'late' ? 'selected' : '' }}>Late</option>
                                                <option value="absent" {{ $status === 'absent' ? 'selected' : '' }}>Absent</option>
                                                <option value="excused" {{ $status === 'excused' ? 'selected' : '' }}>Excused</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="text" name="entries[{{ $employee->id }}][time_in]" data-attendance-time-in value="{{ $timeIn }}" placeholder="HH:MM" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="Enter time in 24-hour HH:MM format, from 00:00 to 23:59." class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-700 shadow-sm focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="text" name="entries[{{ $employee->id }}][time_out]" value="{{ $timeOut }}" placeholder="HH:MM" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="Enter time in 24-hour HH:MM format, from 00:00 to 23:59." class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-700 shadow-sm focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20">
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
                    <div class="flex justify-end border-t border-slate-200 px-4 py-3">
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-[#111111] px-5 text-sm font-bold text-white shadow-[0_10px_18px_rgba(17,17,17,0.14)] transition hover:-translate-y-0.5 hover:bg-[#232a30]">Save Attendance</button>
                    </div>
                @endif
            </form>
        </div>

        <aside class="flex h-full min-h-0 w-full flex-col self-stretch overflow-hidden rounded-[22px] border border-slate-200 bg-[#f8f7f5] p-2.5 shadow-[0_18px_38px_rgba(15,23,42,0.04)]">
            <div class="mb-2 shrink-0">
                <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Setup Guide</p>
            </div>

            <div class="scroll-surface flex flex-1 flex-col gap-2 overflow-y-auto pr-1">
                <div class="flex-1 rounded-[16px] bg-white p-2.5 shadow-[0_8px_18px_rgba(15,23,42,0.04)]">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Before saving</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">
                        Status updates as Time In is entered: on or before the scheduled time is Present; after it is Late.
                    </p>
                </div>

                <div class="flex-1 rounded-[16px] border border-dashed border-slate-300 bg-white/70 p-2.5 shadow-[0_8px_18px_rgba(15,23,42,0.02)]">
                    <p class="mb-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Best practice</p>
                    <p class="text-sm leading-5 text-slate-600">
                        Keep attendance updates consistent by checking late, absent, and excused statuses before saving.
                    </p>
                </div>

                <div class="flex-1 rounded-[16px] bg-[#111111] p-2.5 text-white shadow-[0_12px_22px_rgba(17,17,17,0.18)]">
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
