@php
    $profile = $user->profile;
    $fullName = $profile->full_name ?? $user->name;
    $email = $profile->personal_email ?? $user->email;
    $contact = $profile->contact_number ?? '';
    $summary = $profile->bio ?? '';
    $projectsSummary = $profile->projects_summary ?? '';
    $projectLines = array_filter(preg_split('/\r\n|\r|\n/', $projectsSummary));
@endphp

<div class="text-slate-900" style="font-family: Arial, sans-serif;">
    <div class="border border-slate-200 rounded-lg p-4 mb-6">
        <h1 class="text-3xl font-bold">{{ $fullName }}</h1>
        <p class="text-sm text-slate-600">{{ $email }} @if($contact) | {{ $contact }} @endif</p>
        <p class="text-sm text-slate-600">{{ $profile->tp_number ?? '' }} @if($profile?->course_name) | {{ $profile->course_name }} @endif</p>
    </div>

    <section class="mb-6">
        <h2 class="text-lg font-semibold uppercase tracking-wider text-slate-700">Profile</h2>
        <div class="mt-2 border border-slate-200 rounded-md p-3">
            @if ($summary)
                <p class="text-sm text-slate-700">{{ $summary }}</p>
            @else
                <p class="text-sm text-slate-500">Add your profile summary in Student Profile.</p>
            @endif
        </div>
    </section>

    <section class="mb-6">
        <h2 class="text-lg font-semibold uppercase tracking-wider text-slate-700">Education</h2>
        <div class="mt-3 space-y-3 border border-slate-200 rounded-md p-3">
            @forelse ($user->education as $edu)
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ $edu->degree }} in {{ $edu->field_of_study }}</p>
                    <p class="text-sm text-slate-600">{{ $edu->institution_name }}</p>
                    <p class="text-xs text-slate-500">
                        {{ $edu->start_date->format('M Y') }} - {{ $edu->end_date ? $edu->end_date->format('M Y') : 'Present' }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-slate-500">No education records added yet.</p>
            @endforelse
        </div>
    </section>

    <section class="mb-6">
        <h2 class="text-lg font-semibold uppercase tracking-wider text-slate-700">Projects</h2>
        <div class="mt-3 border border-slate-200 rounded-md p-3">
            @if (count($projectLines))
                <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                    @foreach ($projectLines as $line)
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-slate-500">Add your projects in Student Profile.</p>
            @endif
        </div>
    </section>

    <section>
        <h2 class="text-lg font-semibold uppercase tracking-wider text-slate-700">Skills</h2>
        <div class="mt-3 flex flex-wrap gap-2 border border-slate-200 rounded-md p-3">
            @forelse ($user->skills as $skill)
                <span class="text-xs font-semibold text-slate-700 border border-slate-300 rounded-full px-3 py-1">
                    {{ $skill->name }} ({{ $skill->proficiency }})
                </span>
            @empty
                <p class="text-sm text-slate-500">No skills listed yet.</p>
            @endforelse
        </div>
    </section>
</div>
