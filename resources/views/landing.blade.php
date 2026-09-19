<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('branding.company_name', 'EMSYNC') }} | Performance System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0a0d0f] text-white antialiased">
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(244,194,18,0.18),transparent_24%),radial-gradient(circle_at_bottom_right,_rgba(255,255,255,0.08),transparent_24%)]"></div>

        <header class="relative z-10 mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-between rounded-full border border-white/10 bg-white/5 px-4 py-3 backdrop-blur-sm shadow-[0_0_30px_rgba(0,0,0,0.2)]">
                <div class="flex items-center gap-3">
                    <img src="{{ asset(config('branding.logo_path', '/images/emsync_logo.png')) }}" alt="{{ config('branding.company_name', 'Tempco') }} Logo" class="w-[56px] h-auto" />
                    <div class="leading-none">
                        <p class="text-lg font-black tracking-[0.18em] text-[#F4C212]">{{ strtoupper(config('branding.company_name', 'Tempco')) }}</p>
                        <p class="mt-1 text-[0.5rem] font-semibold uppercase tracking-[0.24em] text-white/70">Tuna Exporters Multi-Purpose Cooperative</p>
                    </div>
                </div>

                <div class="hidden items-center gap-8 text-sm text-white/75 md:flex">
                    <a href="#features" class="transition hover:text-[#F4C212]">Features</a>
                    <a href="#solutions" class="transition hover:text-[#F4C212]">Solutions</a>
                    <a href="#about" class="transition hover:text-[#F4C212]">About</a>
                </div>

                <a href="{{ route('login') }}" class="rounded-full bg-[#F4C212] px-5 py-2.5 text-sm font-bold text-[#0f1114] shadow-[0_15px_30px_rgba(244,194,18,0.35)] transition hover:bg-[#f5c93b]">
                    Sign In
                </a>
            </nav>
        </header>

        <main class="relative z-10 mx-auto max-w-7xl px-4 pb-20 pt-8 sm:px-6 lg:px-8 lg:pb-28 lg:pt-12">
            <section class="grid items-center gap-12 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="animate-slide-up">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-[#F4C212]/40 bg-[#F4C212]/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#F4C212]">
                        <span class="h-2 w-2 rounded-full bg-[#F4C212] animate-pulse"></span>
                        Smart workforce analytics
                    </div>

                    <h1 class="max-w-xl text-5xl font-black leading-[0.95] tracking-[-0.06em] text-white sm:text-6xl lg:text-7xl">
                        Performance that moves your team forward.
                    </h1>

                    <p class="mt-6 max-w-xl text-lg leading-8 text-white/70">
                        EMSYNC helps organizations monitor attendance, employee output, and key performance metrics in one secure and intelligent system.
                    </p>

                    <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full bg-[#F4C212] px-6 py-3.5 text-sm font-bold text-[#111111] shadow-[0_18px_40px_rgba(244,194,18,0.35)] transition hover:-translate-y-0.5 hover:bg-[#f5c93b]">
                            Access Dashboard
                        </a>
                        <a href="#features" class="inline-flex items-center justify-center rounded-full border border-white/15 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white transition hover:border-[#F4C212]/50 hover:text-[#F4C212]">
                            Explore Features
                        </a>
                    </div>

                    <div class="mt-10 flex flex-wrap items-center gap-8 text-sm text-white/60">
                        <div>
                            <p class="text-2xl font-black text-white">99.9%</p>
                            <p class="mt-1 uppercase tracking-[0.18em] text-white/50">Uptime</p>
                        </div>
                        <div>
                            <p class="text-2xl font-black text-white">2.4k+</p>
                            <p class="mt-1 uppercase tracking-[0.18em] text-white/50">Employees</p>
                        </div>
                        <div>
                            <p class="text-2xl font-black text-white">24/7</p>
                            <p class="mt-1 uppercase tracking-[0.18em] text-white/50">Monitoring</p>
                        </div>
                    </div>
                </div>

                <div class="relative animate-float">
                    <div class="absolute -left-6 top-16 h-28 w-28 rounded-full bg-[#F4C212]/25 blur-3xl"></div>
                    <div class="absolute -right-8 bottom-12 h-32 w-32 rounded-full bg-white/10 blur-3xl"></div>

                    <div class="relative overflow-hidden rounded-[28px] border border-white/10 bg-[#111618] p-4 shadow-[0_30px_80px_rgba(0,0,0,0.55)]">
                        <div class="rounded-[22px] border border-white/5 bg-[#171b1d] p-4">
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.2em] text-white/50">Overview</p>
                                    <p class="mt-1 text-xl font-bold text-white">Performance Dashboard</p>
                                </div>
                                <div class="rounded-full bg-[#F4C212] px-2.5 py-1 text-[0.62rem] font-bold uppercase tracking-[0.18em] text-[#121212]">
                                    Live
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div class="rounded-2xl border border-white/8 bg-[#1d2326] p-3">
                                    <p class="text-[0.6rem] uppercase tracking-[0.18em] text-white/50">Attendance</p>
                                    <p class="mt-2 text-2xl font-black text-[#F4C212]">94%</p>
                                </div>
                                <div class="rounded-2xl border border-white/8 bg-[#1d2326] p-3">
                                    <p class="text-[0.6rem] uppercase tracking-[0.18em] text-white/50">KPI</p>
                                    <p class="mt-2 text-2xl font-black text-[#F4C212]">87%</p>
                                </div>
                                <div class="rounded-2xl border border-white/8 bg-[#1d2326] p-3">
                                    <p class="text-[0.6rem] uppercase tracking-[0.18em] text-white/50">Teams</p>
                                    <p class="mt-2 text-2xl font-black text-[#F4C212]">19</p>
                                </div>
                            </div>

                            <div class="mt-5 rounded-2xl border border-white/8 bg-[#1d2326] p-4">
                                <div class="mb-4 flex items-center justify-between text-xs uppercase tracking-[0.18em] text-white/50">
                                    <span>Monthly trend</span>
                                    <span class="text-[#F4C212]">+18.4%</span>
                                </div>
                                <div class="flex h-28 items-end gap-2">
                                    <span class="w-full rounded-t-xl bg-[#F4C212]/70" style="height: 36%"></span>
                                    <span class="w-full rounded-t-xl bg-[#F4C212]/80" style="height: 52%"></span>
                                    <span class="w-full rounded-t-xl bg-[#F4C212]/90" style="height: 44%"></span>
                                    <span class="w-full rounded-t-xl bg-[#F4C212]" style="height: 68%"></span>
                                    <span class="w-full rounded-t-xl bg-[#F4C212]/90" style="height: 76%"></span>
                                    <span class="w-full rounded-t-xl bg-[#F4C212]/90" style="height: 92%"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="mt-24">
                <div class="mb-10 text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-[#F4C212]">Core features</p>
                    <h2 class="mt-4 text-3xl font-black tracking-[-0.05em] text-white sm:text-4xl">Built for modern workforce management</h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div class="group rounded-[26px] border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-[#F4C212]/50 hover:bg-white/8">
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F4C212] text-lg font-black text-[#101214]">A</div>
                        <h3 class="text-xl font-bold text-white">Attendance</h3>
                        <p class="mt-3 text-white/65">Track presence, tardiness, absences, and daily records in a faster batch workflow.</p>
                    </div>

                    <div class="group rounded-[26px] border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-[#F4C212]/50 hover:bg-white/8">
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F4C212] text-lg font-black text-[#101214]">K</div>
                        <h3 class="text-xl font-bold text-white">KPI Monitoring</h3>
                        <p class="mt-3 text-white/65">Measure team performance with clear goals, progress tracking, and role-based insights.</p>
                    </div>

                    <div class="group rounded-[26px] border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-[#F4C212]/50 hover:bg-white/8">
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F4C212] text-lg font-black text-[#101214]">R</div>
                        <h3 class="text-xl font-bold text-white">Reports</h3>
                        <p class="mt-3 text-white/65">Deliver actionable summaries for HR, managers, and employees using clean reporting views.</p>
                    </div>
                </div>
            </section>

            <section id="solutions" class="mt-24 rounded-[30px] border border-white/10 bg-white/5 p-8 backdrop-blur-sm">
                <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.26em] text-[#F4C212]">Why EMSYNC</p>
                        <h3 class="mt-4 text-3xl font-black tracking-[-0.05em] text-white">Built for accountability, clarity, and performance.</h3>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-[#111618] p-5">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#F4C212]">Fast</p>
                            <p class="mt-3 text-white/70">Batch attendance updates and team visibility without redundant work.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-[#111618] p-5">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#F4C212]">Secure</p>
                            <p class="mt-3 text-white/70">Authentic role-based access keeps every dashboard protected and relevant.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-[#111618] p-5">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#F4C212]">Accurate</p>
                            <p class="mt-3 text-white/70">Real-time performance tracking ensures reporting is timely and dependable.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-[#111618] p-5">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#F4C212]">Clear</p>
                            <p class="mt-3 text-white/70">Simple dashboards present employee health, attendance, and outcomes at a glance.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
