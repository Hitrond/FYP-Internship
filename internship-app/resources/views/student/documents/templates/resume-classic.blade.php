@php
    $profile = $user->profile;
    $username = $profile->user->user_name ?? $user->user_name ?? $user->name ?? 'Your Name';
    $title = $profile->course_name ?? 'Software Engineering Student';
    $email = $profile->personal_email ?? $user->email ?? '';
    $phone = $profile->contact_number ?? $user->phone ?? '';
    $address = $profile->address ?? ($profile->city ? ($profile->city . ($profile->postcode ? ', '.$profile->postcode : '')) : '');
    $bio = $profile->bio ?? '';
    $education = $user->education ?? collect();
    $projectsSummary = $profile->projects_summary ?? '';
    $projectLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $projectsSummary))));
    $skills = $user->skills ?? collect();
    $languages = $profile->languages_summary ?? '';
    $nationality = $profile->nationality ?? 'Malaysian';
    $availability = $profile->availability ?? 'Immediate';
@endphp

<div class="bg-white shadow-2xl w-full max-w-[21cm] min-h-[29.7cm] p-10 mx-auto text-gray-900 font-serif">
    <!-- Header -->
    <header class="text-center pb-5 border-b border-gray-300 mb-5">
        <h1 class="text-2xl font-bold tracking-wide">{{ $username }}, {{ $title }}</h1>
        <div class="text-[11px] text-gray-700 mt-2 space-y-0.5">
            <p>{{ $address }}</p>
            <p>{{ $phone }} • {{ $email }} @if($profile->linkedin_url) • <a href="{{ $profile->linkedin_url }}">{{ Str::after($profile->linkedin_url, '://') }}</a>@endif</p>
        </div>
    </header>

    <!-- Profile Section -->
    <section class="flex border-b border-gray-300 pb-5 mb-5">
        <div class="w-1/4 pr-4">
            <h3 class="font-bold uppercase text-[11px] tracking-widest text-gray-800">Profile</h3>
        </div>
        <div class="w-3/4 text-[11px] text-justify leading-relaxed text-gray-800">
            {{ $bio ?: 'Add your profile summary in Student Profile.' }}
        </div>
    </section>

    <!-- Education Section -->
    <section class="border-b border-gray-300 pb-5 mb-5">
        @foreach($education as $edu)
            <div class="flex mb-4">
                <div class="w-1/4 pr-4">
                    <h3 class="font-bold uppercase text-[11px] tracking-widest text-gray-800">Education</h3>
                    <div class="text-[10px] text-gray-500 mt-2">{{ $edu->start_date?->format('M Y') ?? '' }} – {{ $edu->end_date?->format('M Y') ?? 'Present' }}</div>
                </div>
                <div class="w-3/4 text-[11px]">
                    <div class="flex justify-between items-baseline mb-0.5">
                        <span class="font-bold text-[13px] text-gray-900">{{ $edu->institution_name }}</span>
                        <span class="text-gray-500 text-[10px]">{{ $edu->location ?? '' }}</span>
                    </div>
                    <div class="italic text-gray-700 mb-2">{{ $edu->degree }}{{ $edu->field_of_study ? ' (' . $edu->field_of_study . ')' : '' }}</div>
                    @if($edu->notes)
                        <ul class="list-disc list-outside ml-3 text-[11px] text-gray-800 space-y-1">
                            <li>{{ $edu->notes }}</li>
                        </ul>
                    @endif
                </div>
            </div>
        @endforeach
    </section>

    <!-- Experience / Projects Section -->
    <section class="border-b border-gray-300 pb-5 mb-5">
        @if(count($projectLines))
            @foreach($projectLines as $idx => $line)
                <div class="flex mb-5">
                    <div class="w-1/4 pr-4">
                        @if($idx == 0)
                            <h3 class="font-bold uppercase text-[11px] tracking-widest text-gray-800">Experience</h3>
                        @endif
                        <div class="text-[10px] text-gray-500 mt-2">{{ $profile->project_dates[$idx] ?? '' }}</div>
                    </div>
                    <div class="w-3/4 text-[11px]">
                        <div class="flex justify-between items-baseline mb-0.5">
                            <span class="font-bold text-[13px] text-gray-900">{{ $line }}</span>
                            <span class="text-gray-500 text-[10px]">{{ $profile->project_type[$idx] ?? 'Academic Project' }}</span>
                        </div>
                        <div class="italic text-gray-700 mb-2">{{ $profile->project_role[$idx] ?? '' }}</div>
                        <p class="text-justify mb-2 leading-relaxed text-gray-800">{{ $profile->project_descriptions[$idx] ?? '' }}</p>
                        @if($profile->project_bullets && isset($profile->project_bullets[$idx]))
                            <ul class="list-disc list-outside ml-3 text-[11px] text-gray-800 space-y-1">
                                @foreach($profile->project_bullets[$idx] as $bullet)
                                    <li>{{ $bullet }}</li>
                                @endforeach
                            </ul>
                        @else
                            <ul class="list-disc list-outside ml-3 text-[11px] text-gray-800 space-y-1">
                                <li>Architected and developed a centralized web application to digitize administrative workflows.</li>
                                <li>Built using Docker and modern LAMP stack patterns.</li>
                            </ul>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="flex mb-5">
                <div class="w-1/4 pr-4">
                    <h3 class="font-bold uppercase text-[11px] tracking-widest text-gray-800">Experience</h3>
                </div>
                <div class="w-3/4 text-[11px] text-gray-800">Add your projects in Student Profile.</div>
            </div>
        @endif
    </section>

    <!-- Skills Section -->
    <section class="border-b border-gray-300 pb-5 mb-5">
        <div class="flex">
            <div class="w-1/4 pr-4">
                <h3 class="font-bold uppercase text-[11px] tracking-widest text-gray-800">Skills</h3>
                <div class="text-[9px] text-gray-500 italic mt-1">In decreasing order</div>
            </div>
            <div class="w-3/4 text-[11px] text-gray-800">
                <div class="grid grid-cols-2 gap-x-12 gap-y-2">
                    @forelse($skills as $skill)
                        <div class="flex justify-between border-b border-gray-100 pb-1">
                            <span class="font-semibold">{{ $skill->name }}</span>
                            <span class="text-gray-500">{{ $skill->proficiency ?? '' }}</span>
                        </div>
                    @empty
                        <div class="text-gray-500">No skills listed yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Details Grid -->
    <section class="text-[10px] text-gray-700 mt-6">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <span class="text-gray-500 mr-2">Nationality:</span> {{ $nationality }}
            </div>
            <div>
                <span class="text-gray-500 mr-2">Languages:</span> {{ $languages ?: 'English, Malay' }}
            </div>
            <div>
                <span class="text-gray-500 mr-2">Availability:</span> {{ $availability }}
            </div>
        </div>
    </section>

</div>
