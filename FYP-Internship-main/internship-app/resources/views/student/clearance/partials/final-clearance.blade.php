@php
    $canSubmitFinal = ! $finalClearance || $finalClearance->status === 'rejected';
    $hasReviewers = Auth::user()->mentor_id && Auth::user()->supervisor_id;
@endphp

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-xl font-bold text-slate-900">Final Internship Clearance</h3>
                <p class="text-sm text-slate-500">Submit the report and signed company forms separately for dual sign-off.</p>
            </div>
            @if ($finalClearance)
                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold capitalize
                    @if($finalClearance->status === 'completed') bg-emerald-100 text-emerald-800
                    @elseif($finalClearance->status === 'rejected') bg-red-100 text-red-800
                    @else bg-amber-100 text-amber-800 @endif">
                    {{ $finalClearance->status }}
                </span>
            @endif
        </div>
    </div>

    <div class="space-y-6 p-6">
        @if (session('final-success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                {{ session('final-success') }}
            </div>
        @endif
        @if (session('final-error'))
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                {{ session('final-error') }}
            </div>
        @endif

        @if (! $hasReviewers)
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Your university coordinator must assign both a Mentor and Supervisor before you can submit final clearance.
            </div>
        @endif

        @if ($finalClearance)
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach ([
                    ['label' => 'Academic Mentor sign-off', 'status' => $finalClearance->mentor_status, 'feedback' => $finalClearance->mentor_feedback],
                    ['label' => 'Industrial Supervisor sign-off', 'status' => $finalClearance->supervisor_status, 'feedback' => $finalClearance->supervisor_feedback],
                ] as $review)
                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold text-slate-800">{{ $review['label'] }}</p>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize
                                @if($review['status'] === 'approved') bg-emerald-100 text-emerald-800
                                @elseif($review['status'] === 'rejected') bg-red-100 text-red-800
                                @else bg-amber-100 text-amber-800 @endif">
                                {{ $review['status'] }}
                            </span>
                        </div>
                        @if ($review['feedback'])
                            <p class="mt-3 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $review['feedback'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('final-clearances.view', [$finalClearance, 'report']) }}" target="_blank" rel="noopener" class="rounded-lg bg-indigo-100 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-200">
                    View Internship Report
                </a>
                @if ($finalClearance->report_clearance_form_path)
                    <a href="{{ route('final-clearances.view', [$finalClearance, 'report-clearance-form']) }}" target="_blank" rel="noopener" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        View Report Clearance Form
                    </a>
                @endif
                @if ($finalClearance->placementClearance)
                    <a href="{{ route('placement-clearances.view', [$finalClearance->placementClearance, 'placement-agreement']) }}" target="_blank" rel="noopener" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">
                        View placement form
                    </a>
                @endif
            </div>

            @if ($finalClearance->status === 'completed')
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    Final clearance completed {{ $finalClearance->completed_at?->format('M d, Y H:i') }}.
                </div>
            @endif
        @endif

        @if ($canSubmitFinal)
            <form method="POST" action="{{ route('student.final-clearance.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @if ($finalClearance?->status === 'rejected')
                    <p class="rounded-lg bg-blue-50 p-4 text-sm text-blue-800">
                        Upload corrected files to resubmit. Both sign-offs will restart.
                    </p>
                @endif
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                    <p class="font-semibold">Submit these as separate documents:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li>Internship Report - report writing only</li>
                        <li>Report Clearance Form - signed and stamped by the company</li>
                    </ul>
                    <p class="mt-2 text-xs text-blue-700">Attendance is verified through the approved weekly digital logbooks.</p>
                    <p class="mt-2 text-xs text-blue-700">Accepted format: PDF, DOC, or DOCX.</p>
                </div>
                <div>
                    <x-input-label for="final_report" value="Internship Report (writing only)" />
                    <input id="final_report" name="final_report" type="file" accept=".pdf,.doc,.docx" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm" required />
                    <x-input-error class="mt-2" :messages="$errors->get('final_report')" />
                </div>
                <div>
                    <x-input-label for="report_clearance_form" value="Signed Report Clearance Form" />
                    <input id="report_clearance_form" name="report_clearance_form" type="file" accept=".pdf,.doc,.docx" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm" required />
                    <p class="mt-1 text-xs text-slate-500">The blank university form can be added here once it is provided.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('report_clearance_form')" />
                </div>
                <div class="flex justify-end">
                    <button type="submit" @disabled(! $hasReviewers) class="rounded-lg bg-indigo-600 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                        {{ $finalClearance ? 'Resubmit final clearance' : 'Submit final clearance' }}
                    </button>
                </div>
            </form>
        @elseif ($finalClearance?->status === 'pending')
            <p class="text-sm text-slate-500">Your documents are locked while review is in progress.</p>
        @endif
    </div>
</div>
