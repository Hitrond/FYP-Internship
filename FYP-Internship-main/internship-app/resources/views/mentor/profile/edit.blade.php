<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">University workspace</p>
        <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
            {{ __('Academic Mentor Profile') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'profile-updated')
                <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 shadow-sm border border-green-200">
                    Profile updated successfully.
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-8 space-y-6">
                    <form method="POST" action="{{ route('mentor.profile.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="mentor_staff_id" :value="__('Staff ID')" />
                                <input id="mentor_staff_id" name="mentor_staff_id" type="text" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('mentor_staff_id', $user->profile->mentor_staff_id ?? '') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('mentor_staff_id')" />
                            </div>
                            <div>
                                <x-input-label for="mentor_department" :value="__('Department/Faculty')" />
                                <input id="mentor_department" name="mentor_department" type="text" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('mentor_department', $user->profile->mentor_department ?? '') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('mentor_department')" />
                            </div>
                        </div>

                        <div class="space-y-3">
                            <p class="text-sm font-semibold text-slate-700 uppercase tracking-wider">Notification Preferences</p>
                            <label class="flex items-center gap-3 text-sm text-slate-700">
                                <input type="checkbox" name="notify_email_missed_logbook" value="1" class="rounded border-gray-300 text-indigo-600" @if(old('notify_email_missed_logbook', $user->profile->notify_email_missed_logbook ?? true)) checked @endif />
                                Send me an email if a student misses a logbook for 7 days.
                            </label>
                            <label class="flex items-center gap-3 text-sm text-slate-700">
                                <input type="checkbox" name="notify_dashboard_only" value="1" class="rounded border-gray-300 text-indigo-600" @if(old('notify_dashboard_only', $user->profile->notify_dashboard_only ?? false)) checked @endif />
                                Only show alerts on my dashboard.
                            </label>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">Save Preferences</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-8">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
