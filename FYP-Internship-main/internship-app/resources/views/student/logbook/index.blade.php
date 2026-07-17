<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Student workspace</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Weekly Logbook Timeline</h2>
            <p class="text-sm text-slate-500">Your {{ $totalWeeks }} weekly blocks, submission deadlines, and Industrial Supervisor decisions.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div x-data="{ extensionOpen: false, extensionAction: '', extensionWeek: '' }" class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ session('error') }}</div>
            @endif

            <form method="GET" action="{{ route('student.logbook.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_220px_auto_auto]">
                <label><span class="sr-only">Search week</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Search week number..." class="w-full rounded-xl border-slate-300 text-sm"></label>
                <label><span class="sr-only">Logbook status</span><select name="status" class="w-full rounded-xl border-slate-300 text-sm">
                    <option value="">All statuses</option>
                    @foreach(['approved' => 'Approved & signed', 'pending' => 'Pending review', 'rejected' => 'Needs revision', 'open' => 'Open', 'overdue_locked' => 'Overdue / locked', 'scheduled' => 'Scheduled', 'not_generated' => 'Not generated'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select></label>
                <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white">Filter</button>
                <a href="{{ route('student.logbook.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-center text-sm font-bold text-slate-700">Reset</a>
            </form>

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
                        @forelse($weekEntries as $entry)
                            @php($week = $entry['week'])
                            @php($logbook = $entry['logbook'])
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
                                                <button type="button" @click="extensionAction = '{{ route('student.logbook.extension.request', $logbook) }}'; extensionWeek = '{{ $week }}'; extensionOpen = true" class="rounded-lg bg-amber-600 px-3 py-2 text-xs font-bold uppercase text-white hover:bg-amber-500">Request extension</button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-10 text-center text-sm text-slate-500">No weekly logbooks match your search or filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($weekEntries->hasPages())
                <div>{{ $weekEntries->links() }}</div>
            @endif

            <div x-show="extensionOpen" x-cloak @keydown.escape.window="extensionOpen = false" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="extension-title">
                <button type="button" @click="extensionOpen = false" class="absolute inset-0 bg-slate-900/60" aria-label="Close extension request"></button>
                <div x-show="extensionOpen" x-transition class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div><p class="text-xs font-bold uppercase tracking-wider text-amber-600">Overdue / locked</p><h3 id="extension-title" class="mt-1 text-xl font-bold text-slate-900">Request extension for Week <span x-text="extensionWeek"></span></h3></div>
                        <button type="button" @click="extensionOpen = false" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Close">×</button>
                    </div>
                    <form method="POST" :action="extensionAction" class="mt-5 space-y-4">
                        @csrf
                        <label><span class="mb-2 block text-sm font-bold text-slate-700">Why is an extension necessary?</span><textarea name="extension_reason" rows="4" required class="w-full rounded-xl border-slate-300 text-sm" placeholder="Briefly explain the circumstances and the time you need."></textarea></label>
                        <div class="flex justify-end gap-3"><button type="button" @click="extensionOpen = false" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700">Cancel</button><button class="rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-700">Send request</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
