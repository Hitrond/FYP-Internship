<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-slate-800 leading-tight">Week {{ $logbook->week_number }} Logbook</h2>
                <p class="text-sm text-slate-500">{{ $logbook->start_date->format('M d, Y') }} - {{ $logbook->end_date->format('M d, Y') }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('student.logbook.index') }}" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-300 transition-colors">Back</a>
                @if(in_array($logbook->status, ['pending', 'rejected'], true))
                    <a href="{{ route('student.logbook.edit', $logbook->id) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:bg-indigo-500 transition-colors">Edit Entry</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        
        <!-- Status Banner -->
        <div class="mb-6 flex flex-col gap-3 rounded-xl border p-4 sm:flex-row sm:items-center sm:justify-between
            @if($logbook->status === 'approved') bg-emerald-50 border-emerald-200 text-emerald-800
            @elseif($logbook->status === 'rejected') bg-red-50 border-red-200 text-red-800
            @else bg-amber-50 border-amber-200 text-amber-800 @endif">
            <div>
                <p class="font-bold">Status: <span class="capitalize">{{ $logbook->status }}</span></p>
                @if($logbook->supervisor_remarks)
                    <p class="text-sm mt-1"><strong>Supervisor Remarks:</strong> {{ $logbook->supervisor_remarks }}</p>
                @endif
            </div>
        </div>

        @include('logbooks.partials.content-sections', ['logbook' => $logbook])

        <div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            @if($logbook->attendance_entries)
                <div class="border-t border-slate-200 p-6">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-bold text-slate-800">Attendance & Rendered Hours</p>
                            <p class="text-sm text-slate-500">Declared: {{ number_format($logbook->rendered_hours, 2) }} hours</p>
                        </div>
                        @if($logbook->verified_hours !== null)
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800">
                                Supervisor verified: {{ number_format($logbook->verified_hours, 2) }} hours
                            </span>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="py-2 pr-4">Date</th>
                                    <th class="py-2 pr-4">Status</th>
                                    <th class="py-2 pr-4">Hours</th>
                                    <th class="py-2">Note / Evidence</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($logbook->attendance_entries as $dayIndex => $entry)
                                    <tr>
                                        <td class="py-3 pr-4 text-slate-700">{{ \Carbon\Carbon::parse($entry['date'])->format('M d, Y') }}</td>
                                        <td class="py-3 pr-4 font-medium capitalize text-slate-800">{{ str_replace('_', ' ', $entry['status']) }}</td>
                                        <td class="py-3 pr-4 text-slate-700">{{ number_format(($entry['rendered_minutes'] ?? 0) / 60, 2) }}</td>
                                        <td class="py-3 text-slate-500">
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
                    @if($logbook->attendance_remarks)
                        <p class="mt-4 rounded-lg bg-amber-50 p-3 text-sm text-amber-800"><strong>Attendance note:</strong> {{ $logbook->attendance_remarks }}</p>
                    @endif
                </div>
            @endif

            <div class="border-t border-emerald-200 bg-emerald-50 p-6">
                <p class="font-bold text-emerald-900">Digital verification</p>
                @if($logbook->status === 'approved' && $logbook->approval_signature_path && $logbook->approval_stamp_path)
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-bold text-emerald-900">Digitally verified by the Industrial Supervisor</p>
                            <dl class="mt-2 space-y-1 text-sm text-emerald-800">
                                <div><dt class="inline font-semibold">Approved by:</dt> <dd class="inline">{{ $logbook->approvedBy?->name }}</dd></div>
                                @if($logbook->approval_company_name)
                                    <div><dt class="inline font-semibold">Company:</dt> <dd class="inline">{{ $logbook->approval_company_name }}</dd></div>
                                @endif
                                <div><dt class="inline font-semibold">Approval timestamp:</dt> <dd class="inline">{{ $logbook->approved_at?->format('M d, Y, h:i:s A') }}</dd></div>
                            </dl>
                        </div>
                        <div class="flex items-center gap-5 rounded-lg border border-emerald-200 bg-white p-4">
                            <div class="text-center">
                                <img src="{{ route('logbooks.approval-asset', [$logbook, 'signature']) }}" alt="Industrial Supervisor e-signature" class="mx-auto max-h-16 max-w-36 object-contain">
                                <p class="mt-1 text-xs font-semibold text-slate-500">E-signature</p>
                            </div>
                            <div class="text-center">
                                <img src="{{ route('logbooks.approval-asset', [$logbook, 'stamp']) }}" alt="Company stamp" class="mx-auto max-h-20 max-w-36 object-contain">
                                <p class="mt-1 text-xs font-semibold text-slate-500">Company stamp</p>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="mt-2 text-sm text-emerald-800">Not digitally signed and stamped yet.</p>
                @endif
            </div>
            
            @if($logbook->evidence_file_path)
                <div class="flex flex-col gap-4 border-t border-slate-200 bg-slate-50 p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-slate-700">Attached Evidence</p>
                        <p class="text-xs text-slate-500">A file was uploaded with this entry.</p>
                    </div>
                    <a href="{{ route('logbooks.evidence', $logbook) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-4 py-2 rounded-lg transition-colors">Download File &rarr;</a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
