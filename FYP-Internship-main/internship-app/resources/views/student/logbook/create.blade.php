<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Student workspace</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {{ __('Submit Weekly Logbook') }}
            </h2>
            <p class="text-sm text-slate-500">Record your activities. Your Industrial Supervisor will review and verify this submission.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $selectedWeek = old('week_number', $timelineLogbook?->week_number ?? request('week'));
                $defaultAttendance = collect(range(0, 4))->map(function ($day) use ($timelineLogbook) {
                    return [
                        'date' => $timelineLogbook?->start_date?->copy()->addDays($day)->format('Y-m-d'),
                        'status' => 'present',
                        'rendered_minutes' => 480,
                    ];
                })->all();
                $attendanceEntries = old('attendance', $defaultAttendance);
                $logbook = null;
            @endphp

            @if(session('error'))
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ session('error') }}</div>
            @endif

            <!-- ADDED: enctype="multipart/form-data" to allow file uploads -->
            <form action="{{ route('student.logbook.store') }}" method="POST" enctype="multipart/form-data" data-weekly-logbook-form class="rounded-2xl border border-slate-200 bg-white p-6 text-slate-700 shadow-sm sm:p-8">
                @csrf

                <!-- Date & Week Info -->
                <div class="mb-8 grid grid-cols-1 gap-6 border-b border-slate-200 pb-8 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Internship Week</label>
                        @if($timelineLogbook)
                            <input type="hidden" name="week_number" value="{{ $selectedWeek }}">
                            <div class="rounded-xl border border-slate-300 bg-slate-100 px-4 py-2.5 font-semibold text-slate-900">Week {{ $selectedWeek }}</div>
                        @else
                            <select name="week_number" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Week...</option>
                                @for ($i = 1; $i <= $totalWeeks; $i++)
                                    <option value="{{ $i }}" @selected((int) $selectedWeek === $i)>Week {{ $i }}</option>
                                @endfor
                            </select>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Start Date</label>
                        <input id="start_date" type="date" name="start_date" value="{{ old('start_date', $timelineLogbook?->start_date?->format('Y-m-d')) }}" required @readonly($timelineLogbook) class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-slate-100">
                        <p class="mt-1 text-xs text-slate-500">Generated timelines use the official placement start date for Week 1, then continue by working week.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">End Date</label>
                        <input id="end_date" type="date" name="end_date" value="{{ old('end_date', $timelineLogbook?->end_date?->format('Y-m-d')) }}" required @readonly($timelineLogbook) class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500 read-only:bg-slate-100">
                        <p class="mt-1 text-xs text-slate-500">Automatically set to Friday of the selected week.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                    </div>
                </div>

                <!-- APU Section 1: Objectives -->
                <div class="mb-8">
                    <label class="block text-sm font-medium text-indigo-400 mb-2">Type(s) & Objective(s) of the Activities</label>
                    <p class="text-xs text-slate-500 mb-3">List the main tasks and goals you accomplished this week (bullet points recommended).</p>
                    <textarea name="objectives" rows="5" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-indigo-500 focus:ring-indigo-500" placeholder="• Completed compliance dashboard...&#10;• Validated data accuracy..."></textarea>
                </div>

                <!-- APU Section 2: Content -->
                <div class="mb-8 border-b border-slate-200 pb-8">
                    <label class="block text-sm font-medium text-indigo-400 mb-2">Content (Knowledge, Skills & Experience)</label>
                    <p class="text-xs text-slate-500 mb-3">Describe the technical/non-technical skills developed and relate them to your future career.</p>
                    <textarea name="content" rows="6" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-indigo-500 focus:ring-indigo-500" placeholder="This week marked a significant milestone as I..."></textarea>
                </div>

                @include('student.logbook.partials.attendance-fields')

                <!-- ADDED: Optional Evidence Upload -->
                <div class="mb-8 border-b border-slate-200 pb-8">
                    <label class="block text-sm font-medium text-indigo-400 mb-2">Supporting Evidence (Optional)</label>
                    <p class="text-xs text-slate-500 mb-3">Upload a screenshot, photo, or document to support your entry (maximum 100 MB; PDF, DOC, DOCX, JPG, or PNG).</p>
                    <input type="file" name="evidence" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                </div>

                <!-- Submission Actions -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('student.logbook.index') }}" class="rounded-xl border border-slate-300 bg-white px-6 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-lg shadow-indigo-500/30">
                        Submit for Verification
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
