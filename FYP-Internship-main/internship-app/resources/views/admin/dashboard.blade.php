<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">System overview</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-900">Admin Control Centre</h2>
                <p class="mt-1 text-sm text-slate-500">Monitor placements, weekly progress, exceptions, and final outcomes.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.clearances.index', $selectedCycle ? ['semester' => $selectedCycle->id] : []) }}" class="inline-flex items-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-bold text-indigo-700 shadow-sm hover:bg-indigo-100">
                    Placement & Supervisor Accounts
                </a>
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50">
                    Add user
                </a>
                <a href="{{ route('admin.clearances.export', $selectedCycle ? ['semester' => $selectedCycle->id] : []) }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg>
                    Export cohort
                </a>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-7 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($cycles->isNotEmpty())
                <section class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-slate-900">Reporting semester</p>
                        <p class="mt-1 text-xs text-slate-500">Dashboard statistics and alerts are isolated to the selected cohort.</p>
                    </div>
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex gap-2">
                        <select name="semester" class="w-full min-w-0 rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-auto sm:min-w-[240px]">
                            @foreach ($cycles as $cycle)
                                <option value="{{ $cycle->id }}" @selected($selectedCycle?->id === $cycle->id)>{{ $cycle->name }} · {{ ucfirst($cycle->status) }}</option>
                            @endforeach
                        </select>
                        <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700">View</button>
                    </form>
                </section>
            @else
                <section class="flex flex-col gap-4 rounded-2xl border border-blue-200 bg-blue-50 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-bold text-blue-900">No semester has been configured</p>
                        <p class="mt-1 text-sm text-blue-700">Current statistics use legacy records until you create the first cohort.</p>
                    </div>
                    <a href="{{ route('admin.semesters.create') }}" class="shrink-0 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-800">Create semester</a>
                </section>
            @endif

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['Active interns', $stats['active_interns'], 'Students with approved placements', 'bg-indigo-100 text-indigo-700'],
                    ['Unassigned students', $stats['unassigned_students'], 'Need an Academic Mentor', 'bg-amber-100 text-amber-700'],
                    ['Overdue logbooks', $stats['overdue_logbooks'], 'Locked weekly submissions', 'bg-rose-100 text-rose-700'],
                    ['Clearance rate', $stats['clearance_rate'].'%', $stats['completed_clearances'].' final clearances complete', 'bg-emerald-100 text-emerald-700'],
                ] as [$label, $value, $caption, $colour])
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
                                <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
                            </div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $colour }}">
                                <span class="h-2.5 w-2.5 rounded-full bg-current"></span>
                            </span>
                        </div>
                        <p class="mt-3 text-xs text-slate-500">{{ $caption }}</p>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.35fr_.65fr]">
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                        <div>
                            <h3 class="font-bold text-slate-900">Action required</h3>
                            <p class="mt-1 text-sm text-slate-500">Exceptions needing university attention.</p>
                        </div>
                        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">
                            {{ $stats['unassigned_students'] + $stats['overdue_logbooks'] + $alerts['extensions']->count() }}
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($alerts['unassigned'] as $student)
                            <div class="flex items-center gap-4 px-6 py-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-sm font-bold text-amber-700">AM</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $student->name }}</p>
                                    <p class="text-xs text-slate-500">No Academic Mentor assigned</p>
                                </div>
                                <a href="{{ route('admin.users.index') }}#user-{{ $student->id }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Assign</a>
                            </div>
                        @endforeach

                        @foreach ($alerts['overdue'] as $logbook)
                            <div class="flex items-center gap-4 px-6 py-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-sm font-bold text-rose-700">W{{ $logbook->week_number }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $logbook->student?->name }}</p>
                                    <p class="text-xs text-slate-500">Week {{ $logbook->week_number }} overdue and locked</p>
                                </div>
                                <span class="text-xs font-semibold text-rose-600">{{ $logbook->submission_due_at?->diffForHumans() }}</span>
                            </div>
                        @endforeach

                        @foreach ($alerts['extensions'] as $logbook)
                            <div class="flex items-center gap-4 px-6 py-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-sm font-bold text-blue-700">EX</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $logbook->student?->name }}</p>
                                    <p class="text-xs text-slate-500">Extension waiting for {{ $logbook->student?->mentor?->name ?? 'an Academic Mentor' }}</p>
                                </div>
                                <span class="text-xs font-semibold text-blue-600">Requested</span>
                            </div>
                        @endforeach

                        @foreach ($alerts['attendance'] as $logbook)
                            <div class="flex items-center gap-4 px-6 py-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100 text-sm font-bold text-red-700">!</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $logbook->student?->name }}</p>
                                    <p class="text-xs text-slate-500">Attendance-related rejection in Week {{ $logbook->week_number }}</p>
                                </div>
                                <span class="text-xs font-semibold text-red-600">Red flag</span>
                            </div>
                        @endforeach

                        @if ($alerts['unassigned']->isEmpty() && $alerts['overdue']->isEmpty() && $alerts['extensions']->isEmpty() && $alerts['attendance']->isEmpty())
                            <div class="px-6 py-12 text-center">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <p class="mt-3 font-bold text-slate-900">No open exceptions</p>
                                <p class="mt-1 text-sm text-slate-500">The cohort is currently on track.</p>
                            </div>
                        @endif
                    </div>
                </article>

                <div class="space-y-6">
                    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="font-bold text-slate-900">Users by role</h3>
                        <div class="mt-5 space-y-4">
                            @foreach ([
                                ['Students', 'student', 'bg-indigo-500'],
                                ['Academic Mentors', 'mentor', 'bg-emerald-500'],
                                ['Industrial Supervisors', 'supervisor', 'bg-amber-500'],
                                ['Administrators', 'admin', 'bg-slate-700'],
                            ] as [$label, $role, $colour])
                                @php
                                    $count = (int) ($roleCounts[$role] ?? 0);
                                    $percentage = max(4, $roleCounts->sum() > 0 ? round(($count / $roleCounts->sum()) * 100) : 0);
                                @endphp
                                <div>
                                    <div class="mb-2 flex justify-between text-sm">
                                        <span class="font-medium text-slate-600">{{ $label }}</span>
                                        <span class="font-bold text-slate-900">{{ $count }}</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $colour }}" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="mt-6 inline-flex text-sm font-bold text-indigo-600 hover:text-indigo-800">Manage all users &rarr;</a>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-[#17233f] p-6 text-white shadow-sm">
                        <p class="text-sm font-semibold text-slate-300">Final results locked</p>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-white/10 p-4">
                                <p class="text-2xl font-bold text-emerald-300">{{ $resultCounts['pass'] ?? 0 }}</p>
                                <p class="mt-1 text-xs text-slate-300">Pass</p>
                            </div>
                            <div class="rounded-xl bg-white/10 p-4">
                                <p class="text-2xl font-bold text-rose-300">{{ $resultCounts['fail'] ?? 0 }}</p>
                                <p class="mt-1 text-xs text-slate-300">Fail</p>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

        </div>
    </div>
</x-app-layout>
