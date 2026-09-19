<x-layouts.dashboard title="My Team">
    <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800">My Team</h3>
        <p class="text-gray-500 text-sm">Employees in your department.</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
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

    <div class="mt-4">
        {{ $employees->links() }}
    </div>
</x-layouts.dashboard>