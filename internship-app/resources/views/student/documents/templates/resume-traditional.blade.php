@php
    $profile = $user->profile;
    $fullName = $profile->full_name ?? $user->name;
    $title = $profile->course_name ?? 'Software Engineering Student';
    $email = $profile->personal_email ?? $user->email;
    $phone = $profile->contact_number ?? '';
    $address = $profile->city ?? $profile->company_address ?? '';
    $summary = $profile->bio ?? '';
    $projectsSummary = $profile->projects_summary ?? '';
    $projectLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $projectsSummary))));
    $languageLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $profile->languages_summary ?? ''))));
    $referenceLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $profile->references_summary ?? ''))));
    $skillWidths = [
        'Beginner' => '25%',
        'Intermediate' => '50%',
        'Advanced' => '75%',
        'Expert' => '100%',
    ];
@endphp

<div class="bg-white shadow-2xl w-full max-w-[21cm] min-h-[29.7cm] mx-auto font-sans flex text-gray-900 border border-gray-200">
    <!-- Left Sidebar -->
    <aside class="w-[35%] bg-gray-50 p-8 border-r border-gray-300 flex flex-col">
        <div class="mb-8">
            <h3 class="text-sm font-bold tracking-widest uppercase mb-4 text-black">Info</h3>

            <h4 class="text-xs font-bold uppercase mt-4">Address</h4>
            <p class="text-xs text-gray-600 mt-1">{{ $address ?: '—' }}</p>

            <h4 class="text-xs font-bold uppercase mt-4">Phone</h4>
            <p class="text-xs text-gray-600 mt-1">{{ $phone ?: '—' }}</p>

            <h4 class="text-xs font-bold uppercase mt-4">Email</h4>
            <p class="text-xs text-gray-600 mt-1 break-all">{{ $email }}</p>
        </div>

        <div class="mb-8">
            <h3 class="text-sm font-bold tracking-widest uppercase mb-4 text-black border-b border-gray-300 pb-2">Skills</h3>
            <div class="mt-4">
                @forelse ($user->skills as $skill)
                    <div class="mt-4">
                        <p class="text-xs font-bold mb-1">{{ $skill->name }}</p>
                        <div class="w-full bg-gray-300 h-1"><div class="bg-black h-1" style="width: {{ $skill->proficiency == 'Expert' ? '90%' : ($skill->proficiency == 'Advanced' ? '75%' : ($skill->proficiency == 'Intermediate' ? '50%' : '25%')) }}"></div></div>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">No skills listed yet.</p>
                @endforelse
            </div>
        </div>
    </aside>

    <!-- Right Main Content -->
    <main class="w-[65%] p-8 bg-white">
        <header class="mb-8 border-b border-gray-300 pb-6">
            <h1 class="text-5xl font-black uppercase tracking-tighter leading-none mb-2 text-black">{{ $fullName }}</h1>
            <h2 class="text-md text-gray-500 tracking-widest uppercase mt-2">{{ $title }}</h2>
        </header>

        <section class="mb-8">
            <h3 class="text-sm font-bold tracking-widest uppercase mb-3 text-black">Profile</h3>
            <p class="text-[13px] text-gray-600 leading-relaxed text-justify">{{ $summary ?: 'Add your profile summary in Student Profile.' }}</p>
        </section>

        <section>
            <h3 class="text-sm font-bold tracking-widest uppercase mb-4 text-black">Project History</h3>
            @if(count($projectLines))
                @foreach($projectLines as $line)
                    <div class="mb-6">
                        <div class="flex justify-between items-baseline">
                            <h4 class="text-[14px] font-bold text-black">{{ $line }}</h4>
                            <span class="text-xs text-gray-500 font-bold">{{ $profile->location ?? '' }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">{{ $profile->project_date ?? '' }}</p>
                        <ul class="text-[13px] text-gray-600 list-disc list-outside ml-4 space-y-1">
                            <li>Implemented architectural best practices in Laravel.</li>
                            <li>Maintained PostgreSQL schemas with referential integrity.</li>
                        </ul>
                    </div>
                @endforeach
            @else
                <p class="text-[13px] text-gray-600">Add your projects in Student Profile.</p>
            @endif
        </section>
    </main>
</div>
