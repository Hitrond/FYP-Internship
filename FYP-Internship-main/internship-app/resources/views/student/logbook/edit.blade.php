<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Student workspace</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Edit Week {{ $logbook->week_number }} Logbook</h2>
            <p class="text-sm text-slate-500">Update your entry before it is approved.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $attendanceEntries = old('attendance', $logbook->attendance_entries ?: array_fill(0, 5, [
                    'status' => 'present',
                    'rendered_minutes' => 480,
                ]));
            @endphp
            @if($logbook->status === 'rejected' && $logbook->supervisor_remarks)
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-5 text-rose-800">
                    <p class="font-semibold">Supervisor feedback</p>
                    <p class="mt-1 text-sm">{{ $logbook->supervisor_remarks }}</p>
                    <p class="mt-2 text-xs text-rose-700">Saving your changes will resubmit this entry for review.</p>
                </div>
            @else
                <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    This entry is pending review. You may edit it until your Supervisor approves it.
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                    Please correct the form errors below.
                </div>
            @endif

            <form action="{{ route('student.logbook.update', $logbook->id) }}" method="POST" enctype="multipart/form-data" data-weekly-logbook-form class="rounded-2xl border border-slate-200 bg-white p-6 text-slate-700 shadow-sm sm:p-8">
                @csrf
                @method('PUT')

                <div class="mb-8 grid grid-cols-1 gap-6 border-b border-slate-200 pb-8 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Start Date</label>
                        <input id="start_date" type="date" name="start_date" value="{{ old('start_date', $logbook->start_date->format('Y-m-d')) }}" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-slate-500">Selecting a date automatically uses that week’s Monday.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">End Date</label>
                        <input id="end_date" type="date" name="end_date" value="{{ old('end_date', $logbook->end_date->format('Y-m-d')) }}" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-slate-500">Automatically set to Friday of the selected week.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-sm font-medium text-indigo-400 mb-2">Type(s) & Objective(s) of the Activities</label>
                    <textarea name="objectives" rows="5" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">{{ old('objectives', $logbook->objectives) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('objectives')" />
                </div>

                <div class="mb-8 border-b border-slate-200 pb-8">
                    <label class="block text-sm font-medium text-indigo-400 mb-2">Content (Knowledge, Skills & Experience)</label>
                    <textarea name="content" rows="6" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-indigo-500 focus:ring-indigo-500">{{ old('content', $logbook->content) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('content')" />
                </div>

                @include('student.logbook.partials.attendance-fields')

                <div class="mb-8 border-b border-slate-200 pb-8">
                    <label class="block text-sm font-medium text-indigo-400 mb-2">Replace Evidence (Optional)</label>
                    @if($logbook->evidence_file_path)
                        <p class="text-xs text-emerald-500 mb-2">A file is attached. Uploading a new one will replace it.</p>
                    @endif
                    <input type="file" name="evidence" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                    <x-input-error class="mt-2" :messages="$errors->get('evidence')" />
                </div>

                <div class="flex justify-end gap-4">
                    <a href="{{ route('student.logbook.show', $logbook->id) }}" class="rounded-xl border border-slate-300 bg-white px-6 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Cancel</a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-lg shadow-indigo-500/30">
                        {{ $logbook->status === 'rejected' ? 'Save & Resubmit' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
