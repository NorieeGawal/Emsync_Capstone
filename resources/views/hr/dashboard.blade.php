<x-layouts.dashboard title="Performance KPI Dashboard">
    @php($manilaNow = now()->setTimezone('Asia/Manila'))

    <div class="space-y-6">
        <section class="rounded-[20px] bg-[#232a30] px-5 py-5 text-white shadow-[0_12px_24px_rgba(35,42,48,0.18)] sm:px-6 sm:py-6" aria-label="HR dashboard information">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-white/60">HR Dashboard</p>
                    <h3 class="mt-1 truncate text-xl font-bold tracking-[-0.02em] sm:text-2xl">Hello, {{ auth()->user()->name }}</h3>
                </div>
                <time datetime="{{ $manilaNow->toIso8601String() }}" data-manila-clock class="shrink-0 text-base font-semibold text-white/90 sm:text-right sm:text-lg">
                    <span data-manila-date class="block">{{ $manilaNow->format('l, F j, Y') }}</span>
                    <span data-manila-time class="mt-1 block text-sm font-bold text-white/70 sm:text-base">{{ $manilaNow->format('h:i A') }}</span>
                </time>
            </div>
        </section>

        <div class="rounded-[20px] border border-slate-200 bg-[#f5f4f3] p-3 shadow-[0_10px_18px_rgba(15,23,42,0.04)]">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
                <div class="flex flex-1 min-w-0 flex-col gap-3 md:flex-row md:flex-wrap md:items-end">
                    <div class="w-full min-w-[180px] md:w-[260px]">
                        <label class="mb-2 block text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Department</label>
                        <div class="relative">
                            <select name="department" form="filterForm" class="w-full appearance-none rounded-xl border border-[#d9c45b] bg-[#f9f7f2] px-3 py-2 pr-10 text-sm text-slate-700 shadow-[inset_0_0_0_1px_rgba(244,194,18,0.25)] transition focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ (string)$departmentId === (string)$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[1.2rem] font-bold leading-none text-slate-500">▾</span>
                        </div>
                    </div>

                    <div class="w-full min-w-[180px] md:w-[260px]">
                        <label class="mb-2 block text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Month</label>
                        <div class="relative">
                            <input type="month" name="period" form="filterForm" value="{{ $period }}" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 pr-10 text-sm text-slate-700 shadow-sm transition focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20">
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">📅</span>
                        </div>
                    </div>

                    <button type="submit" form="filterForm" class="rounded-xl bg-[#f4c212] px-4 py-2.5 text-sm font-bold text-[#111111] shadow-[0_8px_18px_rgba(244,194,18,0.28)] transition hover:-translate-y-0.5 hover:bg-[#f5c93b]">
                        Apply Filter
                    </button>

                    @if($departmentId)
                        <a href="{{ route('hr.dashboard') }}" class="self-center text-sm font-medium text-slate-500 transition hover:text-red-500">Clear</a>
                    @endif
                </div>

                <div class="w-full xl:w-auto">
                    <a href="{{ route('hr.reports.summary', ['department' => $departmentId, 'period' => $period]) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-[#111111] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_18px_rgba(17,24,39,0.12)] transition hover:-translate-y-0.5 hover:bg-[#232a30] xl:w-auto">
                        Download Summary PDF
                    </a>
                </div>
            </div>
        </div>

        <form id="filterForm" method="GET" action="{{ route('hr.dashboard') }}" class="hidden"></form>

        <div class="grid items-stretch gap-[18px] xl:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-[0_5px_12px_rgba(15,23,42,0.04)]">
                <div class="mb-3 flex flex-col gap-0.5">
                    <h4 class="text-[0.92rem] font-bold tracking-[-0.01em] text-slate-700">Performance Trend</h4>
                    <p class="text-[0.68rem] text-slate-500">3 months leading to {{ \Carbon\Carbon::parse($period . '-01')->format('F Y') }}</p>
                </div>
                <div class="h-[218px] w-full min-w-0">
                    <canvas id="trendChart" class="h-full w-full"></canvas>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-[0_5px_12px_rgba(15,23,42,0.04)]">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h4 class="text-[0.92rem] font-bold tracking-[-0.01em] text-slate-700">KPI Breakdown</h4>
                </div>

                <div class="flex min-h-[218px] items-center justify-between gap-5">
                    <div class="w-full max-w-[250px] space-y-3">
                        @foreach($breakdown as $name => $score)
                            <div class="flex items-center gap-2">
                                <span class="w-[92px] shrink-0 text-[0.72rem] font-medium text-slate-600">{{ $name }}</span>
                                <div class="h-1.5 min-w-0 flex-1 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-[#f4c212]" style="width: {{ min($score, 100) }}%"></div>
                                </div>
                                <span class="w-7 shrink-0 text-right text-[0.72rem] font-medium text-slate-500">{{ number_format($score, 0) }}%</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="relative -left-2 w-[105px] shrink-0 text-center">
                        <div class="mb-2 text-[0.62rem] font-medium uppercase tracking-[0.02em] text-slate-500">Overall Rating</div>
                        <div class="mx-auto flex h-[92px] w-[92px] items-center justify-center rounded-full border-[6px] border-[#e32929] bg-white">
                            <span class="text-[1.2rem] font-bold text-[#d52222]">{{ number_format($companyAverage, 0) }}%</span>
                        </div>
                        <span class="mt-2 inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[0.62rem] font-semibold text-red-600">@if($companyAverage >= 90) Outstanding @elseif($companyAverage >= 80) Very Satisfactory @elseif($companyAverage >= 70) Satisfactory @elseif($companyAverage >= 50) Needs Improvement @else Unsatisfactory @endif</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-[20px] border border-slate-200 bg-white shadow-[0_12px_22px_rgba(15,23,42,0.05)]">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
                <h4 class="text-[1.15rem] font-bold tracking-[-0.02em] text-slate-800 md:text-[1.25rem]">Employee Performance Summary</h4>
                <form method="GET" action="{{ route('hr.dashboard') }}" class="flex w-full flex-wrap items-center gap-3 md:w-auto">
                    <input type="hidden" name="department" value="{{ $departmentId }}">
                    <input type="hidden" name="period" value="{{ $period }}">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search employees..." class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-[#f4c212] focus:outline-none focus:ring-2 focus:ring-[#f4c212]/20 md:w-56">
                    <button type="submit" class="rounded-xl bg-[#111111] px-3.5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#232a30]">Search</button>
                    @if($search)
                        <a href="{{ route('hr.dashboard', ['department' => $departmentId, 'period' => $period]) }}" class="text-xs font-medium text-slate-500 hover:text-red-500">Clear</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Department</th>
                            <th class="px-6 py-4 text-center whitespace-nowrap">Overall Rating</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($employeeSummaries as $row)
                            <tr class="bg-white transition hover:bg-slate-50">
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $row['employee']->name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ ucfirst($row['employee']->role) }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $row['employee']->department->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-center font-bold whitespace-nowrap
                                    @if($row['overallScore'] >= 90) text-emerald-600
                                    @elseif($row['overallScore'] >= 80) text-green-600
                                    @elseif($row['overallScore'] >= 70) text-[#111111]
                                    @elseif($row['overallScore'] >= 50) text-yellow-600
                                    @else text-red-600 @endif">
                                    {{ number_format($row['overallScore'], 0) }}%
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[0.7rem] font-semibold
                                        @if($row['overallScore'] >= 90) bg-emerald-100 text-emerald-700
                                        @elseif($row['overallScore'] >= 80) bg-green-100 text-green-700
                                        @elseif($row['overallScore'] >= 70) bg-[#f4c212]/20 text-[#111111]
                                        @elseif($row['overallScore'] >= 50) bg-yellow-100 text-yellow-700
                                        @else bg-red-100 text-red-700 @endif">
                                        @if($row['overallScore'] >= 90) Outstanding
                                        @elseif($row['overallScore'] >= 80) Very Satisfactory
                                        @elseif($row['overallScore'] >= 70) Satisfactory
                                        @elseif($row['overallScore'] >= 50) Needs Improvement
                                        @else Unsatisfactory @endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400">No employees found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const manilaClock = document.querySelector('[data-manila-clock]');

        if (manilaClock) {
            const manilaDate = manilaClock.querySelector('[data-manila-date]');
            const manilaTime = manilaClock.querySelector('[data-manila-time]');
            const manilaFormatter = new Intl.DateTimeFormat('en-PH', {
                timeZone: 'Asia/Manila',
                weekday: 'long',
                month: 'long',
                day: 'numeric',
                year: 'numeric',
            });
            const manilaTimeFormatter = new Intl.DateTimeFormat('en-PH', {
                timeZone: 'Asia/Manila',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
            });

            const updateManilaClock = () => {
                const currentTime = new Date();
                manilaDate.textContent = manilaFormatter.format(currentTime);
                manilaTime.textContent = manilaTimeFormatter.format(currentTime);
                manilaClock.setAttribute('datetime', currentTime.toISOString());
            };

            updateManilaClock();
            window.setInterval(updateManilaClock, 1000);
        }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script>
        const trendCanvas = document.getElementById('trendChart');
        if (trendCanvas && typeof Chart !== 'undefined') {
                    const datasets = {!! json_encode($trendDatasets) !!};
                    const colors = ['#23408e', '#16a34a', '#c98a00', '#e32929', '#7c3aed', '#159bb5'];

            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: {!! json_encode($trendLabels) !!},
                    datasets: Object.keys(datasets).map((name, i) => ({
                        label: name,
                        data: datasets[name],
                        borderColor: colors[i % colors.length],
                        backgroundColor: colors[i % colors.length],
                        tension: 0.35,
                        fill: false,
                        borderWidth: 2.5,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'nearest', intersect: false },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    align: 'center',
                                    labels: {
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        padding: 7,
                                        boxWidth: 8,
                                        boxHeight: 8,
                                        color: '#475569',
                                        font: { size: 10 }
                                    }
                                },
                                tooltip: { enabled: true }
                            },
                    scales: {
                        x: {
                                    grid: { color: '#e5e7eb', drawBorder: false },
                                    ticks: { color: '#64748b', font: { size: 12 } },
                                    border: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            max: 100,
                                    grid: { color: '#e5e7eb', drawBorder: false },
                                    ticks: { color: '#64748b', stepSize: 20, font: { size: 12 } },
                                    border: { display: false }
                        }
                    }
                }
            });
        }
    </script>
</x-layouts.dashboard>