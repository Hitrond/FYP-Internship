<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Student workspace</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    Welcome back, {{ Str::before(Auth::user()->name, ' ') }}
                </h2>
                <p class="text-sm text-slate-500">Here is your internship progress at a glance.</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-semibold capitalize
                @if($internshipStatus === 'secured') bg-emerald-100 text-emerald-800
                @elseif($internshipStatus === 'interviewing') bg-blue-100 text-blue-800
                @else bg-amber-100 text-amber-800 @endif">
                {{ str_replace('_', ' ', $internshipStatus) }}
            </span>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ([
                    ['label' => 'Applications', 'value' => $applicationCounts['total'], 'color' => 'text-slate-900'],
                    ['label' => 'Active', 'value' => $applicationCounts['active'], 'color' => 'text-indigo-700'],
                    ['label' => 'Interviewing', 'value' => $applicationCounts['interviewing'], 'color' => 'text-blue-700'],
                    ['label' => 'Accepted', 'value' => $applicationCounts['accepted'], 'color' => 'text-emerald-700'],
                ] as $metric)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $metric['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold {{ $metric['color'] }}">{{ $metric['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Current placement</p>
                                @if ($acceptedApplication)
                                    <h3 class="mt-2 text-xl font-bold text-slate-900">{{ $acceptedApplication->company_name }}</h3>
                                    <p class="text-sm text-slate-500">{{ $acceptedApplication->position_title ?: 'Internship placement accepted' }}</p>
                                @else
                                    <h3 class="mt-2 text-xl font-bold text-slate-900">Still searching</h3>
                                    <p class="text-sm text-slate-500">Keep your application pipeline moving.</p>
                                @endif
                            </div>
                            <a href="{{ route('student.companies.index') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-indigo-700">
                                Manage companies
                            </a>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                            <div>
                                <h3 class="font-semibold text-slate-900">Upcoming follow-ups</h3>
                                <p class="text-sm text-slate-500">Application deadlines and contacts that need attention.</p>
                            </div>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @forelse ($followUps as $application)
                                @php($isOverdue = $application->next_followup_on->lt(today()))
                                <div class="flex items-center justify-between gap-4 px-6 py-4">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $application->company_name }}</p>
                                        <p class="text-sm text-slate-500">
                                            {{ $application->position_title ?: $application->status }}
                                            @if ($application->contact_name)
                                                · {{ $application->contact_name }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold {{ $isOverdue ? 'text-red-600' : 'text-slate-700' }}">
                                            {{ $application->next_followup_on->format('M d, Y') }}
                                        </p>
                                        <p class="text-xs {{ $isOverdue ? 'text-red-500' : 'text-slate-400' }}">
                                            {{ $isOverdue ? 'Overdue' : ($application->next_followup_on->isToday() ? 'Today' : $application->next_followup_on->diffForHumans()) }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="px-6 py-10 text-center">
                                    <p class="text-sm text-slate-500">No follow-ups scheduled.</p>
                                    <a href="{{ route('student.companies.index') }}" class="mt-2 inline-block text-sm font-semibold text-indigo-600 hover:text-indigo-800">Add a follow-up date</a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Latest logbook</p>
                            <a href="{{ route('student.logbook.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">View all</a>
                        </div>
                        @if ($latestLogbook)
                            <div class="mt-4 flex items-center justify-between">
                                <div>
                                    <p class="text-xl font-bold text-slate-900">Week {{ $latestLogbook->week_number }}</p>
                                    <p class="text-sm text-slate-500">Updated {{ $latestLogbook->updated_at->diffForHumans() }}</p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize
                                    @if($latestLogbook->status === 'approved') bg-emerald-100 text-emerald-800
                                    @elseif($latestLogbook->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-amber-100 text-amber-800 @endif">
                                    {{ $latestLogbook->status }}
                                </span>
                            </div>
                            @if ($latestLogbook->status === 'rejected' && $latestLogbook->supervisor_remarks)
                                <p class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $latestLogbook->supervisor_remarks }}</p>
                            @endif
                        @elseif ($nextLogbookWeek)
                            <p class="mt-4 text-sm text-slate-500">Week {{ $nextLogbookWeek }} has not been submitted yet.</p>
                        @endif
                    </div>

                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Clearance</p>
                            <a href="{{ route('student.clearance.create') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Open</a>
                        </div>
                        @if ($finalClearance)
                            <p class="mt-3 text-lg font-bold capitalize text-slate-900">{{ $finalClearance->status }}</p>
                            <p class="text-sm text-slate-500">
                                Mentor: {{ ucfirst($finalClearance->mentor_status) }} · Supervisor: {{ ucfirst($finalClearance->supervisor_status) }}
                            </p>
                        @elseif ($latestClearance)
                            <p class="mt-3 text-lg font-bold capitalize text-slate-900">Placement {{ $latestClearance->status }}</p>
                            <p class="text-sm text-slate-500">Submitted {{ $latestClearance->created_at->format('M d, Y') }}</p>
                        @else
                            <p class="mt-3 text-sm text-slate-500">No clearance submission yet.</p>
                        @endif
                        @if ($latestClearance)
                            <a href="{{ route('placement-clearances.view', [$latestClearance, 'placement-agreement']) }}" target="_blank" rel="noopener" class="mt-4 inline-flex rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-slate-200">
                                View placement form
                            </a>
                        @endif
                    </div>

                    <div class="bg-slate-900 rounded-xl p-6 text-white shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Quick actions</p>
                        <div class="mt-4 grid gap-2">
                            <a href="{{ route('student.resume.builder') }}" class="rounded-lg bg-white/10 px-4 py-2.5 text-sm font-semibold hover:bg-white/15">Build resume</a>
                            <a href="{{ route('student.cover-letter.create') }}" class="rounded-lg bg-white/10 px-4 py-2.5 text-sm font-semibold hover:bg-white/15">Write cover letter</a>
                            @if ($nextLogbookWeek)
                                <a href="{{ route('student.logbook.create', ['week' => $nextLogbookWeek]) }}" class="rounded-lg bg-indigo-500 px-4 py-2.5 text-sm font-semibold hover:bg-indigo-400">Submit week {{ $nextLogbookWeek }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
