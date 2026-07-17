<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'InternTrack') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-800">
        <div class="min-h-screen bg-slate-50">
            @include('layouts.navigation')

            @auth
                @php
                    $isRoleDashboard = request()->routeIs('dashboard', 'admin.dashboard', 'mentor.dashboard', 'supervisor.dashboard');
                    $closeRoute = Auth::user()->isAdmin()
                        ? route('admin.dashboard')
                        : (Auth::user()->isMentor()
                            ? route('mentor.dashboard')
                            : (Auth::user()->isSupervisor() ? route('supervisor.dashboard') : route('dashboard')));
                    $previousUrl = url()->previous() !== url()->current() ? url()->previous() : $closeRoute;
                @endphp
                @unless($isRoleDashboard)
                    <nav aria-label="Page navigation" class="border-b border-slate-200 bg-slate-50/90">
                        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                            <a href="{{ $previousUrl }}" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg>
                                Back
                            </a>
                            <a href="{{ $closeRoute }}" class="inline-flex min-h-10 items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold text-slate-600 transition hover:bg-slate-200 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" aria-label="Close page and return to dashboard">
                                Close
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                            </a>
                        </div>
                    </nav>
                @endunless
            @endauth

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-slate-200 bg-white">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="min-w-0 overflow-x-hidden">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
