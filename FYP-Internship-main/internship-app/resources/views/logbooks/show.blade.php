<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Weekly logbook</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $logbook->student->name }} — Week {{ $logbook->week_number }}</h2>
                <p class="text-sm text-slate-500">{{ $logbook->start_date->format('M d, Y') }} to {{ $logbook->end_date->format('M d, Y') }}</p>
            </div>
            <span class="w-fit rounded-full px-3 py-1 text-xs font-bold capitalize
                @if($logbook->status === 'approved') bg-emerald-100 text-emerald-800
                @elseif($logbook->status === 'rejected') bg-red-100 text-red-800
                @else bg-amber-100 text-amber-800 @endif">
                {{ $logbook->status }}
            </span>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if($logbook->supervisor_remarks)
                <div class="rounded-xl border border-red-200 bg-red-50 p-5 text-red-800">
                    <p class="font-bold">Industrial Supervisor feedback</p>
                    <p class="mt-1 text-sm">{{ $logbook->supervisor_remarks }}</p>
                </div>
            @endif

            @include('logbooks.partials.content-sections', ['logbook' => $logbook])

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-6">
                    <h3 class="font-bold text-slate-900">Attendance and working hours</h3>
                    <div class="flex flex-wrap gap-3 text-sm">
                        <span class="rounded-lg bg-indigo-50 px-3 py-2 text-indigo-800">
                            Student declared: <strong>{{ number_format($logbook->rendered_hours, 2) }} hrs</strong>
                        </span>
                        <span class="rounded-lg bg-emerald-50 px-3 py-2 text-emerald-800">
                            Supervisor verified:
                            <strong>{{ $logbook->verified_hours !== null ? number_format($logbook->verified_hours, 2).' hrs' : 'Not verified' }}</strong>
                        </span>
                    </div>
                </div>
                @if($logbook->attendance_entries)
                    <div class="overflow-x-auto p-6">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="py-3 pr-4">Date</th>
                                    <th class="py-3 pr-4">Status</th>
                                    <th class="py-3 pr-4">Hours</th>
                                    <th class="py-3">Note / Evidence</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($logbook->attendance_entries as $dayIndex => $entry)
                                    <tr>
                                        <td class="py-3 pr-4">{{ \Carbon\Carbon::parse($entry['date'])->format('D, M d, Y') }}</td>
                                        <td class="py-3 pr-4 capitalize">{{ str_replace('_', ' ', $entry['status']) }}</td>
                                        <td class="py-3 pr-4">{{ number_format(($entry['rendered_minutes'] ?? 0) / 60, 2) }}</td>
                                        <td class="py-3 text-slate-600">
                                            {{ $entry['note'] ?: '—' }}
                                            @if(! empty($entry['mc_evidence_path']))
                                                <a href="{{ route('logbooks.mc-evidence', [$logbook, $dayIndex]) }}" class="ml-2 font-semibold text-red-600 hover:text-red-800">View MC</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="p-6 text-sm text-slate-500">A daily attendance breakdown was not recorded for this entry. The total declared and verified hours remain available above.</p>
                @endif
                @if($logbook->attendance_remarks)
                    <div class="border-t border-slate-100 bg-amber-50 px-6 py-4 text-sm text-amber-900">
                        <strong>Supervisor hours note:</strong> {{ $logbook->attendance_remarks }}
                    </div>
                @endif
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-slate-900">Supporting evidence</h3>
                    @if($logbook->evidence_file_path)
                        <a href="{{ route('logbooks.evidence', $logbook) }}" class="mt-3 inline-flex rounded-lg bg-indigo-100 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-200">Download evidence</a>
                    @else
                        <p class="mt-2 text-sm text-slate-500">No supporting evidence attached.</p>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-slate-900">Digital verification</h3>
                    @if($logbook->status === 'approved' && $logbook->approval_signature_path && $logbook->approval_stamp_path)
                        <p class="mt-1 text-sm text-slate-600">Signed by {{ $logbook->approvedBy?->name }} on {{ $logbook->approved_at?->format('M d, Y H:i') }}.</p>
                        <div class="mt-4 flex items-center gap-5">
                            <img src="{{ route('logbooks.approval-asset', [$logbook, 'signature']) }}" alt="E-signature" class="max-h-16 max-w-36 object-contain">
                            <img src="{{ route('logbooks.approval-asset', [$logbook, 'stamp']) }}" alt="Company stamp" class="max-h-20 max-w-36 object-contain">
                        </div>
                    @else
                        <p class="mt-2 text-sm text-slate-500">Not digitally signed and stamped yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
