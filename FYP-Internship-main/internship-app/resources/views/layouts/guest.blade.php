<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'InternTrack') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <main class="grid min-h-screen lg:grid-cols-[.9fr_1.1fr]">
        <section class="relative hidden overflow-hidden bg-[#17233f] p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-72 w-72 rounded-full bg-orange-400/15 blur-3xl"></div>
            <div class="relative">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/15">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-9 4h14M5 7h14a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z"/></svg>
                    </span>
                    <span class="text-xl font-bold tracking-tight">InternTrack</span>
                </a>
            </div>

            <div class="relative max-w-xl">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-orange-300">One connected journey</p>
                <h1 class="mt-5 text-5xl font-bold leading-tight tracking-[-0.04em]">Every internship milestone, in one secure workspace.</h1>
                <p class="mt-6 text-lg leading-8 text-slate-300">Applications, placement approval, weekly attendance, evaluations, and final clearance stay connected from beginning to completion.</p>
                <div class="mt-9 flex flex-wrap gap-3 text-sm font-semibold text-slate-200">
                    <span class="rounded-full bg-white/10 px-4 py-2 ring-1 ring-white/10">Students</span>
                    <span class="rounded-full bg-white/10 px-4 py-2 ring-1 ring-white/10">Academic Mentors</span>
                    <span class="rounded-full bg-white/10 px-4 py-2 ring-1 ring-white/10">Industrial Supervisors</span>
                </div>
            </div>

            <p class="relative text-xs text-slate-400">&copy; {{ date('Y') }} Internship Management System</p>
        </section>

        <section class="relative flex items-center justify-center px-5 py-10 sm:px-8">
            <div class="absolute right-0 top-0 h-64 w-64 rounded-full bg-indigo-100/70 blur-3xl"></div>
            <div class="relative w-full max-w-md">
                <a href="{{ url('/') }}" class="mb-8 inline-flex items-center gap-3 lg:hidden">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#17233f]">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-9 4h14M5 7h14a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z"/></svg>
                    </span>
                    <span class="text-lg font-bold text-slate-900">InternTrack</span>
                </a>

                <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-xl shadow-slate-900/5 sm:p-9">
                    {{ $slot }}
                </div>

                <p class="mt-6 text-center text-xs text-slate-500">
                    Need help accessing your account? Contact your internship coordinator.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
