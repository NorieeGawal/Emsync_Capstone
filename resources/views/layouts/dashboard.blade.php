<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('branding.company_name', 'EMSYNC') }} — {{ $title ?? 'Dashboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-950 text-white flex flex-col sticky top-0 h-screen overflow-y-auto">
            <div class="p-6 border-b border-blue-900">
                <h1 class="text-xl font-bold">{{ config('branding.company_name', 'EMSYNC') }}</h1>
                <p class="text-xs text-blue-300 mt-1">EMSYNC System</p>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1">
                @auth
                    @if(auth()->user()->role === 'hr')
                        <a href="{{ route('hr.dashboard') }}" class="block px-4 py-2 rounded hover:bg-blue-900">Dashboard</a>
                        <a href="{{ route('hr.employees.index') }}" class="block px-4 py-2 rounded hover:bg-blue-900">Employees</a>
                        <a href="{{ route('hr.kpi.bulk') }}" class="block px-4 py-2 rounded hover:bg-blue-900">KPI Management</a>
                    @elseif(auth()->user()->role === 'manager')
                        <a href="{{ route('manager.dashboard') }}" class="block px-4 py-2 rounded hover:bg-blue-900">Dashboard</a>
                    @elseif(auth()->user()->role === 'employee')
                        <a href="{{ route('employee.dashboard') }}" class="block px-4 py-2 rounded hover:bg-blue-900">Dashboard</a>
                    @endif
                @endauth
            </nav>

            <div class="p-4 border-t border-blue-900">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 rounded hover:bg-blue-900">Log out</button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col">
            <header class="bg-white shadow-sm px-8 py-4 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h2>
                <span class="text-sm text-gray-600">{{ auth()->user()->name ?? '' }}</span>
            </header>

            <main class="flex-1 p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>