<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <body class="bg-slate-950 font-sans text-slate-100 antialiased">
        <div class="flex min-h-screen flex-col bg-slate-950 pt-6 text-slate-100 sm:pt-0">
            <div class="flex flex-1 flex-col items-center justify-center px-4 sm:px-6">
                <a href="/">
                    <x-application-logo class="h-20 w-20 fill-current text-sky-300" />
                </a>

                <div class="mt-6 w-full overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/95 px-6 py-4 shadow-2xl shadow-slate-950/40 sm:max-w-md">
                    {{ $slot }}
                </div>
            </div>

            <footer class="border-t border-slate-800/70 bg-slate-950/80 px-4 py-6 text-center text-xs text-slate-500">
                © 2026 Bogo Eagle's Club Finance Tracker • Developed by Nigel Eian Cavalida
            </footer>
        </div>
    </body>
</html>
