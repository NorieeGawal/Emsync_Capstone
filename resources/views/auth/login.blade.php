<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log In — {{ config('branding.company_name', 'EMSYNC') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .login-brand-logo {
            filter: brightness(0);
        }
    </style>
</head>
<body class="min-h-screen bg-[#0a0d0f] font-sans text-slate-900 antialiased">
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden p-4 sm:p-8 lg:p-10">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(244,194,18,0.2),transparent_22%),radial-gradient(circle_at_bottom_right,_rgba(255,255,255,0.07),transparent_26%)]"></div>
        <div class="absolute left-1/2 top-1/2 h-[36rem] w-[36rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#f4c212]/10 blur-3xl animate-glow"></div>

        <div class="relative w-full max-w-[980px] overflow-hidden rounded-[26px] border border-white/5 shadow-[0_30px_90px_rgba(0,0,0,0.75)] animate-slide-up">
            <div class="flex flex-col lg:flex-row">
                <aside class="relative flex w-full flex-col justify-between overflow-hidden bg-[#F4C212] p-6 sm:p-7 lg:w-[45%] lg:p-8">
                    <div class="absolute -right-12 top-10 h-32 w-32 rounded-full bg-[#0a0d0f]/10 blur-2xl animate-glow"></div>
                    <div class="absolute bottom-0 left-0 h-24 w-24 rounded-full bg-[#0a0d0f]/10 blur-3xl"></div>

                    <div class="relative flex items-center gap-3 pt-1">
                        <img src="{{ asset(config('branding.logo_path', '/images/emsync_logo.png')) }}" alt="{{ config('branding.company_name', 'Tempco') }} Logo" class="brand-logo login-brand-logo h-auto w-[64px] drop-shadow-sm animate-float" />
                        <div class="leading-none">
                            <p class="text-[1.85rem] font-extrabold tracking-[-0.03em] text-[#0d1116]">{{ strtoupper(config('branding.company_name', 'Tempco')) }}</p>
                            <p class="mt-1 text-[0.58rem] font-semibold uppercase tracking-[0.18em] text-black/70">Tuna Exporters Multi-Purpose Cooperative</p>
                        </div>
                    </div>

                    <div class="relative mt-8 flex flex-col items-start justify-start text-left">
                        <h1 class="text-[2.9rem] font-black leading-[0.9] tracking-[-0.07em] text-[#0d1116]">EMSYNC SYSTEM</h1>
                        <p class="mt-3 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-white/90">Employee Performance Monitoring</p>

                        <p class="mt-4 max-w-[20rem] text-[0.8rem] leading-relaxed text-[#111111]/75">
                            Allows authorized users to manage employee performance, attendance, and productivity in one secure platform.
                        </p>
                    </div>

                    <div class="relative mt-8 text-[0.62rem] font-semibold uppercase tracking-[0.14em] text-black/70">
                        © 2026 EMSYNC. All rights reserved.
                    </div>
                </aside>

                <main class="relative w-full bg-[#F4F2EF] p-6 sm:p-7 lg:w-[55%] lg:p-8">
                    <div class="absolute -left-12 bottom-10 h-28 w-28 rounded-full bg-[#F4C212]/20 blur-3xl"></div>
                    <div class="mx-auto max-w-[400px] pt-10 lg:pt-12">
                        <div class="animate-slide-up">
                            <h2 class="text-[2.75rem] font-black leading-none tracking-[-0.06em] text-[#141414]">Welcome Back!</h2>
                            <p class="mt-3 text-[1rem] font-medium text-slate-500">Sign in to continue to your dashboard.</p>
                        </div>

                        <x-auth-session-status class="mt-6" :status="session('status')" />

                        @if ($errors->any())
                            <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-4">
                            @csrf

                            <div class="animate-slide-up" style="animation-delay: 80ms;">
                                <label for="email" class="mb-2 block text-[1rem] font-semibold tracking-[0.02em] text-slate-700">Email Address</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                                    class="w-full rounded-md border border-slate-300 bg-white px-4 py-3 text-[1rem] text-slate-800 placeholder:text-slate-400 transition focus:border-[#F4C212] focus:outline-none focus:ring-2 focus:ring-[#F4C212]/30" placeholder="Enter your email" />
                            </div>

                            <div x-data="{ showPassword: false }" class="animate-slide-up" style="animation-delay: 150ms;">
                                <label for="password" class="mb-2 block text-[1rem] font-semibold tracking-[0.02em] text-slate-700">Password</label>
                                <div class="relative">
                                    <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                        class="w-full rounded-md border border-slate-300 bg-white px-4 py-3 pr-12 text-[1rem] text-slate-800 placeholder:text-slate-400 transition focus:border-[#F4C212] focus:outline-none focus:ring-2 focus:ring-[#F4C212]/30" placeholder="Enter your password" />
                                    <button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? 'Hide password' : 'Show password'" class="absolute right-2 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#F4C212]" title="Show or hide password">
                                        <svg x-show="!showPassword" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
                                            <circle cx="12" cy="12" r="2.5" />
                                        </svg>
                                        <svg x-cloak x-show="showPassword" aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="m3 3 18 18" />
                                            <path d="M10.6 6.2A10.5 10.5 0 0 1 12 6c6 0 9.5 6 9.5 6a17.6 17.6 0 0 1-3.1 3.7M6.3 6.3C3.9 8 2.5 12 2.5 12s3.5 6 9.5 6c1.5 0 2.8-.4 4-1" />
                                            <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="animate-slide-up" style="animation-delay: 220ms;">
                                <label for="role" class="mb-2 block text-[1rem] font-semibold tracking-[0.02em] text-slate-700">Role</label>
                                <select id="role" name="role" required
                                    class="w-full rounded-md border border-slate-300 bg-white px-4 py-3 text-[1rem] text-slate-800 transition focus:border-[#F4C212] focus:outline-none focus:ring-2 focus:ring-[#F4C212]/30">
                                    <option value="">Select role</option>
                                    <option value="hr">HR Personnel</option>
                                    <option value="manager">Manager</option>
                                    <option value="employee">Employee</option>
                                </select>
                            </div>

                            <button type="submit" class="mt-4 w-full rounded-md bg-[#F4C212] px-5 py-3.5 text-[1.1rem] font-bold tracking-[0.02em] text-[#111111] shadow-[0_14px_30px_rgba(244,194,18,0.35)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#e9b809] animate-slide-up" style="animation-delay: 320ms;">
                                Sign In
                            </button>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </div>
</body>
</html>