<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">University workspace</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ __('Academic Mentor Dashboard') }}</h2>
                <p class="text-sm text-slate-500">
                    Pre-placement oversight, active monitoring, extensions, and final results.
                    @if ($activeCycle)
                        <span class="ml-1 font-semibold text-indigo-600">Viewing {{ $activeCycle->name }}.</span>
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form method="GET" action="{{ route('mentor.dashboard') }}" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-[180px_220px_220px_auto_auto]">
                    <select name="semester" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Active semester</option>
                        @foreach($cycles as $cycle)
                            <option value="{{ $cycle->id }}" @selected($activeCycle?->id === $cycle->id)>{{ $cycle->name }}</option>
                        @endforeach
                    </select>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search student / email / TP..." class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <select name="student_status" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All student criteria</option>
                        <option value="no_applications" @selected(request('student_status') === 'no_applications')>No applications</option>
                        <option value="high_rejection" @selected(request('student_status') === 'high_rejection')>High rejection rate</option>
                        <option value="pending_logbooks" @selected(request('student_status') === 'pending_logbooks')>Pending logbooks</option>
                        <option value="can_finalize" @selected(request('student_status') === 'can_finalize')>Ready for pass/fail</option>
                        <option value="result_pending" @selected(request('student_status') === 'result_pending')>Result pending</option>
                        <option value="pass" @selected(request('student_status') === 'pass')>Passed</option>
                        <option value="fail" @selected(request('student_status') === 'fail')>Failed</option>
                    </select>
                    <button class="rounded-lg bg-white px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">Filter</button>
                    <a href="{{ route('mentor.dashboard') }}" class="rounded-lg bg-white px-3 py-2 text-center text-xs font-bold uppercase tracking-wider text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">Reset</a>
                </form>
                <a href="{{ route('mentor.results.export') }}" class="w-fit rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-slate-700">Export cohort CSV</a>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif
            @php
                $workflowSteps = [
                    'pre_placement' => ['label' => '1. Student applications', 'help' => 'Monitor application progress and offer letters.', 'route' => route('mentor.dashboard')],
                    'placement_review' => ['label' => '2. Placement approval', 'help' => 'Check placement details and approve or reject submissions.', 'route' => route('mentor.clearances.index', ['status' => 'pending'])],
                    'internship' => ['label' => '3. Internship monitoring', 'help' => 'Review every student’s weekly logbook progress and exceptions.', 'route' => route('mentor.logbooks.index')],
                    'completion' => ['label' => '4. Completion', 'help' => 'Review final clearance and record the final result.', 'route' => route('mentor.final-clearances.index')],
                ];
            @endphp
            <section aria-labelledby="workflow-title" class="overflow-hidden rounded-xl border border-indigo-200 bg-white shadow-sm">
                <div class="border-b border-indigo-100 bg-indigo-50 px-6 py-4">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">Current event</p>
                    <h3 id="workflow-title" class="mt-1 text-lg font-bold text-slate-900">{{ $workflowSteps[$workflowStage]['label'] }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ $workflowSteps[$workflowStage]['help'] }} Use the highlighted step to open the relevant workspace.</p>
                </div>
                <ol class="grid gap-px bg-slate-200 md:grid-cols-4" aria-label="Internship event timeline">
                    @foreach($workflowSteps as $key => $step)
                        <li class="bg-white">
                            <a href="{{ $step['route'] }}" @if($key === $workflowStage) aria-current="step" @endif class="block h-full border-t-4 px-5 py-4 transition hover:bg-slate-50 {{ $key === $workflowStage ? 'border-indigo-600 bg-indigo-50/60' : 'border-transparent' }}">
                                <span class="text-sm font-bold {{ $key === $workflowStage ? 'text-indigo-700' : 'text-slate-700' }}">{{ $step['label'] }}</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">{{ $step['help'] }}</span>
                                <span class="mt-3 inline-block text-xs font-bold {{ $key === $workflowStage ? 'text-indigo-700' : 'text-slate-500' }}">{{ $key === $workflowStage ? 'Go to current action →' : 'Open workspace →' }}</span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </section>
            @if($attendanceAlerts->isNotEmpty())
                <div class="overflow-hidden rounded-xl border border-red-300 bg-red-50 shadow-sm">
                    <div class="border-b border-red-200 px-6 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="font-bold text-red-900">Attendance intervention required</h3>
                                <p class="text-sm text-red-700">An Industrial Supervisor rejected these weeks for an attendance issue.</p>
                            </div>
                            <span class="rounded-full bg-red-600 px-3 py-1 text-sm font-bold text-white">{{ $attendanceAlerts->count() }}</span>
                        </div>
                    </div>
                    <div class="divide-y divide-red-200">
                        @foreach($attendanceAlerts as $alert)
                            <div class="grid gap-2 px-6 py-4 md:grid-cols-[1fr_auto] md:items-center">
                                <div>
                                    <p class="font-semibold text-red-950">{{ $alert->student->name }} - Week {{ $alert->week_number }}</p>
                                    <p class="mt-1 text-sm text-red-800">{{ $alert->supervisor_remarks }}</p>
                                </div>
                                <p class="text-xs font-semibold text-red-600">{{ $alert->updated_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($evaluationAlerts->isNotEmpty())
                <div class="overflow-hidden rounded-xl border border-orange-300 bg-orange-50 shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-orange-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-bold text-orange-900">Performance intervention recommended</h3>
                            <p class="text-sm text-orange-700">Low scores or Poor ratings were submitted by an Industrial Supervisor.</p>
                        </div>
                        <a href="{{ route('mentor.evaluations.index') }}" class="rounded-lg bg-orange-600 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-orange-700">Review evaluations</a>
                    </div>
                    <div class="divide-y divide-orange-200">
                        @foreach($evaluationAlerts as $evaluation)
                            <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                                <div>
                                    <p class="font-semibold text-orange-950">{{ $evaluation->student->name }} - {{ ucfirst($evaluation->type) }}</p>
                                    <p class="text-sm text-orange-800">{{ $evaluation->overall_comments }}</p>
                                </div>
                                <span class="rounded-full bg-orange-600 px-3 py-1 text-sm font-bold text-white">{{ $evaluation->overall_grade }}/10</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="font-bold text-slate-900">Pre-placement pipeline oversight</h3>
                    <p class="text-sm text-slate-500">Identify students with no applications or high rejection rates before placement.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Student</th>
                                <th class="px-6 py-3">Applications</th>
                                <th class="px-6 py-3">Latest company</th>
                                <th class="px-6 py-3">Interviewing</th>
                                <th class="px-6 py-3">Rejected</th>
                                <th class="px-6 py-3">Accepted</th>
                                <th class="px-6 py-3">Intervention</th>
                                <th class="px-6 py-3">Placement approval</th>
                                <th class="px-6 py-3 text-right">Next action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($assignedStudents as $student)
                                @php
                                    $applicationCount = $student->applications->count();
                                    $rejectedCount = $student->applications->where('status', 'Rejected')->count();
                                    $offerApplication = $student->applications->first(fn ($application) => $application->offer_letter_path);
                                    $highRejection = $applicationCount >= 3 && ($rejectedCount / $applicationCount) >= 0.5;
                                    $placement = $student->latestPlacementClearance;
                                @endphp
                                <tr class="{{ $applicationCount === 0 || $highRejection ? 'bg-red-50' : '' }}">
                                    <td class="px-6 py-4 font-semibold text-slate-900">{{ $student->name }}</td>
                                    <td class="px-6 py-4">{{ $applicationCount }}</td>
                                    <td class="px-6 py-4 text-slate-700">{{ $student->applications->sortByDesc('updated_at')->first()?->company_name ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ $student->applications->where('status', 'Interviewing')->count() }}</td>
                                    <td class="px-6 py-4">{{ $rejectedCount }}</td>
                                    <td class="px-6 py-4">{{ $student->applications->where('status', 'Accepted')->count() }}</td>
                                    <td class="px-6 py-4">
                                        @if($applicationCount === 0)
                                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">No applications</span>
                                        @elseif($highRejection)
                                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">High rejection rate</span>
                                        @else
                                            <span class="text-xs font-semibold text-emerald-600">On track</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($placement)
                                            <span class="rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ $placement->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($placement->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $placement->status }}</span>
                                        @else
                                            <span class="text-sm font-semibold text-slate-500">Not available</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($placement)
                                            <a href="{{ route('mentor.clearances.show', $placement) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">{{ $placement->status === 'pending' ? 'Review placement' : 'View placement' }}</a>
                                        @elseif($offerApplication)
                                            <a href="{{ route('applications.offer-letter', $offerApplication) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">View offer</a>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-6 py-10 text-center text-slate-500">No students assigned.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($pendingExtensions->isNotEmpty())
                <div class="overflow-hidden rounded-xl border border-amber-300 bg-amber-50 shadow-sm">
                    <div class="border-b border-amber-200 px-6 py-4">
                        <h3 class="font-bold text-amber-950">Pending extension requests</h3>
                    </div>
                    <div class="divide-y divide-amber-200">
                        @foreach($pendingExtensions as $logbook)
                            <div class="grid gap-4 px-6 py-5 lg:grid-cols-[1fr_360px]">
                                <div>
                                    <p class="font-bold text-amber-950">{{ $logbook->student->name }} - Week {{ $logbook->week_number }}</p>
                                    <p class="mt-1 text-sm text-amber-800">{{ $logbook->extension_reason }}</p>
                                    <p class="mt-1 text-xs text-amber-700">Original deadline: {{ $logbook->submission_due_at?->format('M d, Y H:i') }}</p>
                                </div>
                                <div class="space-y-3">
                                    <form method="POST" action="{{ route('mentor.logbooks.extension.approve', $logbook) }}" class="flex flex-wrap gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="datetime-local" name="extension_until" value="{{ now()->addWeek()->format('Y-m-d\TH:i') }}" required class="min-w-52 rounded-lg border-amber-300 text-sm">
                                        <button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold uppercase text-white hover:bg-emerald-700">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('mentor.logbooks.extension.reject', $logbook) }}" class="flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="extension_decision_note" required class="min-w-52 flex-1 rounded-lg border-amber-300 text-sm" placeholder="Reason for rejection">
                                        <button class="rounded-lg bg-red-600 px-3 py-2 text-xs font-bold uppercase text-white hover:bg-red-700">Reject</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Active internship cohort</h3>
                        <p class="text-sm text-slate-500">Weekly status overview. Use the dedicated monitor to filter and navigate multiple students.</p>
                    </div>
                    <a href="{{ route('mentor.logbooks.index') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-xs font-bold uppercase tracking-wider text-white hover:bg-indigo-700">Open student logbook monitor</a>
                </div>
                <div class="flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-white p-4 text-xs font-bold shadow-sm" aria-label="Logbook colour guide">
                    <span class="mr-1 self-center text-slate-500">Status guide:</span>
                    <span class="rounded-md border border-emerald-200 bg-emerald-100 px-2.5 py-1 text-emerald-800">✓ Approved</span>
                    <span class="rounded-md border border-amber-200 bg-amber-100 px-2.5 py-1 text-amber-800">! Pending review</span>
                    <span class="rounded-md border border-red-200 bg-red-100 px-2.5 py-1 text-red-800">× Rejected / overdue</span>
                    <span class="rounded-md border border-indigo-200 bg-indigo-100 px-2.5 py-1 text-indigo-800">○ Open</span>
                    <span class="rounded-md border border-slate-200 bg-slate-100 px-2.5 py-1 text-slate-600">– Scheduled</span>
                </div>
                @foreach($assignedStudents as $student)
                    @php
                        $weeklyLogbooks = $student->logbooks->keyBy('week_number');
                        $approvedWeeks = $student->logbooks->where('status', 'approved')->count();
                        $expectedWeeks = $activeCycle?->duration_weeks ?? max(16, (int) $student->logbooks->where('timeline_generated', true)->max('week_number'));
                        $finalEvaluation = $student->performanceEvaluations
                            ->where('type', 'final')
                            ->where('status', 'submitted')
                            ->first();
                        $resolvedWeeks = $student->logbooks
                            ->whereIn('status', ['approved', 'rejected', 'overdue_locked'])
                            ->count();
                        $canFinalize = !$student->internshipResult
                            && $student->logbooks->where('timeline_generated', true)->count() === $expectedWeeks
                            && $resolvedWeeks === $expectedWeeks
                            && $finalEvaluation;
                    @endphp
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h4 class="text-lg font-bold text-slate-900">{{ $student->name }}</h4>
                                <p class="text-sm text-slate-500">{{ $approvedWeeks }}/{{ $expectedWeeks }} approved logbooks</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-800">Final score: {{ $finalEvaluation?->overall_grade ?? 'Pending' }}</span>
                                @if($student->internshipResult)
                                    <span class="rounded-full px-3 py-1 text-xs font-bold uppercase {{ $student->internshipResult->result === 'pass' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">{{ $student->internshipResult->result }}</span>
                                @endif
                            </div>
                        </div>
                        @if($canFinalize)
                            <form method="POST" action="{{ route('mentor.results.store', $student) }}" class="mt-5 grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 lg:grid-cols-[180px_1fr_auto] lg:items-end">
                                @csrf
                                <label>
                                    <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Mentor decision</span>
                                    <select name="result" required class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select final result...</option>
                                        <option value="pass">Pass</option>
                                        <option value="fail">Fail</option>
                                    </select>
                                </label>
                                <label>
                                    <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Rationale</span>
                                    <textarea name="rationale" rows="2" required class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Explain the Academic Mentor's final decision.">Reviewed {{ $approvedWeeks }}/{{ $expectedWeeks }} approved logbooks and final supervisor score {{ $finalEvaluation->overall_grade }}/10.</textarea>
                                </label>
                                <button class="rounded-lg bg-slate-900 px-4 py-2.5 text-xs font-bold uppercase text-white hover:bg-slate-700">Lock result</button>
                            </form>
                        @endif
                        <div class="mt-5 grid grid-cols-4 gap-2 sm:grid-cols-8 lg:grid-cols-16">
                            @for($week = 1; $week <= $expectedWeeks; $week++)
                                @php($weekly = $weeklyLogbooks->get($week))
                                <a href="{{ $weekly ? route('logbooks.show', $weekly) : '#' }}" title="Week {{ $week }}: {{ $weekly?->status ?? 'not generated' }}" class="rounded-lg border p-2 text-center text-xs font-bold
                                    @if($weekly?->status === 'approved') border-emerald-200 bg-emerald-100 text-emerald-800
                                    @elseif($weekly?->status === 'rejected' || $weekly?->status === 'overdue_locked') border-red-200 bg-red-100 text-red-800
                                    @elseif($weekly?->status === 'pending') border-amber-200 bg-amber-100 text-amber-800
                                    @elseif($weekly?->status === 'open') border-indigo-200 bg-indigo-100 text-indigo-800
                                    @else border-slate-200 bg-slate-100 text-slate-500 @endif">
                                    W{{ $week }}
                                </a>
                            @endfor
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-wider text-slate-400">Pending Clearances</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $pendingCount }}</p>
                        <p class="text-sm text-slate-500 mt-1">Students awaiting placement approval.</p>
                    </div>
                    <a href="{{ route('mentor.clearances.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Review Submissions
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-slate-800">Latest Pending Submissions</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-sm uppercase tracking-wider text-slate-500">
                                    <th class="py-3 px-4 font-semibold">Student ID</th>
                                    <th class="py-3 px-4 font-semibold">Company</th>
                                    <th class="py-3 px-4 font-semibold">Supervisor</th>
                                    <th class="py-3 px-4 font-semibold">Submitted</th>
                                    <th class="py-3 px-4 font-semibold text-right">Review</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($pendingClearances as $clearance)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 text-slate-700">{{ $clearance->student_id }}</td>
                                        <td class="py-3 px-4 text-slate-900">{{ $clearance->company_name }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ $clearance->supervisor_name }}</td>
                                        <td class="py-3 px-4 text-slate-500 text-sm">{{ $clearance->created_at->format('M d, Y') }}</td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('mentor.clearances.show', $clearance) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm">Review</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-slate-500">No pending submissions.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
