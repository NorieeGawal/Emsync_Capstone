<x-layouts.dashboard title="Profile">
    <div class="mb-4">
        <h3 class="text-xl font-bold tracking-[-0.03em] text-slate-800">Profile</h3>
        <p class="mt-1 text-sm text-slate-600">Manage your personal information, photo, and password.</p>
    </div>

    <div class="grid max-w-5xl grid-cols-1 gap-3 md:grid-cols-2">
        <div class="rounded-[16px] border border-slate-200 bg-white p-4 shadow-[0_10px_22px_rgba(15,23,42,0.05)]">
            @include('profile.partials.update-profile-information-form')
        </div>
        <div class="rounded-[16px] border border-slate-200 bg-white p-4 shadow-[0_10px_22px_rgba(15,23,42,0.05)]">
            @include('profile.partials.update-password-form')
        </div>
        <aside class="rounded-[16px] border border-slate-200 bg-[#f8f7f5] p-3 shadow-[0_10px_22px_rgba(15,23,42,0.04)] md:col-span-2">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-slate-500">Account access</p>
                <span><span class="text-xs text-slate-500">Role:</span> <strong class="text-slate-800">{{ ucfirst(auth()->user()->role) }}</strong></span>
                <span><span class="text-xs text-slate-500">Department:</span> <strong class="text-slate-800">{{ auth()->user()->department->name ?? 'No department' }}</strong></span>
                <span class="text-xs text-slate-500">Role and department are managed by HR.</span>
            </div>
        </aside>
    </div>
</x-layouts.dashboard>
