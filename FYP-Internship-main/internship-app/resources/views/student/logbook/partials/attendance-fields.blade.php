@php
    $attendanceEntries = $attendanceEntries ?? [];
    $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    $statuses = [
        'present' => 'Present',
        'approved_leave' => 'Annual / Approved Leave',
        'medical_leave' => 'Medical Leave (MC)',
        'public_holiday' => 'Public Holiday',
    ];
@endphp

<div class="mb-8 border-b border-slate-200 pb-8">
    <div class="mb-4">
        <h3 class="text-base font-semibold text-indigo-700">Monday–Friday Daily Log</h3>
        <p class="mt-1 text-xs text-slate-500">Record attendance only. Describe your work once in the Objectives and Content sections above.</p>
    </div>

    <div class="space-y-4">
        @foreach ($weekdays as $index => $weekday)
            @php
                $entry = $attendanceEntries[$index] ?? [];
                $status = $entry['status'] ?? 'present';
            @endphp
            <div
                x-data="{ status: @js($status) }"
                class="rounded-xl border border-slate-200 bg-slate-50 p-4"
                :class="{
                    'border-emerald-400': status === 'present',
                    'border-amber-400': status === 'approved_leave',
                    'border-rose-400': status === 'medical_leave',
                    'border-violet-400': status === 'public_holiday'
                }"
            >
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="font-semibold text-slate-900">{{ $weekday }}</p>
                    <span x-show="status !== 'present'" class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600">0 rendered hours</span>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Date</label>
                        <input type="date" name="attendance[{{ $index }}][date]" value="{{ old("attendance.$index.date", $entry['date'] ?? '') }}" data-attendance-date="{{ $index }}" required readonly class="w-full cursor-not-allowed rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-600">
                        <x-input-error class="mt-1" :messages="$errors->get('attendance.'.$index.'.date')" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Day status</label>
                        <select name="attendance[{{ $index }}][status]" x-model="status" required class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div x-show="status === 'present'" class="mt-4 max-w-xs">
                    <label class="mb-1 block text-xs font-medium text-emerald-700">Rendered hours</label>
                    <input type="number" min="0.25" max="24" step="0.25" name="attendance[{{ $index }}][rendered_hours]" value="{{ old("attendance.$index.rendered_hours", isset($entry['rendered_minutes']) ? $entry['rendered_minutes'] / 60 : 8) }}" :required="status === 'present'" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-sm text-slate-900">
                    <x-input-error class="mt-1" :messages="$errors->get('attendance.'.$index.'.rendered_hours')" />
                </div>

                <div x-show="status !== 'present'" class="mt-4">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Note (optional)</label>
                    <input type="text" maxlength="500" name="attendance[{{ $index }}][note]" value="{{ old("attendance.$index.note", $entry['note'] ?? '') }}" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" placeholder="Example: Approved by company supervisor">
                </div>

                <div x-show="status === 'medical_leave'" class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-4">
                    <label class="mb-1 block text-xs font-semibold text-rose-700">Medical Certificate for {{ $weekday }}</label>
                    @if (! empty($entry['mc_evidence_path']) && isset($logbook))
                        <p class="mb-2 text-xs text-emerald-700">MC attached. Upload another file only to replace it.</p>
                    @endif
                    <input type="file" name="attendance[{{ $index }}][mc_evidence]" accept=".jpg,.jpeg,.png,.pdf" :required="status === 'medical_leave' && @js(empty($entry['mc_evidence_path']))" class="w-full text-sm text-rose-700 file:mr-3 file:rounded-md file:border-0 file:bg-rose-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-rose-700">
                    <x-input-error class="mt-1" :messages="$errors->get('attendance.'.$index.'.mc_evidence')" />
                </div>
            </div>
        @endforeach
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('[data-weekly-logbook-form]');
            if (!form) return;

            const startInput = form.querySelector('#start_date');
            const endInput = form.querySelector('#end_date');
            const attendanceInputs = Array.from(form.querySelectorAll('[data-attendance-date]'));

            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            const setSelectedWeek = (value) => {
                if (!value) return;

                const selected = new Date(`${value}T00:00:00`);
                const dayOfWeek = selected.getDay();
                const offsetToMonday = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
                const monday = new Date(selected);
                monday.setDate(selected.getDate() + offsetToMonday);

                startInput.value = formatDate(monday);
                attendanceInputs.forEach((input, index) => {
                    const weekday = new Date(monday);
                    weekday.setDate(monday.getDate() + index);
                    input.value = formatDate(weekday);
                });

                const friday = new Date(monday);
                friday.setDate(monday.getDate() + 4);
                endInput.value = formatDate(friday);
            };

            startInput.addEventListener('change', () => setSelectedWeek(startInput.value));
            endInput.addEventListener('change', () => setSelectedWeek(endInput.value));

            if (startInput.value) {
                setSelectedWeek(startInput.value);
            }
        });
    </script>
@endonce
