<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-slate-800 leading-tight">Logbook History</h2>
                <p class="text-sm text-slate-500">Previously approved and rejected submissions from your assigned students.</p>
            </div>
            <a href="{{ route('supervisor.logbooks.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                Pending reviews
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('supervisor.logbooks.history') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_180px_160px_auto_auto]">
                <label>
                    <span class="sr-only">Search reviewed logbooks</span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search student / email / TP..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label>
                    <span class="sr-only">Status</span>
                    <select name="status" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Approved & rejected</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    </select>
                </label>
                <label>
                    <span class="sr-only">Week</span>
                    <input type="number" name="week" value="{{ request('week') }}" min="1" max="52" placeholder="Week" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Filter</button>
                <a href="{{ route('supervisor.logbooks.history') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
            </form>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-sm uppercase tracking-wider text-slate-500">
                                <th class="py-4 px-6">Student</th>
                                <th class="py-4 px-6">Week</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6">Declared / verified hours</th>
                                <th class="py-4 px-6">Feedback</th>
                                <th class="py-4 px-6">Reviewed</th>
                                <th class="py-4 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($logbooks as $logbook)
                                <tr>
                                    <td class="py-4 px-6 font-medium text-slate-900">{{ $logbook->student->name }}</td>
                                    <td class="py-4 px-6 text-slate-700">Week {{ $logbook->week_number }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $logbook->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($logbook->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-slate-700">
                                        <p>Declared: <strong>{{ number_format($logbook->rendered_hours, 2) }} hrs</strong></p>
                                        <p class="mt-1 text-emerald-700">Verified: <strong>{{ $logbook->verified_hours !== null ? number_format($logbook->verified_hours, 2).' hrs' : 'Not verified' }}</strong></p>
                                        @if($logbook->attendance_remarks)
                                            <p class="mt-1 text-xs text-amber-700">{{ $logbook->attendance_remarks }}</p>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-sm text-slate-600">{{ $logbook->supervisor_remarks ?: '—' }}</td>
                                    <td class="py-4 px-6 text-sm text-slate-500">{{ $logbook->updated_at->format('M d, Y') }}</td>
                                    <td class="py-4 px-6 text-right">
                                        <a href="{{ route('logbooks.show', $logbook) }}" class="mr-3 text-sm font-semibold text-indigo-600 hover:text-indigo-800">View logbook</a>
                                        @if ($logbook->evidence_file_path)
                                            <a href="{{ route('logbooks.evidence', $logbook) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Download</a>
                                        @else
                                            <span class="text-sm text-slate-400">None</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-slate-500">No reviewed logbooks yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($logbooks->hasPages())
                    <div class="border-t border-slate-200 px-6 py-4">{{ $logbooks->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
