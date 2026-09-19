<x-layouts.dashboard title="Encode KPI Scores">
    <div class="mb-6 flex items-end justify-between gap-3">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">Encode KPI Scores</h3>
            <p class="text-sm text-gray-500">{{ $employee->name }} {{ $employee->department->name ?? 'No department' }}</p>
        </div>
        <a href="{{ route('hr.kpi.index') }}" class="text-sm text-blue-700 hover:underline">Back to KPI management</a>
    </div>

    <div class="max-w-2xl rounded-lg bg-white p-6 shadow-sm">
        <form action="{{ route('hr.kpi.store', $employee) }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="mb-1 block text-sm font-medium text-gray-700">Period</label>
                <input type="month" name="period" value="{{ old('period', now()->format('Y-m')) }}" class="w-full rounded-md border-gray-300 text-sm" required>
                @error('period') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6 space-y-4">
                @foreach($indicators as $indicator)
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">{{ $indicator->name }} (0–100)</label>
                        <input type="number" step="0.01" min="0" max="100" name="scores[{{ $indicator->id }}]" value="{{ old('scores.' . $indicator->id) }}" class="w-full rounded-md border-gray-300 text-sm" required>
                        @error('scores.' . $indicator->id) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>

            <div class="mb-6">
                <label class="mb-1 block text-sm font-medium text-gray-700">Remarks (optional)</label>
                <textarea name="remarks" rows="3" class="w-full rounded-md border-gray-300 text-sm">{{ old('remarks') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-blue-900 px-4 py-2 text-sm text-white hover:bg-blue-800">Save Scores</button>
                <a href="{{ route('hr.kpi.index') }}" class="rounded-md px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
