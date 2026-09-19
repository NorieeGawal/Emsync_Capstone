<x-layouts.dashboard title="Employee Profile">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[0.62rem] font-semibold uppercase tracking-[0.2em] text-slate-500">Employee Profile</p>
            <h3 class="mt-1 text-2xl font-bold tracking-[-0.03em] text-slate-800">{{ $employee->name }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $employee->department->name ?? 'No department' }} — {{ ucfirst($employee->role) }} — {{ \Carbon\Carbon::parse($period . '-01')->format('F Y') }}</p>
        </div>
            <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('hr.reports.employee', $employee) }}" class="rounded-xl bg-[#111111] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#232a30]">Download PDF</a>
            <a href="{{ route('hr.employees.index') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Back to Directory</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <!-- KPI Cards -->
    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
        @foreach($scores as $name => $score)
            <div class="rounded-[16px] border border-slate-200 bg-white p-4 shadow-[0_8px_18px_rgba(15,23,42,0.04)]">
                <p class="text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-slate-500">{{ $name }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ number_format($score, 0) }}%</p>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-[#f4c212]" style="width: {{ min($score, 100) }}%"></div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <!-- Overall score -->
        <div class="flex flex-col items-center justify-center rounded-[20px] border border-slate-200 bg-white p-6 shadow-[0_10px_22px_rgba(15,23,42,0.05)]">
            <p class="mb-4 text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-slate-500">Overall Rating</p>
            <div class="w-32 h-32 rounded-full border-8 flex items-center justify-center
                @if($overallScore >= 85) border-green-600
                @elseif($overallScore >= 70) border-[#232a30]
                @elseif($overallScore >= 40) border-[#f4c212]
                @else border-red-600 @endif">
                <span class="text-3xl font-bold
                    @if($overallScore >= 85) text-green-700
                    @elseif($overallScore >= 70) text-[#232a30]
                    @elseif($overallScore >= 40) text-yellow-700
                    @else text-red-700 @endif">{{ number_format($overallScore, 0) }}%</span>
            </div>
            <span class="mt-4 rounded-full px-3 py-1 text-xs font-semibold
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
        <div class="overflow-hidden rounded-[20px] border border-slate-200 bg-white shadow-[0_10px_22px_rgba(15,23,42,0.05)] lg:col-span-2">
            <div class="border-b border-slate-200 px-5 py-4">
                <h4 class="font-bold text-slate-800">Recent Attendance</h4>
            </div>
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">In</th>
                        <th class="px-5 py-3">Out</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentLogs as $log)
                        <tr>
                            <td class="px-5 py-3 text-slate-700">{{ \Carbon\Carbon::parse($log->date)->format('M d') }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-xs
                                    @if($log->status === 'present') bg-green-100 text-green-700
                                    @elseif($log->status === 'late') bg-yellow-100 text-yellow-700
                                    @elseif($log->status === 'absent') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $log->time_in ? \Carbon\Carbon::parse($log->time_in)->format('h:i A') : '—' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $log->time_out ? \Carbon\Carbon::parse($log->time_out)->format('h:i A') : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-400">No attendance logs yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Notes -->
    <div class="rounded-[20px] border border-slate-200 bg-white p-5 shadow-[0_10px_22px_rgba(15,23,42,0.05)] sm:p-6">
        <h4 class="mb-4 font-bold text-slate-800">Performance Feedback</h4>

        <form action="{{ route('hr.employees.notes.store', $employee) }}" method="POST" class="mb-6">
            @csrf
            <textarea name="note" rows="3" placeholder="Add a note about this employee's performance..." class="w-full rounded-xl border-slate-300 bg-slate-50 text-sm focus:border-[#f4c212] focus:ring-[#f4c212]" required></textarea>
            @error('note') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            <button type="submit" class="mt-2 rounded-xl bg-[#111111] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#232a30]">Add Feedback</button>
        </form>

            <div class="space-y-4 max-h-80 overflow-y-auto pr-2">
            @forelse($notes as $note)
                <div class="rounded-xl border-l-4 border-[#f4c212] bg-slate-50 px-4 py-3">
                    <p class="text-sm text-slate-800">{{ $note->note }}</p>
                    <p class="mt-2 text-xs text-slate-500">— {{ $note->author->name }} ({{ ucfirst($note->author->role) }}), {{ $note->created_at->format('M d, Y g:i A') }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-400">No notes yet.</p>
            @endforelse
        </div>
    </div>
</x-layouts.dashboard>