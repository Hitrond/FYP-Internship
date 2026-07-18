<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">University workspace</p>
        <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Academic Mentor Profile</h2>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-8">
                    <h3 class="text-lg font-bold text-slate-900">Profile details</h3>
                    <p class="mt-1 text-sm text-slate-500">Profile details are managed by the System Administrator.</p>
                </div>
                <dl class="grid gap-6 p-8 md:grid-cols-2">
                    <div>
                        <dt class="text-sm font-semibold text-slate-500">Name</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-slate-500">Email</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-slate-500">Staff ID</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $user->profile?->mentor_staff_id ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-slate-500">Department/Faculty</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $user->profile?->mentor_department ?: 'Not provided' }}</dd>
                    </div>
                </dl>
                <div class="border-t border-emerald-100 bg-emerald-50 px-8 py-4 text-sm text-emerald-800">
                    Email and dashboard alerts are enabled for overdue logbooks and extension requests.
                </div>
            </section>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="p-8">@include('profile.partials.update-password-form')</div>
            </div>
        </div>
    </div>
</x-app-layout>
