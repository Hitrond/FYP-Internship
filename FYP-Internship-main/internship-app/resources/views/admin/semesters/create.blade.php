<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Academic setup</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $semester->exists ? 'Edit semester' : 'Create a semester' }}</h2>
            <p class="mt-1 text-sm text-slate-500">Set the boundaries, duration, and weekly deadline for one internship cohort.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <form method="POST" action="{{ $semester->exists ? route('admin.semesters.update', $semester) : route('admin.semesters.store') }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @csrf
                @if ($semester->exists) @method('PUT') @endif

                <div class="border-b border-slate-200 px-7 py-6">
                    <h3 class="font-bold text-slate-900">Semester identity</h3>
                        <p class="mt-1 text-sm text-slate-500">Use a clear name and unique intake code for reports.</p>
                </div>

                <div class="grid gap-6 p-7 sm:grid-cols-2">
                    <label class="sm:col-span-2">
                        <span class="mb-2 block text-sm font-bold text-slate-700">Semester name</span>
                        <input name="name" value="{{ old('name', $semester->name) }}" placeholder="September 2026 Internship" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Intake code</span>
                        <input name="intake_code" value="{{ old('intake_code', $semester->intake_code) }}" placeholder="INT-SEP-2026" required class="w-full rounded-xl border-slate-300 uppercase focus:border-indigo-500 focus:ring-indigo-500">
                        @error('intake_code')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Academic year</span>
                        <input name="academic_year" value="{{ old('academic_year', $semester->academic_year) }}" placeholder="2026/2027" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('academic_year')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <div class="sm:col-span-2 mt-2 border-t border-slate-100 pt-6">
                        <h3 class="font-bold text-slate-900">Placement start window</h3>
                        <p class="mt-1 text-sm text-slate-500">Students may start on any date inside this window; it does not have to be a Monday.</p>
                    </div>

                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Window opens</span>
                        <input type="date" name="placement_window_start" value="{{ old('placement_window_start', $semester->placement_window_start?->format('Y-m-d')) }}" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('placement_window_start')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Window closes</span>
                        <input type="date" name="placement_window_end" value="{{ old('placement_window_end', $semester->placement_window_end?->format('Y-m-d')) }}" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('placement_window_end')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Internship duration</span>
                        <input type="number" name="duration_weeks" min="1" max="52" value="{{ old('duration_weeks', $semester->duration_weeks ?? 16) }}" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('duration_weeks')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Timezone</span>
                        <select name="timezone" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Asia/Singapore" @selected(old('timezone', $semester->timezone ?? 'Asia/Singapore') === 'Asia/Singapore')>Singapore (UTC+8)</option>
                            <option value="Asia/Kuala_Lumpur" @selected(old('timezone', $semester->timezone) === 'Asia/Kuala_Lumpur')>Kuala Lumpur (UTC+8)</option>
                            <option value="UTC" @selected(old('timezone', $semester->timezone) === 'UTC')>UTC</option>
                        </select>
                    </label>

                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Weekly deadline day</span>
                        <select name="deadline_weekday" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach([0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'] as $value => $label)
                                <option value="{{ $value }}" @selected((int) old('deadline_weekday', $semester->deadline_weekday ?? 5) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('deadline_weekday')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Weekly deadline time</span>
                        <input type="time" name="deadline_time" value="{{ old('deadline_time', $semester->deadline_time ? \Illuminate\Support\Carbon::parse($semester->deadline_time)->format('H:i') : '23:59') }}" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @error('deadline_time')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
                    </label>

                    <div class="sm:col-span-2 rounded-xl border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-800">
                        Weekly submissions become missed only after the configured deadline passes for that weekly block.
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-7 py-5">
                    <a href="{{ $semester->exists ? route('admin.semesters.show', $semester) : route('admin.semesters.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</a>
                    <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">{{ $semester->exists ? 'Save changes' : 'Create draft' }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
