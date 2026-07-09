@php
    $profileUrl = route('student.profile.edit').'#document-profile';
@endphp

<section class="rounded-2xl border {{ $readiness['complete'] ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="font-bold {{ $readiness['complete'] ? 'text-emerald-900' : 'text-amber-900' }}">Student Profile readiness</h3>
                <span class="rounded-full bg-white/80 px-2.5 py-1 text-xs font-bold {{ $readiness['complete'] ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $readiness['completed'] }}/{{ $readiness['total'] }} required
                </span>
            </div>
            <p class="mt-1 text-sm {{ $readiness['complete'] ? 'text-emerald-700' : 'text-amber-700' }}">
                {{ $readiness['complete'] ? 'Your profile contains the required information for generation.' : 'Complete the missing items before downloading a generated document.' }}
            </p>
        </div>
        <a href="{{ $profileUrl }}" class="shrink-0 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">
            Update Student Profile
        </a>
    </div>

    <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/80">
        <div class="h-full rounded-full {{ $readiness['complete'] ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $readiness['percentage'] }}%"></div>
    </div>

    <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($readiness['required'] as $check)
            <div class="flex items-center gap-2 rounded-lg bg-white/70 px-3 py-2 text-sm">
                <span class="flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold {{ $check['complete'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                    {{ $check['complete'] ? '✓' : '!' }}
                </span>
                <span class="{{ $check['complete'] ? 'text-slate-700' : 'font-semibold text-rose-700' }}">{{ $check['label'] }}</span>
            </div>
        @endforeach
    </div>

    @if (! empty($readiness['recommended']))
        <p class="mt-4 text-xs text-slate-600">
            Recommended:
            {{ collect($readiness['recommended'])->where('complete', false)->pluck('label')->implode(', ') ?: 'all optional items completed' }}.
        </p>
    @endif
</section>
