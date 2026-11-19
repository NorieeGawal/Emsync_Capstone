<x-layouts.dashboard title="Manager Team" header-subtitle="Employees in your department.">

    <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="min-h-0 flex-1 overflow-y-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($employees as $employee)
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $employee->name }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $employee->email }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('manager.employees.show', $employee) }}" class="text-blue-700 hover:underline">View Profile</a>
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
        @if($employees->hasPages())
            <div class="shrink-0 border-t border-gray-100 px-4 py-3 sm:px-6">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</x-layouts.dashboard>