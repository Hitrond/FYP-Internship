<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Manage internship applications, placement approvals, weekly logbooks, evaluations, and final clearance in one connected workspace.">
    <title>{{ config('app.name', 'InternTrack') }} — Internship Management</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            color-scheme: light;
            --ink: #172033;
            --muted: #62708a;
            --navy: #14213d;
            --blue: #3157d5;
            --blue-soft: #eef2ff;
            --orange: #f59e6b;
            --line: #e6eaf1;
        }

        html { scroll-behavior: smooth; }
        body { font-family: 'Instrument Sans', sans-serif; color: var(--ink); }

        .hero-mesh {
            background:
                radial-gradient(circle at 78% 8%, rgba(49, 87, 213, .13), transparent 29rem),
                radial-gradient(circle at 7% 60%, rgba(245, 158, 107, .12), transparent 25rem),
                linear-gradient(180deg, #fbfcff 0%, #ffffff 100%);
        }

        .dot-grid {
            background-image: radial-gradient(rgba(49, 87, 213, .16) 1px, transparent 1px);
            background-size: 22px 22px;
        }

        .soft-shadow { box-shadow: 0 24px 70px rgba(20, 33, 61, .12); }
        .card-shadow { box-shadow: 0 12px 35px rgba(20, 33, 61, .07); }

        .float-card { animation: float 5s ease-in-out infinite; }
        .float-card-delayed { animation: float 5s ease-in-out 1.4s infinite; }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .float-card, .float-card-delayed { animation: none; }
        }
    </style>
</head>
<body class="min-h-screen bg-white antialiased">
    <div class="hero-mesh relative overflow-hidden">
        <div class="dot-grid pointer-events-none absolute -right-16 top-24 h-80 w-80 rounded-full opacity-60"></div>

        <header class="relative z-20 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl">
            <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 sm:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3" aria-label="InternTrack home">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#17233f] shadow-lg shadow-slate-900/15">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-9 4h10m-5 0v1m-7 8h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v9a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <span>
                        <span class="block text-lg font-bold tracking-tight text-slate-900">InternTrack</span>
                        <span class="hidden text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500 sm:block">Internship Management</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-8 text-sm font-semibold text-slate-600 lg:flex" aria-label="Main navigation">
                    <a href="#workflow" class="transition hover:text-blue-700">How it works</a>
                    <a href="#features" class="transition hover:text-blue-700">Features</a>
                    <a href="#roles" class="transition hover:text-blue-700">For every role</a>
                </nav>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#17233f] px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-800">
                            Open dashboard
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden text-sm font-semibold text-slate-700 transition hover:text-blue-700 sm:block">Sign in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-xl bg-[#17233f] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:bg-blue-800">
                                Get started
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </header>

        <main class="relative">
            <section class="mx-auto grid max-w-7xl items-center gap-16 px-5 pb-24 pt-16 sm:px-8 lg:grid-cols-[1.02fr_.98fr] lg:pb-32 lg:pt-24">
                <div>
                    <div class="mb-7 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.13em] text-blue-700 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        One connected internship journey
                    </div>

                    <h1 class="max-w-3xl text-5xl font-bold leading-[1.07] tracking-[-0.045em] text-slate-950 sm:text-6xl lg:text-7xl">
                        From first application to
                        <span class="relative inline-block text-blue-700">
                            final clearance.
                            <svg class="absolute -bottom-2 left-0 h-3 w-full text-[#f59e6b]" viewBox="0 0 300 12" fill="none" preserveAspectRatio="none" aria-hidden="true">
                                <path d="M2 9C76 1 190 1 298 7" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </h1>

                    <p class="mt-8 max-w-2xl text-lg leading-8 text-slate-600 sm:text-xl">
                        A single workspace for placement approval, 16-week digital logbooks,
                        attendance verification, evaluations, and university reporting.
                    </p>

                    <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                        <a href="{{ auth()->check() ? url('/dashboard') : route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-700 px-7 py-4 text-sm font-bold text-white shadow-xl shadow-blue-700/20 transition hover:-translate-y-0.5 hover:bg-blue-800">
                            {{ auth()->check() ? 'Go to my dashboard' : 'Sign in to your portal' }}
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="#workflow" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-7 py-4 text-sm font-bold text-slate-700 transition hover:border-blue-300 hover:text-blue-700">
                            Explore the workflow
                        </a>
                    </div>

                    <div class="mt-11 flex flex-wrap items-center gap-x-7 gap-y-3 text-sm font-medium text-slate-500">
                        <span class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Automated deadlines
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Role-based access
                        </span>
                        <span class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Early red-flag alerts
                        </span>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-xl lg:mx-0">
                    <div class="absolute -left-8 top-10 h-36 w-36 rounded-full bg-orange-200/60 blur-3xl"></div>
                    <div class="absolute -right-10 bottom-8 h-44 w-44 rounded-full bg-blue-200/70 blur-3xl"></div>

                    <div class="soft-shadow relative rounded-[2rem] border border-white bg-[#17233f] p-3">
                        <div class="overflow-hidden rounded-[1.45rem] bg-slate-50">
                            <div class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Student workspace</p>
                                    <p class="mt-1 font-bold text-slate-900">Internship overview</p>
                                </div>
                                <span class="rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700">On track</span>
                            </div>

                            <div class="space-y-5 p-6">
                                <div class="rounded-2xl bg-blue-700 p-5 text-white">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-xs font-semibold text-blue-100">16-week progress</p>
                                            <p class="mt-2 text-3xl font-bold">10 <span class="text-base font-medium text-blue-200">/ 16 weeks</span></p>
                                        </div>
                                        <div class="rounded-xl bg-white/15 p-2.5">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                                        </div>
                                    </div>
                                    <div class="mt-5 h-2 overflow-hidden rounded-full bg-blue-900/50">
                                        <div class="h-full w-[62.5%] rounded-full bg-[#f8b486]"></div>
                                    </div>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                        <div class="flex items-center justify-between">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </span>
                                            <span class="text-xs font-bold text-emerald-600">Approved</span>
                                        </div>
                                        <p class="mt-4 text-sm font-bold text-slate-900">Placement</p>
                                        <p class="mt-1 text-xs text-slate-500">Timeline activated</p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                        <div class="flex items-center justify-between">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-100 text-orange-700">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </span>
                                            <span class="text-xs font-bold text-orange-600">Due Friday</span>
                                        </div>
                                        <p class="mt-4 text-sm font-bold text-slate-900">Week 11 logbook</p>
                                        <p class="mt-1 text-xs text-slate-500">Draft in progress</p>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                    <div class="mb-4 flex items-center justify-between">
                                        <p class="text-sm font-bold text-slate-900">Weekly timeline</p>
                                        <p class="text-xs font-semibold text-slate-400">Latest activity</p>
                                    </div>
                                    <div class="flex gap-2">
                                        @foreach (range(1, 16) as $week)
                                            <span class="h-2 flex-1 rounded-full {{ $week <= 9 ? 'bg-emerald-400' : ($week === 10 ? 'bg-blue-600' : 'bg-slate-200') }}" title="Week {{ $week }}"></span>
                                        @endforeach
                                    </div>
                                    <div class="mt-4 flex items-center gap-3">
                                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">IS</span>
                                        <p class="text-xs leading-5 text-slate-600"><strong class="text-slate-800">Industrial Supervisor</strong> verified Week 10 and rendered hours.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="float-card absolute -left-8 top-28 hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-xl sm:block">
                        <p class="text-xs font-semibold text-slate-400">Rendered hours</p>
                        <p class="mt-1 text-xl font-bold text-slate-900">392 hrs</p>
                    </div>
                    <div class="float-card-delayed absolute -bottom-7 -right-5 hidden items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl sm:flex">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5-3a9 9 0 11-16 5.5L3 21l4.5-1A9 9 0 1120 9z"/></svg>
                        </span>
                        <span><strong class="block text-sm text-slate-900">Review complete</strong><span class="text-xs text-slate-500">Feedback recorded</span></span>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <section class="border-y border-slate-200 bg-white">
        <div class="mx-auto grid max-w-7xl grid-cols-2 divide-x divide-y divide-slate-200 px-5 sm:px-8 md:grid-cols-4 md:divide-y-0">
            @foreach ([
                ['Student', 'Track and submit'],
                ['Academic Mentor', 'Monitor and intervene'],
                ['Industrial Supervisor', 'Verify and evaluate'],
                ['Administrator', 'Manage and report'],
            ] as [$role, $action])
                <div class="px-4 py-7 text-center">
                    <p class="font-bold text-slate-900">{{ $role }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $action }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="workflow" class="bg-slate-50 py-24 sm:py-28">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-blue-700">A structured workflow</p>
                <h2 class="mt-4 text-4xl font-bold tracking-tight text-slate-950 sm:text-5xl">One journey. No missing pieces.</h2>
                <p class="mt-5 text-lg leading-8 text-slate-600">Each stage hands off cleanly to the next, giving the university early visibility instead of end-of-semester surprises.</p>
            </div>

            <div class="relative mt-16 grid gap-5 lg:grid-cols-4">
                <div class="absolute left-[12%] right-[12%] top-8 hidden border-t-2 border-dashed border-blue-200 lg:block"></div>
                @foreach ([
                    ['01', 'Apply', 'Students track companies, application outcomes, offer letters, resumes, and cover letters.'],
                    ['02', 'Approve placement', 'The Academic Mentor validates the offer and activates the official 16-week timeline.'],
                    ['03', 'Verify progress', 'Weekly logbooks capture status, hours, MC evidence, feedback, and supervisor approval.'],
                    ['04', 'Complete', 'Evaluations, signed clearance documents, Pass/Fail decisions, and reports close the cycle.'],
                ] as [$number, $title, $copy])
                    <article class="card-shadow relative rounded-3xl border border-slate-200 bg-white p-7">
                        <span class="relative z-10 flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-700 text-lg font-bold text-white shadow-lg shadow-blue-700/20">{{ $number }}</span>
                        <h3 class="mt-7 text-xl font-bold text-slate-900">{{ $title }}</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $copy }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="features" class="py-24 sm:py-28">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="grid gap-12 lg:grid-cols-[.75fr_1.25fr] lg:items-end">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-blue-700">Designed for early action</p>
                    <h2 class="mt-4 text-4xl font-bold tracking-tight text-slate-950 sm:text-5xl">See the issue while there is still time to solve it.</h2>
                </div>
                <p class="max-w-2xl text-lg leading-8 text-slate-600 lg:justify-self-end">
                    Automated schedules and exception alerts keep students accountable while giving Academic Mentors a clear, cohort-wide view.
                </p>
            </div>

            <div class="mt-14 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['calendar', 'Automatic 16-week timeline', 'Placement approval creates every weekly block, date range, and submission deadline automatically.', 'bg-blue-100 text-blue-700'],
                    ['flag', 'Red-flag intervention', 'Overdue submissions and attendance-related rejections surface immediately for Academic Mentors.', 'bg-red-100 text-red-700'],
                    ['briefcase', 'Placement pipeline visibility', 'Mentors can spot zero applications or repeated rejections and support students earlier.', 'bg-orange-100 text-orange-700'],
                    ['document', 'Evidence-backed logbooks', 'Daily statuses, rendered hours, MC uploads, feedback, e-signatures, and company stamps stay connected.', 'bg-violet-100 text-violet-700'],
                    ['unlock', 'Controlled extensions', 'Locked weeks stay auditable while students request and mentors approve a specific extension window.', 'bg-emerald-100 text-emerald-700'],
                    ['chart', 'Evaluation and reporting', 'Final supervisor scores, logbook completeness, Pass/Fail locking, and CSV exports are in one place.', 'bg-cyan-100 text-cyan-700'],
                ] as [$icon, $title, $copy, $colour])
                    <article class="rounded-3xl border border-slate-200 bg-white p-7 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/5">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $colour }}">
                            @if ($icon === 'calendar')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                            @elseif ($icon === 'flag')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5v16m0-16h11l-2 4 2 4H5"/></svg>
                            @elseif ($icon === 'briefcase')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-9 4h14M5 7h14a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z"/></svg>
                            @elseif ($icon === 'document')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/></svg>
                            @elseif ($icon === 'unlock')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11V7a5 5 0 019.8-1.4M5 11h14v10H5V11z"/></svg>
                            @else
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V9m5 10V5m5 14v-7m5 7V3"/></svg>
                            @endif
                        </span>
                        <h3 class="mt-6 text-lg font-bold text-slate-900">{{ $title }}</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $copy }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="roles" class="bg-[#17233f] py-24 text-white sm:py-28">
        <div class="mx-auto max-w-7xl px-5 sm:px-8">
            <div class="max-w-2xl">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-orange-300">Role-focused workspaces</p>
                <h2 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Everyone sees exactly what they need.</h2>
                <p class="mt-5 text-lg leading-8 text-slate-300">Simple student submissions, lightweight company verification, proactive academic oversight, and complete administration.</p>
            </div>

            <div class="mt-14 grid gap-px overflow-hidden rounded-3xl border border-white/10 bg-white/10 md:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Student', 'Applications, documents, weekly logbooks, evaluations, and final clearance.', 'ST'],
                    ['Academic Mentor', 'Placement approval, cohort monitoring, extensions, alerts, and final results.', 'AM'],
                    ['Industrial Supervisor', 'Logbook verification, attendance feedback, evaluations, and company sign-off.', 'IS'],
                    ['Administrator', 'User pairing, system-wide clearance monitoring, and institutional reporting.', 'AD'],
                ] as [$role, $copy, $initials])
                    <article class="bg-[#17233f] p-7 transition hover:bg-white/5">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-sm font-bold text-orange-200">{{ $initials }}</span>
                        <h3 class="mt-7 text-lg font-bold">{{ $role }}</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-300">{{ $copy }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="px-5 py-20 sm:px-8">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-8 overflow-hidden rounded-[2rem] bg-blue-700 px-7 py-12 text-center text-white shadow-2xl shadow-blue-700/20 sm:px-12 lg:flex-row lg:text-left">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-blue-200">Your internship workspace</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Keep every week visible and every decision recorded.</h2>
            </div>
            <a href="{{ auth()->check() ? url('/dashboard') : route('login') }}" class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-white px-7 py-4 text-sm font-bold text-blue-700 shadow-lg transition hover:-translate-y-0.5">
                {{ auth()->check() ? 'Open dashboard' : 'Sign in now' }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
    </section>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-5 py-8 text-center text-sm text-slate-500 sm:px-8 md:flex-row">
            <div class="flex items-center gap-2 font-bold text-slate-800">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#17233f] text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-9 4h14M5 7h14a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z"/></svg>
                </span>
                InternTrack
            </div>
            <p>&copy; {{ date('Y') }} Internship Management System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
