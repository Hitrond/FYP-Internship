<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Completion review</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Final Clearance Sign-off</h2>
            <p class="text-sm text-slate-500">Review final internship documents for your assigned students.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif

            <form method="GET" action="{{ route($role.'.final-clearances.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_220px_auto_auto]">
                <label>
                    <span class="sr-only">Search final clearances</span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search student, email, TP, or company..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label>
                    <span class="sr-only">Review status</span>
                    <select name="status" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All review statuses</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    </select>
                </label>
                <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Filter</button>
                <a href="{{ route($role.'.final-clearances.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
            </form>

            @forelse ($clearances as $clearance)
                @php
                    $reviewStatus = $role === 'mentor' ? $clearance->mentor_status : $clearance->supervisor_status;
                    $feedback = $role === 'mentor' ? $clearance->mentor_feedback : $clearance->supervisor_feedback;
                @endphp
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-4 border-b border-slate-100 p-6 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold text-slate-900">{{ $clearance->student->name }}</h3>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize
                                    @if($reviewStatus === 'approved') bg-emerald-100 text-emerald-800
                                    @elseif($reviewStatus === 'rejected') bg-red-100 text-red-800
                                    @else bg-amber-100 text-amber-800 @endif">
                                    Your review: {{ $reviewStatus }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">First submitted {{ $clearance->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('final-clearances.view', [$clearance, 'report']) }}" target="_blank" rel="noopener" class="rounded-lg bg-indigo-100 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-200">Internship Report</a>
                            @if ($clearance->report_clearance_form_path)
                                <a href="{{ route('final-clearances.view', [$clearance, 'report-clearance-form']) }}" target="_blank" rel="noopener" class="rounded-lg bg-indigo-100 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-200">Report Clearance Form</a>
                            @endif
                            @if ($clearance->placementClearance)
                                <a href="{{ route('placement-clearances.view', [$clearance->placementClearance, 'placement-agreement']) }}" target="_blank" rel="noopener" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Placement form</a>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div class="space-y-3">
                            @if ($clearance->placementClearance)
                                <div class="rounded-lg border border-slate-200 p-4">
                                    <h4 class="font-semibold text-slate-800">Placement details</h4>
                                    <dl class="mt-3 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                                        <div>
                                            <dt class="text-slate-500">Company</dt>
                                            <dd class="font-medium text-slate-900">{{ $clearance->placementClearance->company_name }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-slate-500">Company supervisor</dt>
                                            <dd class="font-medium text-slate-900">{{ $clearance->placementClearance->supervisor_name }}</dd>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <dt class="text-slate-500">Office address</dt>
                                            <dd class="font-medium text-slate-900">{{ $clearance->placementClearance->office_address }}</dd>
                                        </div>
                                    </dl>
                                    <div class="mt-4 flex flex-wrap gap-3 text-sm font-semibold">
                                        <a href="{{ route('placement-clearances.view', [$clearance->placementClearance, 'job-offer']) }}" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-800">Job offer</a>
                                        <a href="{{ route('placement-clearances.view', [$clearance->placementClearance, 'indemnity-letter']) }}" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-800">Indemnity letter</a>
                                        <a href="{{ route('placement-clearances.view', [$clearance->placementClearance, 'placement-agreement']) }}" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-800">Placement agreement</a>
                                    </div>
                                </div>
                            @endif
                            <h4 class="font-semibold text-slate-800">Sign-off progress</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-lg bg-slate-50 p-4">
                                    <p class="text-slate-500">Mentor</p>
                                    <p class="mt-1 font-semibold capitalize text-slate-900">{{ $clearance->mentor_status }}</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-4">
                                    <p class="text-slate-500">Supervisor</p>
                                    <p class="mt-1 font-semibold capitalize text-slate-900">{{ $clearance->supervisor_status }}</p>
                                </div>
                            </div>
                            @if ($feedback)
                                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $feedback }}</div>
                            @endif

                            <details class="rounded-lg border border-slate-200 bg-white" @if($clearance->events->isNotEmpty()) open @endif>
                                <summary class="cursor-pointer px-4 py-3 text-sm font-bold text-slate-800">
                                    Clearance history
                                    <span class="ml-1 font-normal text-slate-500">({{ max(1, $clearance->events->count()) }} {{ Str::plural('event', max(1, $clearance->events->count())) }})</span>
                                </summary>
                                <ol class="border-t border-slate-100 px-4 py-4">
                                    @if ($clearance->events->isEmpty())
                                        <li class="relative border-l-2 border-indigo-200 pb-1 pl-5">
                                            <span class="absolute -left-[7px] top-1 h-3 w-3 rounded-full bg-indigo-500 ring-4 ring-white"></span>
                                            <p class="text-sm font-semibold text-slate-900">Final clearance submitted</p>
                                            <p class="mt-0.5 text-xs text-slate-500">{{ $clearance->student->name }} (Student) · {{ $clearance->created_at->format('M d, Y H:i') }}</p>
                                            <p class="mt-2 text-xs text-slate-500">Earlier decisions were recorded before detailed history tracking was introduced. Current sign-off statuses are shown above.</p>
                                        </li>
                                    @else
                                        @foreach ($clearance->events->sortByDesc('created_at') as $event)
                                            @php
                                                $eventLabel = match ($event->action) {
                                                    'submitted' => 'Final clearance submitted',
                                                    'resubmitted' => 'Final clearance resubmitted',
                                                    'mentor_approved' => 'Approved by Academic Mentor',
                                                    'mentor_rejected' => 'Returned by Academic Mentor',
                                                    'supervisor_approved' => 'Approved by Industrial Supervisor',
                                                    'supervisor_rejected' => 'Returned by Industrial Supervisor',
                                                    default => Str::headline($event->action),
                                                };
                                                $approvedEvent = str_ends_with($event->action, '_approved');
                                                $rejectedEvent = str_ends_with($event->action, '_rejected');
                                            @endphp
                                            <li class="relative border-l-2 border-slate-200 pb-5 pl-5 last:border-transparent last:pb-0">
                                                <span class="absolute -left-[7px] top-1 h-3 w-3 rounded-full ring-4 ring-white {{ $approvedEvent ? 'bg-emerald-500' : ($rejectedEvent ? 'bg-red-500' : 'bg-indigo-500') }}"></span>
                                                <p class="text-sm font-semibold text-slate-900">{{ $eventLabel }}</p>
                                                <p class="mt-0.5 text-xs capitalize text-slate-500">{{ $event->actor?->name ?? 'Former user' }} ({{ $event->actor_role }}) · {{ $event->created_at->format('M d, Y H:i') }}</p>
                                                @if ($event->feedback)
                                                    <div class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">
                                                        <span class="font-semibold">Feedback:</span> {{ $event->feedback }}
                                                    </div>
                                                @endif
                                            </li>
                                        @endforeach
                                    @endif
                                </ol>
                            </details>
                        </div>

                        @if ($reviewStatus === 'pending')
                            <div class="space-y-4">
                                <form method="POST" action="{{ route($role.'.final-clearances.approve', $clearance) }}" class="space-y-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                                    @csrf
                                    @method('PATCH')
                                    @if ($role === 'supervisor')
                                        <label class="flex items-start gap-3 text-sm text-emerald-900">
                                            <input type="checkbox" name="industrial_hours_completed" value="1" class="mt-1 rounded border-emerald-300 text-emerald-600" required />
                                            I confirm the required industrial hours have been completed.
                                        </label>
                                        <label class="flex items-start gap-3 text-sm text-emerald-900">
                                            <input type="checkbox" name="company_property_cleared" value="1" class="mt-1 rounded border-emerald-300 text-emerald-600" required />
                                            I confirm company property and obligations have been cleared.
                                        </label>
                                    @endif
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-emerald-700">Approve and sign</button>
                                </form>

                                <form method="POST" action="{{ route($role.'.final-clearances.reject', $clearance) }}" class="space-y-3 rounded-lg border border-red-200 bg-red-50 p-4">
                                    @csrf
                                    @method('PATCH')
                                    <x-input-label :for="'feedback_'.$clearance->id" value="Required revision feedback" />
                                    <textarea id="{{ 'feedback_'.$clearance->id }}" name="feedback" rows="3" class="block w-full rounded-md border-red-200 text-sm focus:border-red-500 focus:ring-red-500" required></textarea>
                                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-red-700">Reject for revision</button>
                                </form>
                            </div>
                        @else
                            <div class="flex items-center justify-center rounded-lg border border-slate-200 bg-slate-50 p-6 text-sm text-slate-600">
                                Your review has been recorded.
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <p class="font-semibold text-slate-700">No final clearances assigned to you.</p>
                </div>
            @endforelse

            @if ($clearances->hasPages())
                <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">{{ $clearances->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
