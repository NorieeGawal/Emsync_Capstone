<x-layouts.dashboard title="HR Attendance Overview">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="mt-1 text-2xl font-bold tracking-[-0.03em] text-slate-800">Attendance</h3>
            <p class="mt-1 text-sm text-slate-500">Manage daily check-ins, late logs, and bulk updates from one workflow.</p>
        </div>
        <a href="{{ route('hr.attendance.bulk') }}" class="inline-flex items-center justify-center rounded-xl bg-[#111111] px-4 py-2.5 text-sm font-bold text-white shadow-[0_7px_15px_rgba(17,17,17,0.18)] transition hover:-translate-y-0.5 hover:bg-[#232a30]">
            Open Bulk Attendance
        </a>
    </div>

    <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(0,1.7fr)_minmax(190px,0.52fr)] xl:items-start">
        <div class="rounded-[20px] border border-slate-200 bg-white p-6 shadow-[0_10px_22px_rgba(15,23,42,0.05)]">
            <div class="mb-5 flex items-center justify-between border-b border-slate-200 pb-3">
                <p class="text-[0.7rem] font-semibold uppercase tracking-[0.18em] text-slate-500">Overview</p>
                <span class="rounded-full bg-[#f4c212]/20 px-2.5 py-1 text-[0.68rem] font-semibold text-slate-700">Live</span>
            </div>

            <div class="space-y-4">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[0.62rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Today</p>
                        <p class="mt-2 text-xl font-bold text-slate-800">122</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[0.62rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Late</p>
                        <p class="mt-2 text-xl font-bold text-slate-800">8</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[0.62rem] font-semibold uppercase tracking-[0.14em] text-slate-500">Absent</p>
                        <p class="mt-2 text-xl font-bold text-slate-800">3</p>
                    </div>
                </div>

                <div class="rounded-2xl bg-[#f8f7f5] p-4">
                    <p class="text-sm font-semibold text-slate-700">Attendance workflow</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li>• Confirm the company schedule and cutoff time.</li>
                        <li>• Filter by department or date before bulk updates.</li>
                        <li>• Review each status and save the final attendance log.</li>
                    </ul>
                </div>
            </div>
        </div>

        <aside class="rounded-[20px] border border-slate-200 bg-[#f8f7f5] p-3 shadow-[0_10px_22px_rgba(15,23,42,0.04)]">
            <div class="mb-3">
                <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Setup Guide</p>
            </div>

            <div class="space-y-3">
                <div class="rounded-2xl bg-white p-3 shadow-sm">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Before entry</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">
                        Set the official time in and time out before logging employee attendance for the day.
                    </p>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-3">
                    <p class="mb-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Best practice</p>
                    <p class="text-sm leading-5 text-slate-600">
                        Use the date and department filters to keep bulk edits focused and easier to verify.
                    </p>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.dashboard>
