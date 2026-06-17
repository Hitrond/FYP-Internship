<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Company Tracker') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 space-y-8">
                    <div>
                        <h3 class="text-lg font-semibold">Add company</h3>

                        <form method="POST" action="{{ route('student.company-tracker.store') }}" class="mt-4 grid grid-cols-1 md:grid-cols-6 gap-4">
                            @csrf

                            <div class="md:col-span-2">
                                <x-input-label for="company_name" value="Company" />
                                <x-text-input id="company_name" name="company_name" value="{{ old('company_name') }}" class="mt-1" required />
                                @error('company_name')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="position_title" value="Role" />
                                <x-text-input id="position_title" name="position_title" value="{{ old('position_title') }}" class="mt-1" />
                                @error('position_title')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="status" value="Status" />
                                <select id="status" name="status" class="mt-1 block w-full px-3 py-2 rounded-md shadow-sm border border-gray-300 bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:!border-gray-700 dark:!bg-gray-900 dark:!text-gray-100" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" @selected(old('status', 'Interested') === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="location" value="Location" />
                                <x-text-input id="location" name="location" value="{{ old('location') }}" class="mt-1" />
                                @error('location')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="applied_on" value="Applied on" />
                                <x-text-input id="applied_on" type="date" name="applied_on" value="{{ old('applied_on') }}" class="mt-1" />
                                @error('applied_on')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="job_url" value="Job URL" />
                                <x-text-input id="job_url" name="job_url" value="{{ old('job_url') }}" class="mt-1" />
                                @error('job_url')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="md:col-span-6">
                                <x-input-label for="notes" value="Notes" />
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full px-3 py-2 rounded-md shadow-sm border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:!border-gray-700 dark:!bg-gray-900 dark:!text-gray-100 dark:placeholder-gray-400">{{ old('notes') }}</textarea>
                                @error('notes')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="md:col-span-6">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white">
                                    Add
                                </button>
                            </div>
                        </form>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold">Your applications</h3>

                        @if ($applications->isEmpty())
                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">No entries yet.</p>
                        @else
                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-gray-600 dark:text-gray-300">
                                            <th class="py-2 pr-4">Company</th>
                                            <th class="py-2 pr-4">Role</th>
                                            <th class="py-2 pr-4">Status</th>
                                            <th class="py-2 pr-4">Applied</th>
                                            <th class="py-2 pr-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($applications as $app)
                                            <tr>
                                                <td class="py-3 pr-4 align-top">
                                                    <div class="space-y-2">
                                                        <x-text-input form="update-{{ $app->id }}" name="company_name" value="{{ $app->company_name }}" required />

                                                        <x-text-input form="update-{{ $app->id }}" name="job_url" value="{{ $app->job_url }}" placeholder="Job URL" />

                                                        <div class="text-xs text-gray-500 dark:text-gray-400 break-all">
                                                            @if ($app->job_url)
                                                                <a class="underline" href="{{ $app->job_url }}" target="_blank" rel="noopener">{{ $app->job_url }}</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 pr-4 align-top">
                                                    <div class="space-y-2">
                                                        <x-text-input form="update-{{ $app->id }}" name="position_title" value="{{ $app->position_title }}" />
                                                        <x-text-input form="update-{{ $app->id }}" name="location" value="{{ $app->location }}" placeholder="Location" />
                                                        <x-text-input form="update-{{ $app->id }}" name="notes" value="{{ $app->notes }}" placeholder="Notes" />
                                                    </div>
                                                </td>
                                                <td class="py-3 pr-4 align-top">
                                                    <select form="update-{{ $app->id }}" name="status" class="block w-full px-3 py-2 rounded-md shadow-sm border border-gray-300 bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:!border-gray-700 dark:!bg-gray-900 dark:!text-gray-100" required>
                                                        @foreach ($statuses as $status)
                                                            <option value="{{ $status }}" @selected($app->status === $status)>{{ $status }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="py-3 pr-4 align-top">
                                                    <x-text-input form="update-{{ $app->id }}" type="date" name="applied_on" value="{{ optional($app->applied_on)->format('Y-m-d') }}" />
                                                    <input form="update-{{ $app->id }}" type="hidden" name="last_contacted_on" value="{{ optional($app->last_contacted_on)->format('Y-m-d') }}" />
                                                    <input form="update-{{ $app->id }}" type="hidden" name="next_followup_on" value="{{ optional($app->next_followup_on)->format('Y-m-d') }}" />
                                                </td>
                                                <td class="py-3 pr-4 align-top">
                                                    <div class="flex items-center gap-3">
                                                        <form id="update-{{ $app->id }}" method="POST" action="{{ route('student.company-tracker.update', $app) }}">
                                                            @csrf
                                                            @method('PUT')
                                                        </form>

                                                        <button form="update-{{ $app->id }}" type="submit" class="px-3 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md text-xs font-semibold uppercase">Save</button>
                                                    </div>

                                                    <form method="POST" action="{{ route('student.company-tracker.destroy', $app) }}" class="mt-2" onsubmit="return confirm('Delete this entry?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 dark:text-red-400 text-xs font-semibold uppercase">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
