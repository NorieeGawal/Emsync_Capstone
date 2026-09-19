<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('branding.company_name', 'EMSYNC') }} — {{ $title ?? 'Dashboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dashboard-shell > * {
            animation: fadeInUp 0.4s ease-out both;
        }

        .dashboard-shell > *:nth-child(2) { animation-delay: 0.05s; }
        .dashboard-shell > *:nth-child(3) { animation-delay: 0.08s; }
        .dashboard-shell > *:nth-child(4) { animation-delay: 0.12s; }
        .dashboard-shell > *:nth-child(5) { animation-delay: 0.16s; }
    </style>
</head>
<body class="bg-[#f3f0ea] font-sans antialiased text-slate-800">
    <div x-data="{ mobileMenuOpen: false }" @keydown.escape.window="mobileMenuOpen = false" class="flex min-h-screen">
        <aside :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 flex h-screen w-[min(86vw,300px)] shrink-0 -translate-x-full flex-col overflow-y-auto bg-[#f4c212] text-[#111111] shadow-[0_12px_25px_rgba(0,0,0,0.12)] transition-transform duration-300 md:sticky md:top-0 md:z-auto md:w-[240px] md:translate-x-0 md:overflow-hidden xl:w-[250px]">
            <div class="flex items-center justify-between px-5 pb-5 pt-5 md:pt-6">
                <div class="flex items-center gap-3">
                    <img src="{{ asset(config('branding.logo_path', '/images/emsync_logo.png')) }}" alt="{{ config('branding.company_name', 'Tempco') }} Logo" class="h-[72px] w-[72px] object-contain bg-transparent p-0 shadow-none" />
                    <div class="leading-none">
                        <h1 class="text-[2rem] font-extrabold tracking-[-0.04em] text-[#111111]">Tempco</h1>
                        <p class="mt-1 text-[0.62rem] font-semibold uppercase tracking-[0.18em] text-[#111111]/75">Tuna Exporters Multi-Purpose Cooperative</p>
                    </div>
                </div>

                <button type="button" @click="mobileMenuOpen = false" aria-label="Close navigation menu" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-2xl leading-none text-[#111111] transition hover:bg-black/5 md:hidden">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <nav class="flex-1 space-y-1 px-4 py-4">
                @auth
                    @if(auth()->user()->role === 'hr')
                        <a href="{{ route('hr.dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-[1.02rem] font-medium transition hover:bg-black/5 {{ request()->routeIs('hr.dashboard') ? 'bg-[#111111] text-white shadow-inner' : 'text-[#111111]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                                <rect x="14" y="3" width="7" height="4" rx="1.5"></rect>
                                <rect x="14" y="11" width="7" height="10" rx="1.5"></rect>
                                <rect x="3" y="12" width="7" height="9" rx="1.5"></rect>
                            </svg>
                            Dashboard
                        </a>
                        <a href="{{ route('hr.departments.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-[1.02rem] font-medium transition hover:bg-black/5 {{ request()->routeIs('hr.departments.*') ? 'bg-[#111111] text-white shadow-inner' : 'text-[#111111]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 21V7.5A1.5 1.5 0 0 1 4.5 6H9l2-3h7.5A1.5 1.5 0 0 1 20 4.5V21"></path>
                                <path d="M7 21v-7h10v7"></path>
                                <path d="M10 11h4"></path>
                            </svg>
                            Departments
                        </a>
                        <a href="{{ route('hr.employees.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-[1.02rem] font-medium transition hover:bg-black/5 {{ request()->routeIs('hr.employees.*') ? 'bg-[#111111] text-white shadow-inner' : 'text-[#111111]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1"></path>
                                <circle cx="10" cy="7" r="3.5"></circle>
                                <path d="M20 19v-1a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 4.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            Employees
                        </a>
                        <a href="{{ route('hr.kpi.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-[1.02rem] font-medium transition hover:bg-black/5 {{ request()->routeIs('hr.kpi.*') ? 'bg-[#111111] text-white shadow-inner' : 'text-[#111111]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 19h16"></path>
                                <path d="M7 16V9"></path>
                                <path d="M12 16V5"></path>
                                <path d="M17 16v-7"></path>
                            </svg>
                            KPI Management
                        </a>
                        <a href="{{ route('hr.attendance.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-[1.02rem] font-medium transition hover:bg-black/5 {{ request()->routeIs('hr.attendance.*') ? 'bg-[#111111] text-white shadow-inner' : 'text-[#111111]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="8"></circle>
                                <path d="M12 7v5l3 2"></path>
                            </svg>
                            Attendance
                        </a>
                    @elseif(auth()->user()->role === 'manager')
                        <a href="{{ route('manager.dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-[1.02rem] font-medium transition hover:bg-black/5 {{ request()->routeIs('manager.dashboard') ? 'bg-[#111111] text-white shadow-inner' : 'text-[#111111]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                                <rect x="14" y="3" width="7" height="4" rx="1.5"></rect>
                                <rect x="14" y="11" width="7" height="10" rx="1.5"></rect>
                                <rect x="3" y="12" width="7" height="9" rx="1.5"></rect>
                            </svg>
                            Dashboard
                        </a>
                        <a href="{{ route('manager.employees.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-[1.02rem] font-medium transition hover:bg-black/5 {{ request()->routeIs('manager.employees.*') ? 'bg-[#111111] text-white shadow-inner' : 'text-[#111111]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1"></path>
                                <circle cx="10" cy="7" r="3.5"></circle>
                                <path d="M20 19v-1a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 4.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            My Team
                        </a>
                    @elseif(auth()->user()->role === 'employee')
                        <a href="{{ route('employee.dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-[1.02rem] font-medium transition hover:bg-black/5 {{ request()->routeIs('employee.dashboard') ? 'bg-[#111111] text-white shadow-inner' : 'text-[#111111]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                                <rect x="14" y="3" width="7" height="4" rx="1.5"></rect>
                                <rect x="14" y="11" width="7" height="10" rx="1.5"></rect>
                                <rect x="3" y="12" width="7" height="9" rx="1.5"></rect>
                            </svg>
                            Dashboard
                        </a>
                    @endif
                @endauth
            </nav>

            <div class="mt-auto space-y-2 border-t border-black/10 p-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl bg-[#111111] px-4 py-3 text-left text-[1.02rem] font-medium text-white transition hover:bg-[#2b2b2b] focus:outline-none focus:ring-2 focus:ring-white/70 focus:ring-offset-2 focus:ring-offset-[#f4c212]">
                        Log out
                    </button>
                </form>
            </div>
        </aside>

        <div x-cloak x-show="mobileMenuOpen" x-transition.opacity @click="mobileMenuOpen = false" class="pointer-events-auto fixed inset-0 z-30 bg-black/40 md:hidden" aria-hidden="true"></div>

        <div class="flex min-w-0 flex-1 flex-col bg-[#eceae6]">
            <header class="relative z-50 border-b border-slate-200 bg-[#f8f7f5] px-4 py-1.5 shadow-[0_8px_20px_rgba(15,23,42,0.03)] sm:px-6 sm:py-1.5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <button type="button" @click.stop="mobileMenuOpen = true" aria-label="Open navigation menu" class="relative z-50 flex h-9 w-9 shrink-0 cursor-pointer touch-manipulation items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-slate-300 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#f4c212] focus:ring-offset-2 md:hidden">
                            <span class="flex flex-col gap-1" aria-hidden="true">
                                <span class="h-0.5 w-5 rounded-full bg-current"></span>
                                <span class="h-0.5 w-5 rounded-full bg-current"></span>
                                <span class="h-0.5 w-5 rounded-full bg-current"></span>
                            </span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2 self-end sm:gap-3 sm:self-auto">
                        <button type="button" aria-label="Notifications" class="hidden h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-800 sm:flex">◌</button>
                        <div class="flex max-w-[min(58vw,240px)] items-center gap-2 rounded-full border border-slate-200 bg-white px-2 py-0.5 shadow-sm sm:gap-2 sm:px-2.5 sm:py-1">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#f4c212] text-xs font-black text-[#111111]">{{ Str::upper(Str::substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                            <span class="truncate text-sm font-semibold text-slate-700">{{ auth()->user()->name ?? 'Admin' }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="dashboard-shell min-w-0 flex-1 p-3 sm:p-5 lg:p-7">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>