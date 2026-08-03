<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">{{ $semester->name }}</h2>
                    <span class="rounded-full px-3 py-1 text-xs font-bold capitalize
                        {{ $semester->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($semester->status === 'draft' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600') }}">
                        {{ $semester->status }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-500">{{ $semester->intake_code }} · {{ $semester->academic_year }} · {{ $semester->duration_weeks }} weeks · missed after {{ ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$semester->deadline_weekday ?? 5] }} {{ \Illuminate\Support\Carbon::parse($semester->deadline_time ?? '23:59')->format('H:i') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if (in_array($semester->status, ['draft', 'active'], true))
                    <a href="{{ route('admin.semesters.edit', $semester) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Edit semester settings</a>
                @endif
                @if ($semester->status === 'draft')
                    <form method="POST" action="{{ route('admin.semesters.activate', $semester) }}">
                        @csrf @method('PATCH')
                        <button class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">Activate semester</button>
                    </form>
                @elseif ($semester->status === 'active')
                    <form method="POST" action="{{ route('admin.semesters.close', $semester) }}" onsubmit="return confirm('Close this semester? New placement submissions will be disabled.');">
                        @csrf @method('PATCH')
                        <button class="rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600">Close semester</button>
                    </form>
                @elseif ($semester->status === 'closed')
                    <form method="POST" action="{{ route('admin.semesters.archive', $semester) }}">
                        @csrf @method('PATCH')
                        <button class="rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Archive semester</button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800">{{ session('error') }}</div>
            @endif

            <section class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                @foreach ([
                    ['Students', $semester->assignments_count, 'text-slate-900'],
                    ['Placements', $semester->approved_placements_count.'/'.$semester->assignments_count, 'text-indigo-700'],
                    ['Approved logs', $semester->approved_logbooks_count, 'text-emerald-700'],
                    ['Overdue logs', $semester->overdue_logbooks_count, 'text-rose-700'],
                    ['Start window', $semester->placement_window_start->format('d M').' – '.$semester->placement_window_end->format('d M Y'), 'text-slate-900'],
                ] as [$label, $value, $colour])
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>
                        <p class="mt-2 text-xl font-bold {{ $colour }}">{{ $value }}</p>
                    </article>
                @endforeach
            </section>

            @if (in_array($semester->status, ['draft', 'active'], true))
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h3 class="font-bold text-slate-900">Add students to this cohort</h3>
                        <p class="mt-1 text-sm text-slate-500">Select multiple students and optionally assign one Academic Mentor in the same action. Students enrolled in another active semester are unavailable until removed from that semester.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.semesters.students.store', $semester) }}" class="grid gap-4 lg:grid-cols-[1fr_280px_auto] lg:items-end">
                        @csrf
                        <label>
                            <span class="mb-2 block text-sm font-bold text-slate-700">Students</span>
                            <select name="student_ids[]" multiple required size="5" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @forelse ($availableStudents as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }}{{ $student->profile?->tp_number ? ' · '.$student->profile->tp_number : '' }}</option>
                                @empty
                                    <option disabled>All students are already in this cohort</option>
                                @endforelse
                            </select>
                            <span class="mt-1 block text-xs text-slate-500">Hold Ctrl or Cmd to select several students.</span>
                            @if ($activeSemesterStudentIds->isNotEmpty())
                                <span class="mt-1 block text-xs font-medium text-amber-700">{{ $activeSemesterStudentIds->unique()->count() }} active-semester {{ Str::plural('student', $activeSemesterStudentIds->unique()->count()) }} hidden from this list.</span>
                            @endif
                            <x-input-error class="mt-2" :messages="$errors->get('student_ids')" />
                        </label>
                        <label>
                            <span class="mb-2 block text-sm font-bold text-slate-700">Academic Mentor</span>
                            <select name="mentor_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Assign later</option>
                                @foreach ($mentors as $mentor)
                                    <option value="{{ $mentor->id }}">{{ $mentor->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button @disabled($availableStudents->isEmpty()) class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">Add to cohort</button>
                    </form>
                </section>
            @endif

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h3 class="font-bold text-slate-900">Cohort assignments</h3>
                    <p class="mt-1 text-sm text-slate-500">Academic Mentor assignments are synchronized when the semester is active.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[900px] w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Student</th>
                                <th class="px-6 py-4">Academic Mentor</th>
                                <th class="px-6 py-4">Industrial Supervisor</th>
                                <th class="px-6 py-4">Assignment</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($assignments as $assignment)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-900">{{ $assignment->student?->name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $assignment->student?->profile?->tp_number ?? $assignment->student?->email }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if (in_array($semester->status, ['draft', 'active'], true))
                                            <form method="POST" action="{{ route('admin.semesters.students.update', [$semester, $assignment->student]) }}" class="flex items-center gap-2">
                                                @csrf @method('PATCH')
                                                <select name="mentor_id" class="rounded-lg border-slate-300 py-1.5 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="">Unassigned</option>
                                                    @foreach ($mentors as $mentor)
                                                        <option value="{{ $mentor->id }}" @selected($assignment->mentor_id === $mentor->id)>{{ $mentor->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Save</button>
                                            </form>
                                        @else
                                            <span class="text-slate-700">{{ $assignment->mentor?->name ?? 'Unassigned' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $assignment->student?->supervisor?->name ?? 'Provisioned after placement' }}</td>
                                    <td class="px-6 py-4"><span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold capitalize text-blue-700">{{ $assignment->status }}</span></td>
                                    <td class="px-6 py-4 text-right">
                                        @if (in_array($semester->status, ['draft', 'active'], true))
                                            <form method="POST" action="{{ route('admin.semesters.students.destroy', [$semester, $assignment->student]) }}" class="inline" onsubmit="return confirm('Remove this student from the semester?');">
                                                @csrf @method('DELETE')
                                                <button class="text-xs font-bold text-rose-600 hover:text-rose-800">Remove</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">Read only</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">No students have been added to this cohort.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
