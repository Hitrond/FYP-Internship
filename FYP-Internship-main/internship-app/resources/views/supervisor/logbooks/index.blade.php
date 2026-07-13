<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Company workspace</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Industrial Supervisor Logbook Approvals') }}
                </h2>
                <p class="text-sm text-slate-500">Review submitted logbooks and approve or reject each week.</p>
            </div>
            <a href="{{ route('supervisor.logbooks.history') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                View history
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if (session('error') || $errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    {{ session('error') ?: $errors->first() }}
                </div>
            @endif
            @unless($canSignLogbooks)
                <div class="flex flex-col gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between">
                    <p>Approval requires your e-signature and company stamp.</p>
                    <a href="{{ route('supervisor.profile.edit') }}" class="font-bold text-amber-800 underline hover:text-amber-950">Upload verification assets</a>
                </div>
            @endunless
            <form method="GET" action="{{ route('supervisor.logbooks.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_160px_auto_auto]">
                <label>
                    <span class="sr-only">Search pending logbooks</span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search student / email / TP..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label>
                    <span class="sr-only">Week</span>
                    <input type="number" name="week" value="{{ request('week') }}" min="1" max="52" placeholder="Week" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Filter</button>
                <a href="{{ route('supervisor.logbooks.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
            </form>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-sm uppercase tracking-wider text-slate-500">
                                    <th class="py-4 px-6 font-semibold">Student</th>
                                    <th class="py-4 px-6 font-semibold">Week</th>
                                    <th class="py-4 px-6 font-semibold">Summary</th>
                                    <th class="py-4 px-6 font-semibold">Submitted</th>
                                    <th class="py-4 px-6 font-semibold text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
    @forelse($pendingLogbooks as $logbook)
        <tr x-data="{ showView: false, showReject: false }" class="transition-colors hover:bg-slate-50">

            <td class="p-4 font-bold text-slate-900">{{ $logbook->student->name }}</td>
            <td class="p-4 text-slate-700">Week {{ $logbook->week_number }}</td>
            <td class="p-4 text-sm text-slate-500">{{ Str::limit($logbook->description, 40) }}</td>
            <td class="p-4 text-sm text-slate-500">{{ $logbook->created_at->format('M d, Y') }}</td>

            <td class="p-4 text-right flex items-center justify-end gap-2 relative">

                <button @click="showView = true" type="button" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-bold text-indigo-700 transition hover:bg-indigo-100">
                    Review submission
                </button>

                <form action="{{ route('supervisor.logbooks.approve', $logbook->id) }}" method="POST" class="m-0 p-0">
                    @csrf
                    @method('PATCH')
                    @if($logbook->attendance_entries)
                        <input type="hidden" name="verified_hours" value="{{ $logbook->rendered_hours }}">
                    @endif
                    <button type="submit" @disabled(!$canSignLogbooks) class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-40">
                        Approve
                    </button>
                </form>

                <button @click="showReject = true" type="button" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-bold text-rose-700 transition hover:bg-rose-100">
                    Reject
                </button>

                <div x-show="showView" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
                    <div @click.away="showView = false" class="mx-4 max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 text-left shadow-2xl">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-slate-900">Logbook Details: Week {{ $logbook->week_number }}</h3>
                            <button @click="showView = false" class="text-2xl text-slate-400 hover:text-slate-700">&times;</button>
                        </div>



@include('logbooks.partials.content-sections', ['logbook' => $logbook])

<div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
    <div class="flex flex-wrap items-center justify-between gap-2 bg-slate-50 px-4 py-3">
        <p class="font-semibold text-slate-900">Attendance and working hours</p>
        <span class="text-sm font-semibold text-indigo-700">Student declared: {{ number_format($logbook->rendered_hours, 2) }} hrs</span>
    </div>
    @if($logbook->attendance_entries)
        <div class="max-h-64 overflow-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Date</th>
                        <th class="px-3 py-2 text-left">Status</th>
                        <th class="px-3 py-2 text-right">Hours</th>
                        <th class="px-3 py-2 text-left">Note / Evidence</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($logbook->attendance_entries as $dayIndex => $entry)
                        <tr>
                            <td class="px-3 py-2">{{ \Carbon\Carbon::parse($entry['date'])->format('M d') }}</td>
                            <td class="px-3 py-2">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold capitalize
                                    @if($entry['status'] === 'present') bg-emerald-100 text-emerald-700
                                    @elseif($entry['status'] === 'medical_leave') bg-rose-100 text-rose-700
                                    @elseif($entry['status'] === 'approved_leave') bg-amber-100 text-amber-700
                                    @else bg-violet-100 text-violet-700 @endif">
                                    {{ str_replace('_', ' ', $entry['status']) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">{{ number_format(($entry['rendered_minutes'] ?? 0) / 60, 2) }}</td>
                            <td class="px-3 py-2 text-slate-500">
                                {{ $entry['note'] ?: '—' }}
                                @if(! empty($entry['mc_evidence_path']))
                                    <a href="{{ route('logbooks.mc-evidence', [$logbook, $dayIndex]) }}" class="ml-2 font-semibold text-rose-600 hover:text-rose-800">View MC</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="px-4 py-3 text-sm text-slate-500">No daily attendance breakdown was recorded. Verify the declared total before approval.</p>
    @endif
</div>
<form action="{{ route('supervisor.logbooks.approve', $logbook->id) }}" method="POST" class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        @csrf
        @method('PATCH')
        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-xs font-semibold text-emerald-800">Verified hours</label>
                <input type="number" name="verified_hours" value="{{ $logbook->rendered_hours }}" min="0" max="168" step="0.01" required class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-slate-900">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-emerald-800">Attendance remark</label>
                <input type="text" name="attendance_remarks" maxlength="2000" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-slate-900" placeholder="Required when reducing hours">
            </div>
        </div>
        <button type="submit" @disabled(!$canSignLogbooks) class="mt-3 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-40">Verify, sign & approve</button>
</form>

@if($logbook->evidence_file_path)
    <div class="mt-4 flex items-center justify-between rounded-xl border border-indigo-200 bg-indigo-50 p-4">
        <span class="text-sm font-medium text-indigo-800">Evidence file attached</span>
        <a href="{{ route('logbooks.evidence', $logbook) }}"
           class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-bold transition">
           Download / View Evidence
        </a>
    </div>
@else
    <p class="mt-4 text-sm italic text-slate-500">No evidence file was uploaded for this week.</p>
@endif

                        <div class="mt-6 flex justify-end">
                            <button @click="showView = false" class="rounded-lg bg-slate-800 px-4 py-2 text-white transition hover:bg-slate-700">Close</button>
                        </div>
                    </div>
                </div>

                <div x-show="showReject" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-opacity">
                    <div @click.away="showReject = false" class="mx-4 w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 text-left shadow-2xl">
                        <h3 class="mb-2 text-xl font-bold text-rose-700">Reject Logbook</h3>
                        <p class="mb-4 text-sm text-slate-500">Feedback is mandatory. The student will use it to correct and resubmit the week.</p>

                        <form action="{{ route('supervisor.logbooks.reject', $logbook->id) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <label class="mb-1 block text-xs font-semibold text-rose-700">Reason category</label>
                            <select name="issue_type" required class="mb-3 w-full rounded-lg border-slate-300 bg-white p-3 text-slate-800">
                                <option value="content">Logbook content needs revision</option>
                                <option value="attendance">Attendance issue / unapproved absence</option>
                            </select>
                            <p class="mb-3 text-xs text-amber-700">Attendance issues also create a red alert for the assigned Academic Mentor.</p>
                            <textarea name="reason" rows="4" required class="w-full rounded-lg border-slate-300 bg-white p-3 text-slate-800 placeholder-slate-400 focus:border-rose-500 focus:ring-rose-500" placeholder="Example: Student was absent on Thursday without approval."></textarea>

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" @click="showReject = false" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-slate-700 transition hover:bg-slate-50">Cancel</button>
                                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg font-medium shadow-lg transition">Confirm Rejection</button>
                            </div>
                        </form>
                    </div>
                </div>

            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="border-t border-slate-100 p-8 text-center text-slate-500">
                No pending logbooks require your approval right now.
            </td>
        </tr>
    @endforelse
</tbody>
                        </table>
                    </div>
                    @if ($pendingLogbooks->hasPages())
                        <div class="mt-6 border-t border-slate-200 pt-4">{{ $pendingLogbooks->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
