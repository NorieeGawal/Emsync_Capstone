<x-layouts.dashboard title="Dashboard">
    <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800">Hi {{ $employee->name }}</h3>
        <p class="text-gray-500 text-sm">Welcome to EMSYNC. Here's your performance dashboard for {{ \Carbon\Carbon::parse($period . '-01')->format('F Y') }}.</p>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        @foreach($scores as $name => $score)
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $name }}</p>
                <p class="text-2xl font-bold text-blue-900 mt-1">{{ number_format($score, 0) }}%</p>
                <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                    <div class="bg-blue-700 h-1.5 rounded-full" style="width: {{ min($score, 100) }}%"></div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Trend chart -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-semibold text-gray-800">Overall Score Trend</h4>
                <p class="text-xs text-gray-500">Last 3 months</p>
            </div>
            <canvas id="trendChart" height="100"></canvas>
        </div>

        <!-- Overall score ring -->
        <div class="bg-white rounded-lg shadow-sm p-6 flex flex-col items-center justify-center">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-4">Overall Rating</p>
            <div class="w-32 h-32 rounded-full border-8 flex flex-col items-center justify-center
                @if($overallScore >= 90) border-green-600
                @elseif($overallScore >= 80) border-emerald-600
                @elseif($overallScore >= 70) border-blue-700
                @elseif($overallScore >= 50) border-yellow-500
                @else border-red-600 @endif">
                <span class="text-3xl font-bold
                    @if($overallScore >= 90) text-green-700
                    @elseif($overallScore >= 80) text-emerald-700
                    @elseif($overallScore >= 70) text-blue-900
                    @elseif($overallScore >= 50) text-yellow-600
                    @else text-red-700 @endif">{{ number_format($overallScore, 0) }}%</span>
                <span class="text-xs text-gray-500">Average Rating</span>
            </div>
            <span class="mt-4 px-3 py-1 rounded-full text-xs font-medium
                @if($overallScore >= 90) bg-green-100 text-green-700
                @elseif($overallScore >= 80) bg-emerald-100 text-emerald-700
                @elseif($overallScore >= 70) bg-blue-100 text-blue-700
                @elseif($overallScore >= 50) bg-yellow-100 text-yellow-700
                @else bg-red-100 text-red-700 @endif">
                @if($overallScore >= 90) Outstanding
                @elseif($overallScore >= 80) Very Satisfactory
                @elseif($overallScore >= 70) Satisfactory
                @elseif($overallScore >= 50) Needs Improvement
                @else Unsatisfactory @endif
            </span>
            <p class="text-xs text-gray-500 text-center mt-3 px-2">
                @if($overallScore >= 90)
                    Consistently exceeds expectations across most performance indicators.
                @elseif($overallScore >= 80)
                    Performs very well, meeting expectations with strong consistency.
                @elseif($overallScore >= 70)
                    Meets expected performance standards, with room for growth in some areas.
                @elseif($overallScore >= 50)
                    Performance is below target in several areas and would benefit from improvement.
                @else
                    Does not meet the expected performance standards and requires significant improvement.
                @endif
            </p>
        </div>
    </div>

        <!-- Performance feedback -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6 mt-6">
        <h4 class="font-semibold text-gray-800 mb-4">Performance Feedback</h4>
        <div class="space-y-4 max-h-80 overflow-y-auto pr-2">
            @forelse($notes as $note)
                <div class="border-l-4 border-blue-700 bg-gray-50 px-4 py-3 rounded">
                    <p class="text-sm text-gray-800">{{ $note->note }}</p>
                    <p class="text-xs text-gray-500 mt-2">— {{ $note->author->name }} ({{ ucfirst($note->author->role) }}), {{ $note->created_at->format('M d, Y g:i A') }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-400">No feedback yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Recent attendance -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mt-6">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h4 class="font-semibold text-gray-800">Recent Attendance</h4>
        </div>
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3">Time In</th>
                    <th class="px-6 py-3">Time Out</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recentLogs as $log)
                    <tr>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($log->date)->format('M d') }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $log->time_in ? \Carbon\Carbon::parse($log->time_in)->format('h:i A') : '—' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $log->time_out ? \Carbon\Carbon::parse($log->time_out)->format('h:i A') : '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded text-xs
                                @if($log->status === 'present') bg-green-100 text-green-700
                                @elseif($log->status === 'late') bg-yellow-100 text-yellow-700
                                @elseif($log->status === 'absent') bg-red-100 text-red-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">No attendance logs yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('trendChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($trendLabels) !!},
                datasets: [{
                    label: 'Overall Score',
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