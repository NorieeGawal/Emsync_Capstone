<x-layouts.dashboard title="HR Departments" header-subtitle="Create a new staff group.">

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-3 overflow-hidden xl:h-[calc(100vh-7rem)] xl:grid-cols-[minmax(0,1fr)_250px] xl:items-stretch">
        <div class="flex h-full min-h-0 flex-col gap-3 overflow-hidden xl:flex-1">
            <div class="w-full shrink-0 rounded-[22px] border border-slate-200 bg-white p-4 shadow-[0_18px_38px_rgba(15,23,42,0.05)] sm:p-5">
                <p class="mb-2 text-[0.62rem] font-semibold uppercase tracking-[0.16em] text-slate-500">Quick add</p>
                <div class="mb-3 flex flex-wrap gap-1.5">
                    @foreach(['Production', 'Sales', 'Marketing', 'Operations', 'Human Resources', 'Finance', 'IT', 'Customer Support', 'Logistics', 'Procurement', 'Quality Assurance', 'Research & Development', 'Administration', 'Warehouse', 'Engineering'] as $suggestion)
                        <button type="button" onclick="document.getElementById('deptName').value = '{{ $suggestion }}'"
                            class="rounded-full bg-[#111111] px-2.5 py-1 text-[0.68rem] font-medium text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-[#232a30] hover:shadow-md">
                            {{ $suggestion }}
                        </button>
                    @endforeach
                </div>

                <form action="{{ route('hr.departments.store') }}" method="POST" class="flex flex-col gap-2.5 md:flex-row md:items-end">
                    @csrf
                    <div class="min-w-0 md:max-w-[320px] md:flex-1">
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Department Name</label>
                        <input type="text" id="deptName" name="name" value="{{ old('name') }}" placeholder="e.g. Logistics" class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-800 shadow-sm transition focus:border-[#f4c212] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20" required>
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="h-11 w-full whitespace-nowrap rounded-xl bg-[#111111] px-4 text-sm font-bold text-white shadow-[0_10px_18px_rgba(17,17,17,0.14)] transition hover:-translate-y-0.5 hover:bg-[#232a30] md:w-auto">
                        Add Department
                    </button>
                </form>
            </div>

            <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-[0_18px_38px_rgba(15,23,42,0.05)] max-xl:h-[18rem] xl:min-h-0">
                <div class="flex shrink-0 flex-col gap-3 border-b border-slate-200 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h4 class="text-base font-bold tracking-[-0.01em] text-slate-800">Current Departments</h4>
                        <p class="mt-1 text-[0.7rem] text-slate-500">Staff groups currently configured.</p>
                    </div>
                    <form method="GET" action="{{ route('hr.departments.index') }}" class="flex w-full flex-wrap items-center gap-2 sm:w-auto">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search departments..." class="h-11 w-40 rounded-xl border border-slate-300 bg-slate-50 px-3 text-sm text-slate-700 shadow-sm transition focus:border-[#f4c212] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20">
                        <button type="submit" aria-label="Search departments" class="inline-flex h-11 items-center justify-center rounded-xl bg-[#111111] px-4 text-sm font-semibold text-white shadow-[0_8px_16px_rgba(17,17,17,0.14)] transition hover:-translate-y-0.5 hover:bg-[#232a30]">
                            Search
                        </button>
                        @if($search)
                            <a href="{{ route('hr.departments.index') }}" class="inline-flex h-11 items-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-500 transition hover:border-red-200 hover:text-red-600">Clear</a>
                        @endif
                    </form>
                </div>

                <div class="scroll-surface h-full min-h-0 flex-1 overflow-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="sticky top-0 z-10 bg-[#f8f9fb] text-[0.62rem] font-semibold uppercase tracking-[0.16em] text-slate-500 shadow-[inset_0_-1px_0_rgba(148,163,184,0.22)]">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Staff Count</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($departments as $dept)
                                <tr class="group transition-colors hover:bg-[#fffdf3]">
                                    <td class="px-4 py-3.5 font-semibold text-slate-800">{{ $dept->name }}</td>
                                    <td class="px-4 py-3.5 text-slate-600">
                                        <span class="inline-flex min-w-8 justify-center rounded-full bg-[#f4c212]/20 px-2 py-1 text-[0.68rem] font-bold text-slate-700 ring-1 ring-inset ring-[#f4c212]/20">{{ $dept->users_count }}</span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if($dept->users_count > 0)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2 py-1 text-[0.68rem] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-200 px-2 py-1 text-[0.68rem] font-semibold text-slate-600 ring-1 ring-inset ring-slate-300">
                                                <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <form action="{{ route('hr.departments.destroy', $dept) }}" method="POST" class="inline" onsubmit="return confirm('Remove this department?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 items-center rounded-lg bg-[#111111] px-3 text-[0.68rem] font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-red-700 hover:shadow-md">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-400">No departments yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
                        Keep departments aligned with real teams so reporting, access, and workflow stay clear.
                    </p>
                </div>

                <div class="flex-1 rounded-[16px] border border-dashed border-slate-300 bg-white/70 p-3 shadow-[0_8px_18px_rgba(15,23,42,0.02)]">
                    <p class="mb-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Best practice</p>
                    <p class="text-sm leading-5 text-slate-600">
                        Keep names simple and consistent, such as Finance, Operations, or Production.
                    </p>
                </div>

                <div class="flex-1 rounded-[16px] bg-[#111111] p-3 text-white shadow-[0_12px_22px_rgba(17,17,17,0.18)]">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-300">Checklist</p>
                    <ul class="mt-2 space-y-2 text-sm text-slate-200">
                        <li>• Match real team structure</li>
                        <li>• Keep naming consistent</li>
                        <li>• Assign managers clearly</li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>
    <style>
        .scrollbar-thin {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }

        .scrollbar-thin::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 9999px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</x-layouts.dashboard>