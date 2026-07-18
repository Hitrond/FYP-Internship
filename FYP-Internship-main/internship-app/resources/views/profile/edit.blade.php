<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Account</p>
        <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
            {{ __('Account Settings') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            @if(auth()->user()->isStudent())
                <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
                    <div class="max-w-xl">
                        <section>
                            <header>
                                <h2 class="text-lg font-bold text-slate-900">
                                    {{ __('Assigned Supervision') }}
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ __("View your assigned academic mentor and industrial supervisor.") }}
                                </p>
                            </header>

                            <div class="mt-6 space-y-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">
                                        {{ __('Academic Mentor') }}
                                    </label>
                                    @if($user->mentor)
                                        <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                            <p class="text-sm font-medium text-slate-900">{{ $user->mentor->name }}</p>
                                            <a href="mailto:{{ $user->mentor->email }}" class="text-sm text-indigo-600 transition hover:text-indigo-800">
                                                {{ $user->mentor->email }}
                                            </a>
                                        </div>
                                    @else
                                        <p class="mt-2 rounded-xl border border-dashed border-slate-300 p-3 text-sm italic text-slate-500">
                                            No academic mentor assigned yet.
                                        </p>
                                    @endif
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700">
                                        {{ __('Industrial Supervisor') }}
                                    </label>
                                    @if($user->supervisor)
                                        <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                            <p class="text-sm font-medium text-slate-900">{{ $user->supervisor->name }}</p>
                                            <a href="mailto:{{ $user->supervisor->email }}" class="text-sm text-indigo-600 transition hover:text-indigo-800">
                                                {{ $user->supervisor->email }}
                                            </a>
                                        </div>
                                    @else
                                        <p class="mt-2 rounded-xl border border-dashed border-slate-300 p-3 text-sm italic text-slate-500">
                                            No industrial supervisor assigned yet.
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
