<x-layouts.dashboard title="HR Log Attendance">
    <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800">Log Attendance</h3>
        <p class="text-gray-500 text-sm">{{ $employee->name }} — {{ $employee->department->name ?? 'No department' }}</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded-md mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Entry form -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h4 class="font-semibold text-gray-800 mb-4">Add / Update Daily Log</h4>
            <p class="mb-4 text-xs text-gray-500">Use 24-hour time (HH:MM). Scheduled: {{ \Carbon\Carbon::parse($settings->scheduled_time_in)->format('H:i') }} in, {{ \Carbon\Carbon::parse($settings->scheduled_time_out)->format('H:i') }} out. A later check-in is marked late.</p>
            <form action="{{ route('hr.attendance.store', $employee) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="date" value="{{ old('date') }}" class="w-full border-gray-300 rounded-md text-sm" required>
                    @error('date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-md text-sm" required>
                        <option value="present">Present</option>
                        <option value="late">Late</option>
                        <option value="absent">Absent</option>
                        <option value="excused">Excused</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Time In</label>
                        <input type="text" name="time_in" value="{{ old('time_in') }}" placeholder="HH:MM" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="Enter time in 24-hour HH:MM format, from 00:00 to 23:59." class="w-full rounded-md border-gray-300 text-sm">
                        @error('time_in') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Time Out</label>
                        <input type="text" name="time_out" value="{{ old('time_out') }}" placeholder="HH:MM" maxlength="5" pattern="([01][0-9]|2[0-3]):[0-5][0-9]" title="Enter time in 24-hour HH:MM format, from 00:00 to 23:59." class="w-full rounded-md border-gray-300 text-sm">
                        @error('time_out') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-blue-900 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-800">Save Log</button>
                    <a href="{{ route('hr.attendance.index') }}" class="px-4 py-2 rounded-md text-sm text-gray-600 hover:bg-gray-100">Back</a>
                </div>
            </form>
        </div>

        <!-- Recent logs -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
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
                    @forelse($logs as $log)
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
</x-layouts.dashboard>