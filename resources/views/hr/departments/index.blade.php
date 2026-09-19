<x-layouts.dashboard title="Departments">

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

    <div class="grid grid-cols-1 gap-3 overflow-hidden xl:grid-cols-[minmax(0,1.6fr)_minmax(190px,0.52fr)] xl:items-start">
        <div class="space-y-3 overflow-hidden">
            <div class="w-full rounded-[20px] border border-slate-200 bg-white p-3 shadow-[0_10px_22px_rgba(15,23,42,0.05)] sm:p-4">
                <div class="mb-3">
                    <h4 class="text-base font-bold tracking-[-0.01em] text-slate-800">Add Department</h4>
                    <p class="mt-1 text-[0.7rem] text-slate-500">Create a new staff group.</p>
                </div>

                <p class="mb-2 text-[0.62rem] font-semibold uppercase tracking-[0.16em] text-slate-500">Quick add</p>
                <div class="mb-3 flex flex-wrap gap-1.5">
                    @foreach(['Production', 'Sales', 'Marketing', 'Operations', 'Human Resources', 'Finance', 'IT', 'Customer Support', 'Logistics', 'Procurement', 'Quality Assurance', 'Research & Development', 'Administration', 'Warehouse', 'Engineering'] as $suggestion)
                        <button type="button" onclick="document.getElementById('deptName').value = '{{ $suggestion }}'"
                            class="rounded-full bg-[#111111] px-2.5 py-1 text-[0.68rem] font-medium text-white transition hover:bg-[#232a30]">
                            {{ $suggestion }}
                        </button>
                    @endforeach
                </div>

                <form action="{{ route('hr.departments.store') }}" method="POST" class="space-y-2.5">
                    @csrf
                    <div class="max-w-[320px]">
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Department Name</label>
                        <input type="text" id="deptName" name="name" value="{{ old('name') }}" placeholder="e.g. Logistics" class="w-full rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 transition focus:border-[#f4c212] focus:bg-white focus:ring-2 focus:ring-[#f4c212]/20" required>
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="w-full max-w-[320px] rounded-xl bg-[#111111] px-4 py-2.5 text-sm font-bold text-white shadow-[0_7px_15px_rgba(17,17,17,0.18)] transition hover:-translate-y-0.5 hover:bg-[#232a30]">
                        Add Department
                    </button>
                </form>
            </div>

            <div class="flex h-[calc(100vh-23rem)] min-h-[8rem] flex-col overflow-hidden rounded-[20px] border border-slate-200 bg-white shadow-[0_10px_22px_rgba(15,23,42,0.05)] max-xl:h-[18rem]">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-3 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h4 class="text-base font-bold tracking-[-0.01em] text-slate-800">Current Departments</h4>
                        <p class="mt-1 text-[0.7rem] text-slate-500">Staff groups currently configured.</p>
                    </div>
                    <form method="GET" action="{{ route('hr.departments.index') }}" class="flex items-center gap-2">
                        <div class="relative">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search departments..." class="w-40 rounded-xl border-slate-300 bg-slate-50 py-2 pl-3 pr-9 text-sm text-slate-700 focus:border-[#f4c212] focus:ring-[#f4c212]">
                            <button type="submit" aria-label="Search departments" class="absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-xs text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                &#128269;
                            </button>
                        </div>
                        @if($search)
                            <a href="{{ route('hr.departments.index') }}" class="text-xs font-medium text-slate-500 hover:text-red-600">Clear</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-y-auto overflow-x-auto scrollbar-thin scrollbar-thumb-slate-300 scrollbar-track-slate-100">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[0.62rem] font-semibold uppercase tracking-[0.16em] text-slate-500">
                            <tr>
                                <th class="px-4 py-2.5">Name</th>
                                <th class="px-4 py-2.5">Staff Count</th>
                                <th class="px-4 py-2.5">Status</th>
                                <th class="px-4 py-2.5">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($departments as $dept)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $dept->name }}</td>
                                    <td class="px-4 py-3 text-slate-600">
                                        <span class="inline-flex min-w-8 justify-center rounded-full bg-[#f4c212]/20 px-2 py-1 text-[0.68rem] font-bold text-slate-700">{{ $dept->users_count }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($dept->users_count > 0)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2 py-1 text-[0.68rem] font-semibold text-emerald-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-200 px-2 py-1 text-[0.68rem] font-semibold text-slate-600">
                                                <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <form action="{{ route('hr.departments.destroy', $dept) }}" method="POST" class="inline" onsubmit="return confirm('Remove this department?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-[#111111] px-2.5 py-1.5 text-[0.68rem] font-semibold text-white transition hover:bg-red-700">Delete</button>
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

        <aside class="rounded-[20px] border border-slate-200 bg-[#f8f7f5] p-3 shadow-[0_10px_22px_rgba(15,23,42,0.04)]">
            <div class="mb-3">
                <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Setup Guide</p>
            </div>

            <div class="space-y-3">
                <div class="rounded-2xl bg-white p-3 shadow-sm">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Before creating</p>
                    <p class="mt-2 text-sm leading-5 text-slate-600">
                        Keep departments aligned with real teams so reporting, access, and workflow stay clear.
                    </p>
                </div>

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-3">
                    <p class="mb-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Best practice</p>
                    <p class="text-sm leading-5 text-slate-600">
                        Keep names simple and consistent, such as Finance, Operations, or Production.
                    </p>
                </div>

                <div class="rounded-2xl bg-[#111111] p-3 text-white shadow-sm">
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