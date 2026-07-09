<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Academic setup</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Semesters & Cohorts</h2>
                <p class="mt-1 text-sm text-slate-500">Keep every internship intake separated, traceable, and reportable.</p>
            </div>
            <a href="{{ route('admin.semesters.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
                New semester
            </a>
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

            @if ($cycles->isEmpty())
                <section class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-20 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                    </span>
                    <h3 class="mt-5 text-xl font-bold text-slate-900">Create your first internship semester</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">Define the placement window, add students, assign Academic Mentors, then activate the cohort.</p>
                    <a href="{{ route('admin.semesters.create') }}" class="mt-6 inline-flex rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-700">Create semester</a>
                </section>
            @else
                <section class="grid gap-5 lg:grid-cols-2">
                    @foreach ($cycles as $cycle)
                        @php
                            $statusStyle = match ($cycle->status) {
                                'active' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'closed' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'archived' => 'bg-slate-100 text-slate-600 border-slate-200',
                                default => 'bg-blue-100 text-blue-700 border-blue-200',
                            };
                            $placementRate = $cycle->assignments_count > 0
                                ? round(($cycle->approved_placements_count / $cycle->assignments_count) * 100)
                                : 0;
                        @endphp
                        <article class="overflow-hidden rounded-2xl border {{ $cycle->status === 'active' ? 'border-emerald-300 ring-4 ring-emerald-50' : 'border-slate-200' }} bg-white shadow-sm">
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-xl font-bold text-slate-900">{{ $cycle->name }}</h3>
                                            <span class="rounded-full border px-2.5 py-1 text-xs font-bold capitalize {{ $statusStyle }}">{{ $cycle->status }}</span>
                                        </div>
                                        <p class="mt-2 text-sm text-slate-500">{{ $cycle->intake_code }} · Academic Year {{ $cycle->academic_year }}</p>
                                    </div>
                                    <a href="{{ route('admin.semesters.show', $cycle) }}" class="shrink-0 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-700">Manage</a>
                                </div>

                                <div class="mt-6 grid grid-cols-3 divide-x divide-slate-200 rounded-xl bg-slate-50 py-4 text-center">
                                    <div>
                                        <p class="text-xl font-bold text-slate-900">{{ $cycle->assignments_count }}</p>
                                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Students</p>
                                    </div>
                                    <div>
                                        <p class="text-xl font-bold text-indigo-700">{{ $cycle->approved_placements_count }}</p>
                                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Placed</p>
                                    </div>
                                    <div>
                                        <p class="text-xl font-bold text-emerald-700">{{ $cycle->approved_logbooks_count }}</p>
                                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Logs approved</p>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <div class="flex items-center justify-between text-xs font-semibold">
                                        <span class="text-slate-500">Placement progress</span>
                                        <span class="text-slate-800">{{ $placementRate }}%</span>
                                    </div>
                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-indigo-500" style="width: {{ min(100, $placementRate) }}%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/70 px-6 py-4 text-xs text-slate-500">
                                <span>Start window: {{ $cycle->placement_window_start->format('d M Y') }} – {{ $cycle->placement_window_end->format('d M Y') }}</span>
                                <span>{{ $cycle->duration_weeks }} weeks · Friday 11:59 PM</span>
                            </div>
                        </article>
                    @endforeach
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
