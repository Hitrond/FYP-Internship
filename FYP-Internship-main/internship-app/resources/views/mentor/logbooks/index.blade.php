<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Event 3 · Internship monitoring</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Student Logbook Monitor</h2>
            <p class="text-sm text-slate-500">Choose a student, understand each status, and open the week that needs attention.</p>
        </div>
    </x-slot>

    @php
        $statusMeta = [
            'approved' => ['Approved', 'Supervisor approved this week.', '✓', 'border-emerald-200 bg-emerald-100 text-emerald-800'],
            'pending' => ['Pending review', 'Waiting for the Industrial Supervisor.', '!', 'border-amber-200 bg-amber-100 text-amber-800'],
            'rejected' => ['Needs revision', 'Returned to the student for correction.', '×', 'border-red-200 bg-red-100 text-red-800'],
            'overdue_locked' => ['Overdue / locked', 'Deadline passed; check any extension request.', '×', 'border-red-300 bg-red-50 text-red-800'],
            'open' => ['Open', 'Available for the student to complete.', '○', 'border-indigo-200 bg-indigo-100 text-indigo-800'],
            'scheduled' => ['Scheduled', 'This future week has not opened.', '–', 'border-slate-200 bg-slate-100 text-slate-600'],
        ];
        $visibleStudents = $selectedStudentId
            ? $students->where('id', $selectedStudentId)
            : $students;
    @endphp

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section aria-labelledby="legend-title" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 id="legend-title" class="font-bold text-slate-900">Logbook status guide</h3>
                <p class="mt-1 text-sm text-slate-500">The symbol and label explain the status, so meaning does not depend on colour alone.</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($statusMeta as [$label, $description, $symbol, $classes])
                        <div class="flex gap-3 rounded-lg border border-slate-200 p-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border text-sm font-black {{ $classes }}">{{ $symbol }}</span>
                            <span><span class="block text-sm font-bold text-slate-800">{{ $label }}</span><span class="block text-xs leading-5 text-slate-500">{{ $description }}</span></span>
                        </div>
                    @endforeach
                </div>
            </section>

            <form method="GET" action="{{ route('mentor.logbooks.index') }}" class="grid gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-[220px_1fr_220px_auto_auto] md:items-end">
                <label>
                    <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Semester</span>
                    <select name="semester" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">Active semester</option>
                        @foreach($cycles as $cycle)
                            <option value="{{ $cycle->id }}" @selected($activeCycle?->id === $cycle->id)>{{ $cycle->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Student</span>
                    <select name="student" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">All assigned students</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" @selected($selectedStudentId === $student->id)>{{ $student->name }}{{ $student->profile?->tp_number ? ' · '.$student->profile->tp_number : '' }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Show status</span>
                    <select name="status" class="w-full rounded-lg border-slate-300 text-sm">
                        <option value="">All statuses</option>
                        @foreach($statusMeta as $value => [$label])
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Apply</button>
                <a href="{{ route('mentor.logbooks.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
            </form>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ $selectedStudentId ? 'Selected student' : 'Assigned students' }}</h3>
                    <p class="text-sm text-slate-500">{{ $visibleStudents->count() }} student{{ $visibleStudents->count() === 1 ? '' : 's' }} shown</p>
                </div>
                @if($selectedStudentId)
                    <a href="{{ route('mentor.logbooks.index', array_filter(['semester' => $activeCycle?->id, 'status' => $status])) }}" class="text-sm font-bold text-indigo-700">← Back to all students</a>
                @endif
            </div>

            <div class="space-y-4">
                @forelse($visibleStudents as $student)
                    @php
                        $studentLogbooks = $status ? $student->logbooks->where('status', $status) : $student->logbooks;
                        $weeklyLogbooks = $student->logbooks->keyBy('week_number');
                        $expectedWeeks = $activeCycle?->duration_weeks ?? max(16, (int) $student->logbooks->where('timeline_generated', true)->max('week_number'));
                        $approved = $student->logbooks->where('status', 'approved')->count();
                        $attention = $student->logbooks->whereIn('status', ['pending', 'rejected', 'overdue_locked'])->count();
                        $percent = $expectedWeeks ? min(100, (int) round(($approved / $expectedWeeks) * 100)) : 0;
                    @endphp
                    <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <a href="{{ route('mentor.logbooks.index', array_filter(['semester' => $activeCycle?->id, 'student' => $student->id, 'status' => $status])) }}" class="text-lg font-bold text-slate-900 hover:text-indigo-700">{{ $student->name }}</a>
                                <p class="text-sm text-slate-500">{{ $student->profile?->tp_number ?? $student->email }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2 text-xs font-bold">
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-emerald-800">{{ $approved }}/{{ $expectedWeeks }} approved</span>
                                @if($attention > 0)<span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800">{{ $attention }} need attention</span>@endif
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">{{ $percent }}% complete</span>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="mb-5 h-2 overflow-hidden rounded-full bg-slate-100" aria-label="{{ $percent }} percent of logbooks approved"><div class="h-full rounded-full bg-emerald-500" style="width: {{ $percent }}%"></div></div>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-8">
                                @foreach($studentLogbooks as $logbook)
                                    @php($meta = $statusMeta[$logbook->status] ?? $statusMeta['scheduled'])
                                    <a href="{{ route('logbooks.show', $logbook) }}" title="Week {{ $logbook->week_number }}: {{ $meta[0] }}" class="rounded-lg border p-3 transition hover:ring-2 hover:ring-indigo-300 {{ $meta[3] }}">
                                        <span class="flex items-center justify-between gap-2"><span class="font-bold">Week {{ $logbook->week_number }}</span><span aria-hidden="true" class="font-black">{{ $meta[2] }}</span></span>
                                        <span class="mt-1 block text-xs font-semibold">{{ $meta[0] }}</span>
                                    </a>
                                @endforeach
                            </div>
                            @if($studentLogbooks->isEmpty())
                                <p class="rounded-lg bg-slate-50 p-5 text-center text-sm text-slate-500">No logbooks match this status filter.</p>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500">No assigned students are available for this semester.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
