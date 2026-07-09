<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Performance</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $title }}</h2>
            <p class="text-sm text-slate-500">{{ $description }}</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:px-6 lg:px-8">
            @if($showStudent && isset($cycles))
                <form method="GET" action="{{ route('mentor.evaluations.index') }}" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <label>
                        <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Semester / intake</span>
                        <select name="semester" class="rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All assigned intakes</option>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}" @selected((int) request('semester') === $cycle->id)>{{ $cycle->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Apply filter</button>
                </form>
            @endif

            @forelse($evaluations as $evaluation)
                <div class="overflow-hidden rounded-2xl border bg-white shadow-sm {{ $evaluation->hasConcern() ? 'border-red-300' : 'border-slate-200' }}">
                    <div class="flex flex-col gap-3 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            @if($showStudent)
                                <h3 class="text-lg font-bold text-slate-900">{{ $evaluation->student->name }}</h3>
                            @endif
                            <p class="font-semibold capitalize text-slate-700">{{ $evaluation->type }} evaluation</p>
                            <p class="text-sm text-slate-500">Industrial Supervisor: {{ $evaluation->supervisor->name }} - {{ $evaluation->submitted_at?->format('M d, Y') }}</p>
                            @if($evaluation->form)
                                <p class="mt-1 text-xs text-indigo-600">Form: {{ $evaluation->form->title }}{{ $evaluation->form->version ? ' · '.$evaluation->form->version : '' }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($evaluation->hasConcern())
                                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700">Intervention recommended</span>
                            @endif
                            <span class="rounded-full bg-indigo-100 px-3 py-1 text-sm font-bold text-indigo-800">{{ $evaluation->overall_grade }}/10</span>
                        </div>
                    </div>

                    <div class="grid gap-5 p-6 lg:grid-cols-2">
                        @foreach($evaluation->criteria() as $key => $label)
                            @php($rating = $evaluation->ratings[$key] ?? [])
                            <div class="rounded-xl border border-slate-200 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-semibold text-slate-800">{{ $label }}</p>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold
                                        @if(($rating['rating'] ?? 'U') === 'A') bg-emerald-100 text-emerald-800
                                        @elseif(($rating['rating'] ?? 'U') === 'D') bg-red-100 text-red-800
                                        @else bg-slate-100 text-slate-700 @endif">
                                        {{ $rating['rating'] ?? 'U' }}
                                    </span>
                                </div>
                                @if(! empty($rating['comment']))
                                    <p class="mt-2 text-sm text-slate-600">{{ $rating['comment'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50 p-6">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Overall comments</p>
                        <p class="mt-2 text-sm text-slate-700">{{ $evaluation->overall_comments }}</p>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                    No submitted evaluations are available yet.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
