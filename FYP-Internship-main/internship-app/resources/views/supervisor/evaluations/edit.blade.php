@php
    $locked = $evaluation?->status === 'submitted';
    $ratingLabels = [
        'A' => 'Excellent',
        'B' => 'Good',
        'C' => 'Satisfactory',
        'D' => 'Poor',
        'U' => 'Untested',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Performance review</p>
            <h2 class="mt-1 text-2xl font-bold capitalize tracking-tight text-slate-900">{{ $type }} Performance Evaluation</h2>
            <p class="text-sm text-slate-500">{{ $student->name }} - completed by the Industrial Supervisor.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>
            @endif
            @if($locked)
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    This evaluation was submitted {{ $evaluation->submitted_at?->format('M d, Y H:i') }} and is now locked.
                </div>
            @endif
            @if($formTemplate)
                <div class="mb-5 rounded-lg border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-800">
                    Using active form: <strong>{{ $formTemplate->title }}</strong>{{ $formTemplate->version ? ' ('.$formTemplate->version.')' : '' }}.
                    @if($formTemplate->instructions)
                        <p class="mt-1">{{ $formTemplate->instructions }}</p>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('supervisor.evaluations.store', [$student, $type]) }}" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                @csrf
                @method('PUT')

                <div class="border-b border-slate-200 bg-slate-50 p-6">
                    <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
                        @foreach($ratingLabels as $letter => $label)
                            <span class="rounded-full border border-slate-200 bg-white px-3 py-1">{{ $letter }} = {{ $label }}</span>
                        @endforeach
                    </div>
                    <p class="mt-3 text-xs text-slate-500">This assessment feeds the Academic Mentor's final result calculation together with the completed weekly logbooks.</p>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($criteria as $key => $label)
                        @php
                            $selectedRating = old("ratings.$key.rating", data_get($evaluation?->ratings, "$key.rating", 'U'));
                            $comment = old("ratings.$key.comment", data_get($evaluation?->ratings, "$key.comment"));
                        @endphp
                        <div class="grid gap-4 p-6 md:grid-cols-[240px_190px_1fr] md:items-start">
                            <label class="font-semibold text-slate-800">{{ $label }}</label>
                            <select name="ratings[{{ $key }}][rating]" @disabled($locked) class="w-full rounded-lg border-slate-300 text-sm">
                                @foreach($ratingLabels as $letter => $ratingLabel)
                                    <option value="{{ $letter }}" @selected($selectedRating === $letter)>{{ $letter }} - {{ $ratingLabel }}</option>
                                @endforeach
                            </select>
                            <div>
                                <input type="text" name="ratings[{{ $key }}][comment]" value="{{ $comment }}" @disabled($locked) class="w-full rounded-lg border-slate-300 text-sm" placeholder="Comment (required for Poor)">
                                <x-input-error class="mt-1" :messages="$errors->get('ratings.'.$key.'.comment')" />
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-5 border-t border-slate-200 bg-slate-50 p-6">
                    <div>
                        <x-input-label for="overall_grade" value="Overall feedback score (1-10)" />
                        <input id="overall_grade" name="overall_grade" type="number" min="1" max="10" value="{{ old('overall_grade', $evaluation?->overall_grade ?? 5) }}" @disabled($locked) class="mt-1 w-40 rounded-lg border-slate-300">
                        <x-input-error class="mt-1" :messages="$errors->get('overall_grade')" />
                    </div>
                    <div>
                        <x-input-label for="overall_comments" value="Overall comments" />
                        <textarea id="overall_comments" name="overall_comments" rows="5" @disabled($locked) class="mt-1 w-full rounded-lg border-slate-300" placeholder="Summarise strengths, concerns, and recommended development.">{{ old('overall_comments', $evaluation?->overall_comments) }}</textarea>
                        <x-input-error class="mt-1" :messages="$errors->get('overall_comments')" />
                    </div>

                    <div class="flex flex-wrap justify-end gap-3">
                        <a href="{{ route('supervisor.evaluations.index') }}" class="rounded-lg bg-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-300">Back</a>
                        @unless($locked)
                            <button type="submit" name="action" value="draft" class="rounded-lg border border-indigo-200 bg-white px-5 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Save draft</button>
                            <button type="submit" name="action" value="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Submit & lock</button>
                        @endunless
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
