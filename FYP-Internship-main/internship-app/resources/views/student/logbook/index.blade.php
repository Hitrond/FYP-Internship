<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Student workspace</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Weekly Logbook Timeline</h2>
            <p class="text-sm text-slate-500">Your {{ $totalWeeks }} weekly blocks, submission deadlines, and Industrial Supervisor decisions.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ session('error') }}</div>
            @endif

            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full min-w-[900px] text-left">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="p-5">Week</th>
                            <th class="p-5">Date range</th>
                            <th class="p-5">Deadline</th>
                            <th class="p-5">Status</th>
                            <th class="p-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        @for($week = 1; $week <= $totalWeeks; $week++)
                            @php($logbook = $logbooks->get($week))
                            <tr class="align-top transition hover:bg-slate-50">
                                <td class="p-5 font-bold text-slate-900">Week {{ $week }}</td>
                                <td class="p-5">
                                    @if($logbook)
                                        {{ $logbook->start_date->format('M d') }} - {{ $logbook->end_date->format('M d, Y') }}
                                    @else
                                        <span class="text-slate-400">Generated after placement approval</span>
                                    @endif
                                </td>
                                <td class="p-5 text-sm">
                                    @if($logbook?->submission_due_at)
                                        {{ $logbook->submission_due_at->format('M d, Y 11:59 A') }}
                                        @if($logbook->extension_status === 'approved' && $logbook->extension_until)
                                            <p class="mt-1 text-xs text-emerald-400">Extended to {{ $logbook->extension_until->format('M d, Y H:i') }}</p>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="p-5">
                                    @if(!$logbook)
                                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Not generated</span>
                                    @elseif($logbook->status === 'approved')
                                        <span class="rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Approved & signed</span>
                                    @elseif($logbook->status === 'rejected')
                                        <span class="rounded-full border border-rose-200 bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Needs revision</span>
                                    @elseif($logbook->status === 'pending')
                                        <span class="rounded-full border border-amber-200 bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Pending review</span>
                                    @elseif($logbook->status === 'open')
                                        <span class="rounded-full border border-indigo-200 bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">Open</span>
                                    @elseif($logbook->status === 'overdue_locked')
                                        <span class="rounded-full border border-rose-200 bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Overdue / locked</span>
                                    @else
                                        <span class="rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Scheduled</span>
                                    @endif
                                </td>
                                <td class="p-5">
                                    <div class="flex flex-col items-end gap-3">
                                        @if($logbook?->description)
                                            <div class="flex gap-3">
                                                <a href="{{ route('student.logbook.show', $logbook) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">View</a>
                                                @if(in_array($logbook->status, ['pending', 'rejected'], true))
                                                    <a href="{{ route('student.logbook.edit', $logbook) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">{{ $logbook->status === 'rejected' ? 'Edit & resubmit' : 'Edit' }}</a>
                                                @endif
                                            </div>
                                        @elseif($logbook?->status === 'open')
                                            <a href="{{ route('student.logbook.create', ['week' => $week]) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Submit week</a>
                                        @elseif($logbook?->status === 'scheduled')
                                            <p class="text-xs text-slate-500">Opens {{ $logbook->start_date->format('M d, Y') }}</p>
                                        @elseif($logbook?->status === 'overdue_locked')
                                            @if($logbook->extension_status === 'requested')
                                                <p class="text-xs font-semibold text-amber-700">Extension requested</p>
                                            @elseif($logbook->extension_status === 'rejected')
                                                <p class="text-xs text-rose-700">{{ $logbook->extension_decision_note }}</p>
                                            @else
                                                <form method="POST" action="{{ route('student.logbook.extension.request', $logbook) }}" class="flex max-w-sm flex-col items-end gap-2">
                                                    @csrf
                                                    <textarea name="extension_reason" rows="2" required class="w-72 rounded-lg border-slate-300 bg-white text-sm text-slate-900" placeholder="Explain why an extension is needed"></textarea>
                                                    <button class="rounded-lg bg-amber-600 px-3 py-2 text-xs font-bold uppercase text-white hover:bg-amber-500">Request extension</button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
