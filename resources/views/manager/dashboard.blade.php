<x-layouts.dashboard title="Dashboard">
    <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800">Hi {{ $manager->name }}</h3>
        <p class="text-gray-500 text-sm">{{ $manager->department->name ?? 'No department' }} — Team performance overview for {{ \Carbon\Carbon::parse($period . '-01')->format('F Y') }}.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-sm text-gray-500">Team Members</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $employeeScores->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-sm text-gray-500">Department Average</p>
            <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($departmentAverage, 0) }}%</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-sm text-gray-500">Below Standard</p>
            <p class="text-3xl font-bold text-red-700 mt-2">{{ $employeeScores->where('overallScore', '<', 40)->count() }}</p>
        </div>
    </div>

            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-semibold text-gray-800">Department Performance Trend</h4>
            <p class="text-xs text-gray-500">Last 3 months</p>
        </div>
        <canvas id="teamChart" height="90"></canvas>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h4 class="font-semibold text-gray-800">Team Performance</h4>
        </div>
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3">Employee</th>
                    <th class="px-6 py-3">Overall Score</th>
                    <th class="px-6 py-3">Standing</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($employeeScores as $row)
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $row['employee']->name }}</td>
                        <td class="px-6 py-4">
                            <span class="font-semibold
                                @if($row['overallScore'] >= 85) text-green-700
                                @elseif($row['overallScore'] >= 70) text-blue-900
                                @elseif($row['overallScore'] >= 40) text-yellow-600
                                @else text-red-700 @endif">
                                {{ number_format($row['overallScore'], 0) }}%
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded text-xs
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
                        <td colspan="3" class="px-6 py-8 text-center text-gray-400">No employees in your department yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        });
    </script>
</x-layouts.dashboard>