@php
    $profile = $user->profile;
    $fullName = $profile->full_name ?? $user->name;
    $email = $profile->personal_email ?? $user->email;
    $contact = $profile->contact_number ?? '';
    $location = $profile->city ?? $profile->address ?? 'Kuala Lumpur, Malaysia';
    $summary = $profile->bio ?? '';
    $projectsSummary = $profile->projects_summary ?? '';
    $projectLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $projectsSummary))));
    $languageLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $profile->languages_summary ?? ''))));
    $referenceLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $profile->references_summary ?? ''))));
    $skillNames = $user->skills->pluck('name')->all();
    $skillColumns = array_chunk($skillNames, 3);
@endphp

<div class="bg-white shadow-2xl w-full max-w-[21cm] min-h-[29.7cm] p-12 mx-auto font-sans text-gray-800">
    <!-- Header -->
    <header class="flex justify-between items-start mb-8">
        <div>
            <h1 class="text-4xl font-normal text-blue-600 tracking-tight">{{ $fullName }}</h1>
            <h2 class="text-md font-medium text-blue-500 mt-1">{{ $profile->course_name ?? 'Software Engineering Student' }}</h2>
        </div>
        <div class="text-right text-[13px] text-blue-500 space-y-1">
            <p>{{ $email }} • {{ $contact }}</p>
            <p>{{ $location }}</p>
        </div>
    </header>

    <p class="text-[13px] mb-8 leading-relaxed">
        {{ $summary ?: 'Experienced and forward-thinking Software Engineering student. Add your summary in Student Profile.' }}
    </p>

    <!-- Professional Experience -->
    <section class="mb-8">
        <h3 class="text-xl font-normal text-blue-600 mb-4 border-b pb-1 border-blue-100">Project Experience</h3>
        @if(count($projectLines))
            @foreach($projectLines as $line)
                <div class="mb-6">
                    <div class="flex justify-between text-[14px]">
                        <h4 class="text-blue-500 font-medium">{{ $line }}</h4>
                        <span class="text-gray-600">{{ $profile->project_date ?? '' }}</span>
                    </div>
                    <p class="text-[13px] text-blue-400 italic mb-2">{{ $profile->position_title ?? '' }}</p>
                    <ul class="text-[13px] list-disc list-inside ml-2 space-y-1">
                        <li>Led backend architecture with Docker and Laravel.</li>
                        <li>Integrated dynamic PDF generation tooling for automated exports.</li>
                    </ul>
                </div>
            @endforeach
        @else
            <p class="text-[13px] text-blue-400">Add your projects in Student Profile.</p>
        @endif
    </section>

    <!-- Education -->
    <section class="mb-8">
        <h3 class="text-xl font-normal text-blue-600 mb-4 border-b pb-1 border-blue-100">Education</h3>
        @forelse ($user->education as $edu)
            <div class="mb-2">
                <div class="flex justify-between text-[14px]">
                    <h4 class="text-blue-500 font-medium">{{ $edu->degree }}</h4>
                    <span class="text-gray-600">{{ $edu->start_date->format('M Y') }} – {{ $edu->end_date ? $edu->end_date->format('M Y') : 'Present' }}</span>
                </div>
                <div class="text-[13px] text-gray-800">{{ $edu->institution_name }}</div>
            </div>
        @empty
            <p class="text-slate-500">No education records added yet.</p>
        @endforelse
    </section>

    <!-- Areas of Expertise -->
    <section>
        <h3 class="text-xl font-normal text-blue-600 mb-4 border-b pb-1 border-blue-100">Areas of Expertise</h3>
        <ul class="grid grid-cols-3 gap-y-2 text-[13px] text-gray-800 list-disc list-inside ml-2">
            @if(count($skillNames))
                @foreach($skillNames as $skill)
                    <li>{{ $skill }}</li>
                @endforeach
            @else
                <li class="text-slate-500">No skills listed yet.</li>
            @endif
        </ul>
    </section>
</div>
