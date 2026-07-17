<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Student workspace</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Company Tracker</h2>
            <p class="text-sm text-slate-500">Track applications, contacts, follow-ups, and offer letters in one place.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <p class="font-semibold">Please correct the highlighted information.</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ([
                    ['label' => 'Total', 'value' => $totalApplications],
                    ['label' => 'Applied', 'value' => $statusCounts->get('Applied', 0)],
                    ['label' => 'Interviewing', 'value' => $statusCounts->get('Interviewing', 0)],
                    ['label' => 'Accepted', 'value' => $statusCounts->get('Accepted', 0)],
                ] as $metric)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $metric['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $metric['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <details class="group rounded-2xl border border-slate-200 bg-white shadow-sm" @if($errors->any()) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-6 marker:hidden">
                    <span><span class="block text-lg font-semibold text-slate-900">Add an application</span><span class="block text-sm text-slate-500">Only the company name and status are required.</span></span>
                    <span class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white"><span class="group-open:hidden">Add application</span><span class="hidden group-open:inline">Close form</span><svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg></span>
                </summary>

                <form method="POST" action="{{ route('student.companies.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-5 border-t border-slate-100 p-6 md:grid-cols-6">
                    @csrf

                    <div class="md:col-span-2">
                        <x-input-label for="company_name" value="Company" />
                        <x-text-input id="company_name" name="company_name" value="{{ old('company_name') }}" class="mt-1 block w-full" required />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="position_title" value="Internship role" />
                        <x-text-input id="position_title" name="position_title" value="{{ old('position_title') }}" class="mt-1 block w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', 'Interested') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="location" value="Location" />
                        <x-text-input id="location" name="location" value="{{ old('location') }}" class="mt-1 block w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="applied_on" value="Applied on" />
                        <x-text-input id="applied_on" type="date" name="applied_on" value="{{ old('applied_on') }}" class="mt-1 block w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="next_followup_on" value="Next follow-up" />
                        <x-text-input id="next_followup_on" type="date" name="next_followup_on" value="{{ old('next_followup_on') }}" class="mt-1 block w-full" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="contact_name" value="Contact person" />
                        <x-text-input id="contact_name" name="contact_name" value="{{ old('contact_name') }}" class="mt-1 block w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="contact_email" value="Contact email" />
                        <x-text-input id="contact_email" type="email" name="contact_email" value="{{ old('contact_email') }}" class="mt-1 block w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="contact_phone" value="Contact phone" />
                        <x-text-input id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}" class="mt-1 block w-full" />
                    </div>

                    <div class="md:col-span-3">
                        <x-input-label for="job_url" value="Job posting URL" />
                        <x-text-input id="job_url" type="url" name="job_url" value="{{ old('job_url') }}" class="mt-1 block w-full" />
                    </div>
                    <div class="md:col-span-3">
                        <x-input-label for="offer_letter" value="Offer letter (PDF, optional)" />
                        <input id="offer_letter" type="file" name="offer_letter" accept="application/pdf" class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm" />
                    </div>

                    <div class="md:col-span-6">
                        <x-input-label for="notes" value="Notes" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    </div>

                    <div class="md:col-span-6 flex justify-end">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white hover:bg-indigo-700">
                            Add application
                        </button>
                    </div>
                </form>
            </details>

            <div class="space-y-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Your applications</h3>
                        <p class="text-sm text-slate-500">Showing {{ $applications->firstItem() ?? 0 }}–{{ $applications->lastItem() ?? 0 }} of {{ $applications->total() }} matching applications</p>
                    </div>
                    <form method="GET" action="{{ route('student.companies.index') }}" class="grid gap-2 sm:grid-cols-[minmax(220px,1fr)_180px_auto_auto]">
                        <label><span class="sr-only">Search applications</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Search company, role, location..." class="w-full rounded-lg border-slate-300 text-sm"></label>
                        <label><span class="sr-only">Application status</span><select name="status" class="w-full rounded-lg border-slate-300 text-sm"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></label>
                        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Filter</button>
                        <a href="{{ route('student.companies.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-center text-sm font-bold text-slate-700">Reset</a>
                    </form>
                </div>

                @forelse ($applications as $application)
                    <details class="group rounded-xl border border-slate-200 bg-white shadow-sm" @if((int) old('application_id') === $application->id) open @endif>
                        <summary class="flex cursor-pointer list-none flex-col gap-4 p-5 marker:hidden sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="truncate text-lg font-bold text-slate-900">{{ $application->company_name }}</h4>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold @if($application->status === 'Accepted') bg-emerald-100 text-emerald-800 @elseif($application->status === 'Rejected') bg-red-100 text-red-800 @elseif($application->status === 'Interviewing') bg-blue-100 text-blue-800 @elseif($application->status === 'Offered') bg-violet-100 text-violet-800 @else bg-amber-100 text-amber-800 @endif">{{ $application->status }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">{{ $application->position_title ?: 'Role not specified' }}{{ $application->location ? ' · '.$application->location : '' }}</p>
                                @if($application->next_followup_on)<p class="mt-1 text-xs font-semibold text-amber-700">Follow up {{ $application->next_followup_on->format('M d, Y') }}</p>@endif
                            </div>
                            <span class="inline-flex shrink-0 items-center gap-2 text-sm font-bold text-indigo-700">View details <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg></span>
                        </summary>
                        <form method="POST" action="{{ route('student.companies.update', $application) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="application_id" value="{{ $application->id }}">
                            <div class="flex flex-wrap items-center justify-end gap-3 border-y border-slate-100 bg-slate-50 px-6 py-3">
                                    @if ($application->offer_letter_path)
                                        <a href="{{ route('student.companies.offer-letter', $application) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                            Download offer
                                        </a>
                                    @endif
                                    @if ($application->job_url)
                                        <a href="{{ $application->job_url }}" target="_blank" rel="noopener" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                                            Job posting ↗
                                        </a>
                                    @endif
                                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-slate-700">
                                        Save changes
                                    </button>
                            </div>

                            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-3">
                                <div>
                                    <x-input-label for="company_name_{{ $application->id }}" value="Company" />
                                    <x-text-input id="company_name_{{ $application->id }}" name="company_name" value="{{ $application->company_name }}" class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <x-input-label for="position_title_{{ $application->id }}" value="Role" />
                                    <x-text-input id="position_title_{{ $application->id }}" name="position_title" value="{{ $application->position_title }}" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="status_{{ $application->id }}" value="Status" />
                                    <select id="status_{{ $application->id }}" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}" @selected($application->status === $status)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="location_{{ $application->id }}" value="Location" />
                                    <x-text-input id="location_{{ $application->id }}" name="location" value="{{ $application->location }}" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="applied_on_{{ $application->id }}" value="Applied on" />
                                    <x-text-input id="applied_on_{{ $application->id }}" type="date" name="applied_on" value="{{ $application->applied_on?->format('Y-m-d') }}" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="last_contacted_on_{{ $application->id }}" value="Last contacted" />
                                    <x-text-input id="last_contacted_on_{{ $application->id }}" type="date" name="last_contacted_on" value="{{ $application->last_contacted_on?->format('Y-m-d') }}" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="next_followup_on_{{ $application->id }}" value="Next follow-up" />
                                    <x-text-input id="next_followup_on_{{ $application->id }}" type="date" name="next_followup_on" value="{{ $application->next_followup_on?->format('Y-m-d') }}" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="job_url_{{ $application->id }}" value="Job posting URL" />
                                    <x-text-input id="job_url_{{ $application->id }}" type="url" name="job_url" value="{{ $application->job_url }}" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="offer_letter_{{ $application->id }}" value="Replace offer letter (PDF)" />
                                    <input id="offer_letter_{{ $application->id }}" type="file" name="offer_letter" accept="application/pdf" class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm" />
                                </div>

                                <div>
                                    <x-input-label for="contact_name_{{ $application->id }}" value="Contact person" />
                                    <x-text-input id="contact_name_{{ $application->id }}" name="contact_name" value="{{ $application->contact_name }}" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="contact_email_{{ $application->id }}" value="Contact email" />
                                    <x-text-input id="contact_email_{{ $application->id }}" type="email" name="contact_email" value="{{ $application->contact_email }}" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="contact_phone_{{ $application->id }}" value="Contact phone" />
                                    <x-text-input id="contact_phone_{{ $application->id }}" name="contact_phone" value="{{ $application->contact_phone }}" class="mt-1 block w-full" />
                                </div>

                                <div class="md:col-span-3">
                                    <x-input-label for="notes_{{ $application->id }}" value="Notes" />
                                    <textarea id="notes_{{ $application->id }}" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $application->notes }}</textarea>
                                </div>
                            </div>
                        </form>

                        <div class="flex justify-end border-t border-slate-100 px-6 py-3">
                            <form method="POST" action="{{ route('student.companies.destroy', $application) }}" onsubmit="return confirm('Delete this application?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold uppercase tracking-wider text-red-600 hover:text-red-800">Delete application</button>
                            </form>
                        </div>
                    </details>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-12 text-center">
                        <p class="font-semibold text-slate-700">No applications yet</p>
                        <p class="mt-1 text-sm text-slate-500">Add your first company using the form above.</p>
                    </div>
                @endforelse
                @if($applications->hasPages())
                    <div class="pt-2">{{ $applications->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
