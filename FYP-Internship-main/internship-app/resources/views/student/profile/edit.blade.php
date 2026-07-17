<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Student workspace</p>
        <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
            {{ __('Student Profile & Competencies') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 shadow-sm" role="alert">
                    <span class="font-medium">Success!</span> The profile was updated successfully.
                </div>
            @endif

            <section id="document-profile" class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="font-bold text-indigo-900">Document Builder readiness</h3>
                        <p class="mt-1 text-sm text-indigo-700">Resume: {{ $resumeReadiness['completed'] }}/{{ $resumeReadiness['total'] }} required · Cover letter: {{ $coverLetterReadiness['completed'] }}/{{ $coverLetterReadiness['total'] }} required</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('student.resume.builder') }}" class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-indigo-700 shadow-sm ring-1 ring-indigo-200">Resume</a>
                        <a href="{{ route('student.cover-letter.create') }}" class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-indigo-700 shadow-sm ring-1 ring-indigo-200">Cover Letter</a>
                    </div>
                </div>
                @if (! $resumeReadiness['complete'] || ! $coverLetterReadiness['complete'])
                    <p class="mt-4 text-sm text-rose-700">
                        Missing required items:
                        {{ collect(array_merge($resumeReadiness['missing'], $coverLetterReadiness['missing']))->unique()->implode(', ') }}.
                    </p>
                @endif
            </section>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:shadow-md">
                <div class="p-8">
                    <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Student Profile
                    </h3>

                    <form method="POST" action="{{ route('student.profile.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="full_name" :value="__('Full Name')" />
                                <input id="full_name" name="full_name" type="text" class="mt-1 block w-full rounded-md border-gray-300" value="{{ old('full_name', $user->profile->full_name ?? $user->name) }}" />
                            </div>
                            <div>
                                <x-input-label for="tp_number" :value="__('TP Number')" />
                                <input id="tp_number" name="tp_number" type="text" class="mt-1 block w-full rounded-md border-gray-300" value="{{ old('tp_number', $user->profile->tp_number ?? '') }}" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="languages_summary" :value="__('Languages')" />
                                    <textarea id="languages_summary" name="languages_summary" rows="3" class="mt-1 block w-full" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" placeholder="English - Fluent\nMalay - Native">{{ old('languages_summary', $user->profile->languages_summary ?? '') }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('languages_summary')" />
                                </div>
                                <div>
                                    <x-input-label for="references_summary" :value="__('References')" />
                                    <textarea id="references_summary" name="references_summary" rows="3" class="mt-1 block w-full" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" placeholder="Available upon request or list referee names and contacts">{{ old('references_summary', $user->profile->references_summary ?? '') }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('references_summary')" />
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="course_name" :value="__('Course Name')" />
                                <input id="course_name" name="course_name" type="text" class="mt-1 block w-full rounded-md border-gray-300" value="{{ old('course_name', $user->profile->course_name ?? '') }}" />
                            </div>
                            <div>
                                <x-input-label for="specialization" :value="__('Specialization')" />
                                <input id="specialization" name="specialization" type="text" class="mt-1 block w-full rounded-md border-gray-300" value="{{ old('specialization', $user->profile->specialization ?? '') }}" />
                            </div>
                            <div>
                                <x-input-label for="intake_code" :value="__('Intake Code')" />
                                <input id="intake_code" name="intake_code" type="text" class="mt-1 block w-full rounded-md border-gray-300" value="{{ old('intake_code', $user->profile->intake_code ?? '') }}" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="personal_email" :value="__('Personal Email')" />
                                <input id="personal_email" name="personal_email" type="email" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('personal_email', $user->profile->personal_email ?? '') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('personal_email')" />
                            </div>
                            <div>
                                <x-input-label for="contact_number" :value="__('Contact Number')" />
                                <input id="contact_number" name="contact_number" type="text" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('contact_number', $user->profile->contact_number ?? '') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('contact_number')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="bio" :value="__('Profile Summary')" />
                            <textarea id="bio" name="bio" rows="4" class="mt-1 block w-full" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">{{ old('bio', $user->profile->bio ?? '') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
                        </div>

                        <div>
                            <x-input-label for="projects_summary" :value="__('Projects (one per line)')" />
                            <textarea id="projects_summary" name="projects_summary" rows="4" class="mt-1 block w-full" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" placeholder="Inventory Dashboard - Built with Laravel and PostgreSQL
Task Tracker - REST API + React UI">{{ old('projects_summary', $user->profile->projects_summary ?? '') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('projects_summary')" />
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <x-input-label for="linkedin_url" :value="__('LinkedIn URL')" />
                                <x-text-input id="linkedin_url" name="linkedin_url" type="url" class="mt-1" :value="old('linkedin_url', $user->profile->linkedin_url ?? '')" placeholder="https://linkedin.com/in/..." />
                                <x-input-error class="mt-2" :messages="$errors->get('linkedin_url')" />
                            </div>
                            <div>
                                <x-input-label for="github_url" :value="__('GitHub URL')" />
                                <x-text-input id="github_url" name="github_url" type="url" class="mt-1" :value="old('github_url', $user->profile->github_url ?? '')" placeholder="https://github.com/..." />
                                <x-input-error class="mt-2" :messages="$errors->get('github_url')" />
                            </div>
                            <div>
                                <x-input-label for="portfolio_url" :value="__('Portfolio URL')" />
                                <x-text-input id="portfolio_url" name="portfolio_url" type="url" class="mt-1" :value="old('portfolio_url', $user->profile->portfolio_url ?? '')" placeholder="https://yourportfolio.com" />
                                <x-input-error class="mt-2" :messages="$errors->get('portfolio_url')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="internship_status" :value="__('Internship Status')" />
                            <select id="internship_status" name="internship_status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @php($currentStatus = old('internship_status', $user->profile->internship_status ?? 'looking'))
                                <option value="looking" @selected($currentStatus === 'looking')>Looking for Placement</option>
                                <option value="interviewing" @selected($currentStatus === 'interviewing')>Interviewing</option>
                                <option value="secured" @selected($currentStatus === 'secured')>Offer Secured</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('internship_status')" />
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">{{ __('Save Information') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200 transition duration-300 hover:shadow-md flex flex-col">
                    <div class="p-8 flex-1">
                        <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                            Academic History
                        </h3>
                        
                        <div class="space-y-4 mb-8">
                            @forelse ($user->education as $edu)
                                <div class="p-4 border border-slate-100 rounded-lg bg-slate-50 flex justify-between items-start group">
                                    <div>
                                        <h4 class="font-semibold text-slate-800">{{ $edu->degree }} in {{ $edu->field_of_study }}</h4>
                                        <p class="text-sm text-slate-600">{{ $edu->institution_name }}</p>
                                        <p class="text-xs text-slate-500 mt-1">
                                            {{ $edu->start_date->format('M Y') }} - {{ $edu->end_date ? $edu->end_date->format('M Y') : 'Present' }}
                                        </p>
                                    </div>
                                    <form method="POST" action="{{ route('student.education.destroy', $edu) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 opacity-0 group-hover:opacity-100 transition-opacity hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 italic">No academic history added yet.</p>
                            @endforelse
                        </div>

                        <div class="border-t border-slate-100 pt-6">
                            <h4 class="text-sm font-semibold text-slate-700 mb-4 uppercase tracking-wider">Add New Entry</h4>
                            <form method="POST" action="{{ route('student.education.store') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <x-input-label for="institution_name" :value="__('Institution Name')" />
                                    <x-text-input id="institution_name" name="institution_name" type="text" class="mt-1 block w-full shadow-sm" required />
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="degree" :value="__('Qualification / Level')" />
                                        <x-text-input id="degree" name="degree" type="text" class="mt-1 block w-full shadow-sm" placeholder="e.g. Diploma, Foundation, SPM" required />
                                    </div>
                                    <div>
                                        <x-input-label for="field_of_study" :value="__('Field of Study / Stream')" />
                                        <x-text-input id="field_of_study" name="field_of_study" type="text" class="mt-1 block w-full shadow-sm" placeholder="e.g. Computing, IT, Science Stream" required />
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="start_date" :value="__('Start Date')" />
                                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full shadow-sm" required />
                                    </div>
                                    <div>
                                        <x-input-label for="end_date" :value="__('End Date')" />
                                        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full shadow-sm" />
                                    </div>
                                </div>
                                <div class="pt-2 flex justify-end">
                                    <x-secondary-button type="submit" class="border-emerald-200 text-emerald-700 hover:bg-emerald-50 focus:ring-emerald-500">
                                        {{ __('Add Education') }}
                                    </x-secondary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200 transition duration-300 hover:shadow-md flex flex-col">
                    <div class="p-8 flex-1">
                        <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Skills Matrix
                        </h3>

                        <div class="mb-8 flex flex-wrap gap-2">
                            @forelse ($user->skills as $skill)
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium border
                                    @if($skill->proficiency == 'Expert') bg-indigo-50 text-indigo-700 border-indigo-200
                                    @elseif($skill->proficiency == 'Advanced') bg-emerald-50 text-emerald-700 border-emerald-200
                                    @elseif($skill->proficiency == 'Intermediate') bg-amber-50 text-amber-700 border-amber-200
                                    @else bg-slate-50 text-slate-700 border-slate-200 @endif">
                                    <span>{{ $skill->name }}</span>
                                    <span class="opacity-50 text-xs px-1 border-l border-current">{{ $skill->proficiency }}</span>
                                    <form method="POST" action="{{ route('student.skill.destroy', $skill) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hover:text-red-500 focus:outline-none ml-1">
                                            &times;
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 italic">No skills added yet.</p>
                            @endforelse
                        </div>

                        <div class="border-t border-slate-100 pt-6 mt-auto">
                            <h4 class="text-sm font-semibold text-slate-700 mb-4 uppercase tracking-wider">Add New Skill</h4>
                            <form method="POST" action="{{ route('student.skill.store') }}" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div class="col-span-2">
                                        <x-input-label for="name" :value="__('Skill Name')" />
                                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full shadow-sm" placeholder="e.g. PHP, Laravel, Figma" required />
                                    </div>
                                    <div class="col-span-1">
                                        <x-input-label for="proficiency" :value="__('Level')" />
                                        <select id="proficiency" name="proficiency" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                                            <option value="Beginner">Beginner</option>
                                            <option value="Intermediate" selected>Intermediate</option>
                                            <option value="Advanced">Advanced</option>
                                            <option value="Expert">Expert</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="pt-2 flex justify-end">
                                    <x-secondary-button type="submit" class="border-amber-200 text-amber-700 hover:bg-amber-50 focus:ring-amber-500">
                                        {{ __('Add Skill') }}
                                    </x-secondary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
