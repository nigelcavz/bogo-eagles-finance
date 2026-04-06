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
        <div class="flex min-h-screen flex-col bg-slate-950 text-slate-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-slate-800/80 bg-slate-900/80 shadow-lg shadow-slate-950/30 backdrop-blur">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1 pb-10">
                {{ $slot }}
            </main>

            <footer class="border-t border-slate-800/70 bg-slate-950/80 px-4 py-6 text-center text-xs text-slate-500">
                © 2026 Bogo Eagle's Club Finance Tracker • Developed by Nigel Eian Cavalida
            </footer>
        </div>
    </body>
</html>
