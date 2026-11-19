<x-layouts.dashboard title="Manager Employee Profile">
    <div class="mb-6 flex justify-between items-start">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">{{ $employee->name }}</h3>
            <p class="text-gray-500 text-sm">{{ $employee->department->name ?? 'No department' }} — {{ \Carbon\Carbon::parse($period . '-01')->format('F Y') }}</p>
        </div>
        <a href="{{ route('manager.employees.index') }}" class="text-blue-700 text-sm hover:underline">← Back to Team</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded-md mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        @foreach($scores as $name => $score)
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $name }}</p>
                <p class="text-2xl font-bold text-blue-900 mt-1">{{ number_format($score, 0) }}%</p>
                @if($name === 'Productivity')
                    <p class="mt-1 text-xs text-gray-500">
                        @if($productivityRecord?->workload_target)
                            {{ number_format($productivityRecord->workload_completed) }} completed of {{ number_format($productivityRecord->workload_target) }} target
                        @else
                            Workload details not recorded
                        @endif
                    </p>
                @endif
                <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                    <div class="bg-blue-700 h-1.5 rounded-full" style="width: {{ min($score, 100) }}%"></div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Overall score -->
        <div class="bg-white rounded-lg shadow-sm p-6 flex flex-col items-center justify-center">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-4">Overall Score</p>
            <div class="w-32 h-32 rounded-full border-8 flex items-center justify-center
                @if($overallScore >= 85) border-green-600
                @elseif($overallScore >= 70) border-blue-700
                @elseif($overallScore >= 40) border-yellow-500
                @else border-red-600 @endif">
                <span class="text-3xl font-bold
                    @if($overallScore >= 85) text-green-700
                    @elseif($overallScore >= 70) text-blue-900
                    @elseif($overallScore >= 40) text-yellow-600
                    @else text-red-700 @endif">{{ number_format($overallScore, 0) }}%</span>
            </div>
            <span class="mt-4 px-3 py-1 rounded-full text-xs font-medium
                @if($overallScore >= 85) bg-green-100 text-green-700
                @elseif($overallScore >= 70) bg-blue-100 text-blue-700
                @elseif($overallScore >= 40) bg-yellow-100 text-yellow-700
                @else bg-red-100 text-red-700 @endif">
                @if($overallScore >= 85) Excellent Standing
                @elseif($overallScore >= 70) Good Standing
                @elseif($overallScore >= 40) Needs Improvement
                @else Below Standard @endif
            </span>
        </div>

        <!-- Recent attendance -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h4 class="font-semibold text-gray-800">Recent Attendance</h4>
            </div>
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">In</th>
                        <th class="px-4 py-2">Out</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentLogs as $log)
                        <tr>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($log->date)->format('M d') }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded text-xs
                                    @if($log->status === 'present') bg-green-100 text-green-700
                                    @elseif($log->status === 'late') bg-yellow-100 text-yellow-700
                                    @elseif($log->status === 'absent') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-gray-600">{{ $log->time_in ? \Carbon\Carbon::parse($log->time_in)->format('H:i') : '—' }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $log->time_out ? \Carbon\Carbon::parse($log->time_out)->format('H:i') : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400">No attendance logs yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Feedback -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h4 class="font-semibold text-gray-800 mb-4">Performance Feedback</h4>

        <form action="{{ route('manager.employees.notes.store', $employee) }}" method="POST" class="mb-6">
            @csrf
            <textarea name="note" rows="3" placeholder="Add feedback about this employee's performance..." class="w-full border-gray-300 rounded-md text-sm" required></textarea>
            @error('note') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            <button type="submit" class="mt-2 bg-blue-900 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-800">Add Feedback</button>
        </form>

            <div class="space-y-4 max-h-80 overflow-y-auto pr-2">
            @forelse($notes as $note)
                <div class="border-l-4 border-blue-700 bg-gray-50 px-4 py-3 rounded">
                    <p class="text-sm text-gray-800">{{ $note->note }}</p>
                    <p class="text-xs text-gray-500 mt-2">— {{ $note->author->name }} ({{ ucfirst($note->author->role) }}), {{ $note->created_at->format('M d, Y H:i') }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-400">No feedback yet.</p>
            @endforelse
        </div>
    </div>
</x-layouts.dashboard>