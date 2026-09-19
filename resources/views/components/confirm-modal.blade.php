@props(['id', 'title' => 'Are you sure?', 'message' => 'This action cannot be undone.'])

<div id="{{ $id }}" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $title }}</h3>
        <p class="text-sm text-gray-500 mb-6">{{ $message }}</p>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('{{ $id }}').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md">Cancel</button>
            <button type="button" id="{{ $id }}-confirm" class="px-4 py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700">Delete</button>
        </div>
    </div>
</div>