<x-layouts.dashboard title="Manager Dashboard" header-subtitle="{{ $manager->department->name ?? 'No department' }} — Team performance overview for {{ \Carbon\Carbon::parse($period . '-01')->format('F Y') }}.">

    <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="rounded-lg bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm text-gray-500">Team Members</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $employeeScores->count() }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm text-gray-500">Department Average</p>
            <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($departmentAverage, 0) }}%</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm sm:p-5">
            <p class="text-sm text-gray-500">Below Standard</p>
            <p class="text-3xl font-bold text-red-700 mt-2">{{ $employeeScores->where('overallScore', '<', 40)->count() }}</p>
        </div>
    </div>

    <div class="mb-3 shrink-0 rounded-lg bg-white p-3 shadow-sm sm:p-4">
        <div class="mb-2 flex items-center justify-between">
            <h4 class="font-semibold text-gray-800">Department Performance Trend</h4>
            <p class="text-xs text-gray-500">Last 3 months</p>
        </div>
        <div class="relative h-28 sm:h-32">
            <canvas id="teamChart"></canvas>
        </div>
    </div>

    <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-[0_18px_38px_rgba(15,23,42,0.05)]">
        <div class="shrink-0 border-b border-slate-200 px-5 py-4">
            <h4 class="text-[1.15rem] font-bold tracking-[-0.02em] text-slate-800">Team Performance</h4>
        </div>
        <div class="scroll-surface min-h-0 flex-1 overflow-y-auto overflow-x-auto overscroll-contain">
            <table class="w-full min-w-[520px] text-left text-sm">
            <thead class="sticky top-0 z-10 bg-[#f8f9fb] text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500 shadow-[inset_0_-1px_0_rgba(148,163,184,0.22)]">
                <tr>
                    <th class="px-5 py-3.5">Employee</th>
                    <th class="px-5 py-3.5">Overall Score</th>
                    <th class="px-5 py-3.5">Standing</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($employeeScores as $row)
                    <tr class="group transition-colors hover:bg-[#fffdf3]">
                        <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $row['employee']->name }}</td>
                        <td class="px-5 py-3.5">
                            <span class="font-semibold
                                @if($row['overallScore'] >= 85) text-green-700
                                @elseif($row['overallScore'] >= 70) text-blue-900
                                @elseif($row['overallScore'] >= 40) text-yellow-600
                                @else text-red-700 @endif">
                                {{ number_format($row['overallScore'], 0) }}%
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-[0.7rem] font-semibold ring-1 ring-inset ring-black/[0.04]
                                @if($row['overallScore'] >= 85) bg-green-100 text-green-700
                                @elseif($row['overallScore'] >= 70) bg-blue-100 text-blue-700
                                @elseif($row['overallScore'] >= 40) bg-yellow-100 text-yellow-700
                                @else bg-red-100 text-red-700 @endif">
                                @if($row['overallScore'] >= 85) Excellent
                                @elseif($row['overallScore'] >= 70) Good
                                @elseif($row['overallScore'] >= 40) Needs Improvement
                                @else Below Standard @endif
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-10 text-center text-sm text-slate-400">No employees in your department yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('teamChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($trendLabels) !!},
                datasets: [{
                    label: 'Department Average',
                    data: {!! json_encode($trendScores) !!},
                    borderColor: '#1e3a8a',
                    backgroundColor: 'rgba(30, 58, 138, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#1e3a8a',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        });
    </script>
</x-layouts.dashboard>