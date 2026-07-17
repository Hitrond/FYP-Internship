<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Placement administration</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-900">Placement & Supervisor Account Management</h2>
                <p class="mt-1 text-sm text-slate-500">Track every student and create Industrial Supervisor login emails after mentor approval.</p>
            </div>
            <a href="{{ route('admin.clearances.export', request()->query()) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg>
                Export filtered CSV
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            @endif

            <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ([
                    ['Total students', $summary['total'], 'text-slate-900'],
                    ['Active placements', $summary['active'], 'text-indigo-700'],
                    ['Final review pending', $summary['final_pending'], 'text-amber-700'],
                    ['Fully cleared', $summary['completed'], 'text-emerald-700'],
                ] as [$label, $value, $colour])
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
                        <p class="mt-2 text-3xl font-bold {{ $colour }}">{{ $value }}</p>
                    </div>
                @endforeach
            </section>

            <form method="GET" action="{{ route('admin.clearances.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-2 xl:grid-cols-[220px_1fr_210px_170px_auto]">
                <label>
                    <span class="sr-only">Semester</span>
                    <select name="semester" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @if ($cycles->isEmpty())<option value="">Legacy records</option>@endif
                        @foreach ($cycles as $cycle)
                            <option value="{{ $cycle->id }}" @selected($selectedCycle?->id === $cycle->id)>{{ $cycle->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="sr-only">Search students</span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, ID, or email..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label>
                    <span class="sr-only">Placement filter</span>
                    <select name="placement" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All placement stages</option>
                        <option value="unassigned" @selected(request('placement') === 'unassigned')>Mentor unassigned</option>
                        <option value="not_submitted" @selected(request('placement') === 'not_submitted')>Not submitted</option>
                        <option value="pending" @selected(request('placement') === 'pending')>Awaiting approval</option>
                        <option value="active" @selected(request('placement') === 'active')>Active placement</option>
                    </select>
                </label>
                <label>
                    <span class="sr-only">Result filter</span>
                    <select name="result" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All results</option>
                        <option value="pending" @selected(request('result') === 'pending')>Result pending</option>
                        <option value="pass" @selected(request('result') === 'pass')>Pass</option>
                        <option value="fail" @selected(request('result') === 'fail')>Fail</option>
                    </select>
                </label>
                <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Apply filters</button>
            </form>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-[1120px] w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-4">Student</th>
                                <th class="px-5 py-4">Assigned team</th>
                                <th class="px-5 py-4">Placement</th>
                                <th class="px-5 py-4">Logbooks</th>
                                <th class="px-5 py-4">Final clearance</th>
                                <th class="px-5 py-4">Result</th>
                                <th class="px-5 py-4 text-right">Admin action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($students as $student)
                                @php($placement = $student->latestPlacementClearance)
                                @php($expectedWeeks = $placement?->cycle?->duration_weeks ?? max(16, $student->generated_logbooks_count))
                                <tr class="align-top hover:bg-slate-50">
                                    <td class="px-5 py-4">
                                        <p class="font-bold text-slate-900">{{ $student->name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $student->profile?->tp_number ?? $student->email }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-slate-700"><span class="text-xs text-slate-400">AM:</span> {{ $student->mentor?->name ?? 'Unassigned' }}</p>
                                        <p class="mt-1 text-slate-700"><span class="text-xs text-slate-400">IS:</span> {{ $student->supervisor?->name ?? 'Not provisioned' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($placement)
                                            <p class="font-semibold text-slate-800">{{ $placement->company_name }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ $placement->start_date?->format('d M Y') ?? 'Dates pending' }}</p>
                                            <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-bold capitalize
                                                {{ in_array($placement->status, ['approved', 'completed'], true) ? 'bg-emerald-100 text-emerald-700' : ($placement->status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                                {{ $placement->status }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">Not submitted</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="font-bold text-slate-900">{{ $student->approved_logbooks_count }} / {{ $expectedWeeks }}</p>
                                        <div class="mt-2 h-1.5 w-28 overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full rounded-full bg-indigo-500" style="width: {{ min(100, round(($student->approved_logbooks_count / max(1, $expectedWeeks)) * 100)) }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        @php($finalStatus = $student->finalClearance?->status ?? 'not submitted')
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold capitalize
                                            {{ $finalStatus === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($finalStatus === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600') }}">
                                            {{ $finalStatus }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($student->internshipResult)
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $student->internshipResult->result === 'pass' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                {{ ucfirst($student->internshipResult->result) }}
                                            </span>
                                            <p class="mt-1 text-xs text-slate-500">Score {{ $student->internshipResult->supervisor_score }}/10</p>
                                        @else
                                            <span class="text-slate-400">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        @if ($placement && $placement->status === 'approved' && ! $student->supervisor_id)
                                            <form method="POST" action="{{ route('admin.clearances.generate-supervisor', $placement) }}">
                                                @csrf
                                                <button class="rounded-lg bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100">Create supervisor login</button>
                                                <p class="mt-1 text-[11px] text-slate-400">{{ $placement->supervisor_personal_email }}</p>
                                            </form>
                                        @else
                                            <a href="{{ route('admin.users.edit', $student) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Manage student</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500">No students match these filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($students->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $students->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
