<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', "Bogo Eagles' Club Finance Tracker") }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <body class="bg-slate-950 font-sans text-slate-100 antialiased">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.12),_transparent_34%),linear-gradient(180deg,_rgba(15,23,42,0.98)_0%,_rgba(15,23,42,0.96)_18%,_rgba(2,6,23,1)_42%)] text-slate-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="relative z-30 bg-transparent">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-slate-900/30 via-slate-900/10 to-transparent"></div>
                    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="pb-10">
                {{ $slot }}
            </main>

            <footer class="border-t border-slate-800/70 bg-slate-950/80 px-4 py-6 text-center text-xs text-slate-500">
                © 2026 Bogo Eagle's Club Finance Tracker • Developed by Nigel Eian Cavalida
            </footer>
        </div>
    </body>
</html>
