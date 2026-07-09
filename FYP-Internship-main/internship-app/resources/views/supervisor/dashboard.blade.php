<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Company workspace</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Industrial Supervisor Dashboard</h2>
            <p class="text-sm text-slate-500">Review attendance, logbooks, evaluations, and final clearance.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('supervisor.dashboard') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_220px_auto_auto]">
                <label>
                    <span class="sr-only">Search students</span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search assigned student / email / TP..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label>
                    <span class="sr-only">Recent logbook status</span>
                    <select name="logbook_status" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All recent logbooks</option>
                        <option value="pending" @selected(request('logbook_status') === 'pending')>Pending</option>
                        <option value="approved" @selected(request('logbook_status') === 'approved')>Approved</option>
                        <option value="rejected" @selected(request('logbook_status') === 'rejected')>Rejected</option>
                        <option value="open" @selected(request('logbook_status') === 'open')>Open</option>
                        <option value="overdue_locked" @selected(request('logbook_status') === 'overdue_locked')>Missed / locked</option>
                    </select>
                </label>
                <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Filter</button>
                <a href="{{ route('supervisor.dashboard') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
            </form>

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                @foreach([
                    ['label' => 'Assigned students', 'value' => $students->count(), 'color' => 'text-slate-900'],
                    ['label' => 'Pending logbooks', 'value' => $pendingLogbooks, 'color' => 'text-indigo-700'],
                    ['label' => 'Attendance issues', 'value' => $attendanceIssues, 'color' => 'text-red-700'],
                    ['label' => 'Evaluations due', 'value' => $pendingEvaluations, 'color' => 'text-amber-700'],
                    ['label' => 'Clearances due', 'value' => $pendingClearances, 'color' => 'text-emerald-700'],
                ] as $metric)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $metric['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold {{ $metric['color'] }}">{{ $metric['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <a href="{{ route('supervisor.logbooks.index') }}" class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 transition hover:-translate-y-0.5 hover:bg-indigo-100 hover:shadow-lg hover:shadow-indigo-900/5">
                    <p class="font-bold text-indigo-900">Review pending logbooks</p>
                    <p class="mt-1 text-sm text-indigo-700">Approve, sign, stamp, or return submissions.</p>
                </a>
                <a href="{{ route('supervisor.evaluations.index') }}" class="rounded-2xl border border-amber-200 bg-amber-50 p-5 transition hover:-translate-y-0.5 hover:bg-amber-100 hover:shadow-lg hover:shadow-amber-900/5">
                    <p class="font-bold text-amber-900">Performance evaluations</p>
                    <p class="mt-1 text-sm text-amber-700">Complete midterm and final assessments.</p>
                </a>
                <a href="{{ route('supervisor.final-clearances.index') }}" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 transition hover:-translate-y-0.5 hover:bg-emerald-100 hover:shadow-lg hover:shadow-emerald-900/5">
                    <p class="font-bold text-emerald-900">Final clearance</p>
                    <p class="mt-1 text-sm text-emerald-700">Confirm hours and company obligations.</p>
                </a>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h3 class="font-bold text-slate-900">Recent student logbooks</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Student</th>
                                <th class="px-6 py-3">Week</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Hours</th>
                                <th class="px-6 py-3">Updated</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentLogbooks as $logbook)
                                <tr>
                                    <td class="px-6 py-4 font-semibold text-slate-900">{{ $logbook->student->name }}</td>
                                    <td class="px-6 py-4 text-slate-700">Week {{ $logbook->week_number }}</td>
                                    <td class="px-6 py-4 capitalize text-slate-700">{{ $logbook->status }}</td>
                                    <td class="px-6 py-4 text-slate-700">{{ $logbook->verified_hours !== null ? number_format($logbook->verified_hours, 2) : number_format($logbook->rendered_hours, 2) }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $logbook->updated_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('logbooks.show', $logbook) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">View logbook</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-10 text-center text-slate-500">No student logbooks yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
