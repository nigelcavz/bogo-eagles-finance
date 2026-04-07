<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', "Bogo Eagle's Club Finance Tracker") }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
        <div class="relative isolate overflow-hidden bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.16),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(250,204,21,0.18),_transparent_24%),linear-gradient(180deg,_#020617_0%,_#0b1120_45%,_#111827_100%)]">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-sky-400/60 to-transparent"></div>
            <div class="absolute inset-y-0 left-0 w-64 bg-[radial-gradient(circle_at_left,_rgba(250,204,21,0.10),_transparent_70%)]"></div>
            <div class="absolute right-0 top-20 h-72 w-72 rounded-full bg-sky-400/10 blur-3xl"></div>

            <header class="relative z-10">
                <div class="mx-auto flex max-w-7xl items-center px-6 py-6 lg:px-8">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-amber-400/25 bg-slate-900/80 shadow-lg shadow-slate-950/40 ring-1 ring-sky-400/10">
                            <img src="{{ asset('images/logo.png') }}" alt="Bogo Eagles Club Logo" class="h-10 w-10 object-contain" />
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-300/90">Cebu North Bogo Eagles Club</p>
                            <h1 class="text-sm font-semibold text-slate-100 sm:text-base">Finance Tracker</h1>
                        </div>
                    </div>
                </div>
            </header>

            <main class="relative z-10">
                <div class="mx-auto grid max-w-7xl gap-12 px-6 pb-16 pt-10 lg:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)] lg:items-center lg:px-8 lg:pb-24 lg:pt-16">
                    <section class="max-w-3xl">
                        <div class="inline-flex items-center rounded-full border border-sky-400/20 bg-slate-900/70 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.3em] text-sky-200/90 shadow-sm shadow-slate-950/40">
                            Service Through Strong Brotherhood
                        </div>

                        <h2 class="mt-8 max-w-4xl text-4xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl">
                            Club finance records that feel clear, secure, and easy to trust.
                        </h2>

                        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                            Track member contributions, record expenses responsibly, and give officers and members a cleaner view of how club funds are managed.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-sky-400/35 bg-sky-400/15 px-6 py-3.5 text-sm font-semibold text-sky-50 shadow-xl shadow-sky-950/20 transition duration-200 ease-in-out hover:border-sky-300/50 hover:bg-sky-400/25">
                                    {{ __('Open Dashboard') }}
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-2xl border border-sky-400/35 bg-sky-400/15 px-6 py-3.5 text-sm font-semibold text-sky-50 shadow-xl shadow-sky-950/20 transition duration-200 ease-in-out hover:border-sky-300/50 hover:bg-sky-400/25">
                                    {{ __('Log in') }}
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-700 bg-slate-900/80 px-6 py-3.5 text-sm font-semibold text-slate-100 transition duration-200 ease-in-out hover:border-amber-300/40 hover:bg-slate-800/90 hover:text-amber-100">
                                        {{ __('Register for an account') }}
                                    </a>
                                @endif
                            @endauth
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-2xl border border-slate-800 bg-slate-900/65 p-4 shadow-lg shadow-slate-950/30 ring-1 ring-white/5 backdrop-blur-sm">
                                <p class="text-sm font-semibold text-white">Member Contributions</p>
                                <p class="mt-2 text-sm leading-6 text-slate-400">Record and review member payments with cleaner category-based tracking.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-800 bg-slate-900/65 p-4 shadow-lg shadow-slate-950/30 ring-1 ring-white/5 backdrop-blur-sm">
                                <p class="text-sm font-semibold text-white">Expense Visibility</p>
                                <p class="mt-2 text-sm leading-6 text-slate-400">Keep spending records organized with clear purpose, date, and reference details.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-800 bg-slate-900/65 p-4 shadow-lg shadow-slate-950/30 ring-1 ring-white/5 backdrop-blur-sm">
                                <p class="text-sm font-semibold text-white">Trusted Reporting</p>
                                <p class="mt-2 text-sm leading-6 text-slate-400">Support transparent summaries that officers and members can understand quickly.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-800 bg-slate-900/65 p-4 shadow-lg shadow-slate-950/30 ring-1 ring-white/5 backdrop-blur-sm">
                                <p class="text-sm font-semibold text-white">Controlled Access</p>
                                <p class="mt-2 text-sm leading-6 text-slate-400">Use role-based access so finance actions stay limited to authorized club users.</p>
                            </div>
                        </div>
                    </section>

                    <section class="relative">
                        <div class="absolute inset-0 rounded-[2rem] bg-gradient-to-br from-amber-300/10 via-sky-400/10 to-transparent blur-2xl"></div>
                        <div class="relative overflow-hidden rounded-[2rem] border border-slate-800/90 bg-slate-900/80 p-6 shadow-2xl shadow-slate-950/40 ring-1 ring-white/5 backdrop-blur-xl sm:p-8">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-300/90">Finance Platform</p>
                                    <h3 class="mt-3 text-2xl font-bold text-white">Built for club accountability</h3>
                                </div>

                                <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-amber-400/25 bg-slate-950/70 shadow-lg shadow-slate-950/50">
                                    <img src="{{ asset('images/logo.png') }}" alt="Bogo Eagles Club Logo" class="h-12 w-12 object-contain" />
                                </div>
                            </div>

                            <div class="mt-8 rounded-3xl border border-slate-800 bg-[linear-gradient(145deg,rgba(15,23,42,0.95),rgba(12,18,32,0.86))] p-6 shadow-inner shadow-slate-950/40">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-400/12 ring-1 ring-sky-400/20">
                                        <img src="{{ asset('images/logo.png') }}" alt="Bogo Eagles Club Logo" class="h-10 w-10 object-contain" />
                                    </div>

                                    <div>
                                        <p class="text-lg font-semibold text-white">CNBEC Finance Tracker</p>
                                        <p class="text-sm text-slate-400">A centralized system for responsible club fund management.</p>
                                    </div>
                                </div>

                                <div class="mt-6 space-y-4">
                                    <div class="flex items-start gap-3 rounded-2xl border border-slate-800/80 bg-slate-950/55 px-4 py-4">
                                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-amber-300 shadow-[0_0_12px_rgba(252,211,77,0.5)]"></span>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-100">Contribution tracking for every member</p>
                                            <p class="mt-1 text-sm text-slate-400">Organize payment entries with categories, references, and clear financial history.</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3 rounded-2xl border border-slate-800/80 bg-slate-950/55 px-4 py-4">
                                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-sky-300 shadow-[0_0_12px_rgba(125,211,252,0.45)]"></span>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-100">Structured expense and reporting workflow</p>
                                            <p class="mt-1 text-sm text-slate-400">Keep officers aligned with practical records that support transparency and review.</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3 rounded-2xl border border-slate-800/80 bg-slate-950/55 px-4 py-4">
                                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-300 shadow-[0_0_12px_rgba(110,231,183,0.4)]"></span>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-100">Role-based access for trusted operations</p>
                                            <p class="mt-1 text-sm text-slate-400">Separate member visibility from officer-level finance actions without complicating the workflow.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </main>

            <footer class="relative z-10 border-t border-slate-800/70 bg-slate-950/50 px-6 py-6 text-center text-xs text-slate-500 backdrop-blur-sm">
                © 2026 Bogo Eagle's Club Finance Tracker • Developed by Nigel Eian Cavalida
            </footer>
        </div>
    </body>
</html>
