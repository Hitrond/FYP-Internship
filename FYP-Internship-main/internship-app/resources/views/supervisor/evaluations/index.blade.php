<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Company workspace</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Industrial Supervisor Evaluations</h2>
            <p class="text-sm text-slate-500">Complete the Week-8 midterm and final workplace assessments.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif

            <form method="GET" action="{{ route('supervisor.evaluations.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_180px_180px_auto_auto]">
                <label>
                    <span class="sr-only">Search students</span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search student / email / TP..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </label>
                <label>
                    <span class="sr-only">Evaluation type</span>
                    <select name="type" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Any type</option>
                        <option value="midterm" @selected(request('type') === 'midterm')>Midterm</option>
                        <option value="final" @selected(request('type') === 'final')>Final</option>
                    </select>
                </label>
                <label>
                    <span class="sr-only">Evaluation status</span>
                    <select name="status" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Any status</option>
                        <option value="not_started" @selected(request('status') === 'not_started')>Not started</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="submitted" @selected(request('status') === 'submitted')>Submitted</option>
                    </select>
                </label>
                <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Filter</button>
                <a href="{{ route('supervisor.evaluations.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
            </form>

            @forelse($students as $student)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">{{ $student->name }}</h3>
                            <p class="text-sm text-slate-500">{{ $student->email }}</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach(['midterm' => 'Midterm (Week 8)', 'final' => 'Final'] as $type => $label)
                                @php($evaluation = $student->performanceEvaluations->firstWhere('type', $type))
                                <a href="{{ route('supervisor.evaluations.edit', [$student, $type]) }}" class="min-w-52 rounded-lg border p-4 transition
                                    @if($evaluation?->status === 'submitted') border-emerald-200 bg-emerald-50 hover:bg-emerald-100
                                    @elseif($evaluation) border-amber-200 bg-amber-50 hover:bg-amber-100
                                    @else border-slate-200 bg-slate-50 hover:bg-slate-100 @endif">
                                    <p class="font-semibold text-slate-800">{{ $label }}</p>
                                    <p class="mt-1 text-xs font-bold uppercase tracking-wider
                                        @if($evaluation?->status === 'submitted') text-emerald-700
                                        @elseif($evaluation) text-amber-700
                                        @else text-slate-500 @endif">
                                        {{ $evaluation?->status ?? 'Not started' }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                    No students are assigned to you.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
