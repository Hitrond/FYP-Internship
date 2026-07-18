<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Company workspace</p>
        <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Industrial Supervisor Profile</h2>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'approval-assets-updated')
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">Approval assets updated successfully.</div>
            @endif

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-8">
                    <h3 class="text-lg font-bold text-slate-900">Profile details</h3>
                    <p class="mt-1 text-sm text-slate-500">Personal and company details are managed by the System Administrator.</p>
                </div>
                <dl class="grid gap-6 p-8 md:grid-cols-2">
                    <div><dt class="text-sm font-semibold text-slate-500">Name</dt><dd class="mt-1 font-medium text-slate-900">{{ $user->name }}</dd></div>
                    <div><dt class="text-sm font-semibold text-slate-500">Login email</dt><dd class="mt-1 font-medium text-slate-900">{{ $user->email }}</dd></div>
                    <div><dt class="text-sm font-semibold text-slate-500">Company</dt><dd class="mt-1 font-medium text-slate-900">{{ $user->profile?->company_name ?: 'Not provided' }}</dd></div>
                    <div><dt class="text-sm font-semibold text-slate-500">Job title</dt><dd class="mt-1 font-medium text-slate-900">{{ $user->profile?->supervisor_job_title ?: 'Not provided' }}</dd></div>
                </dl>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-8">
                    <h3 class="text-lg font-bold text-slate-900">Logbook approval assets</h3>
                    <p class="mt-1 text-sm text-slate-500">An e-signature and company stamp are required to approve pending logbooks.</p>
                </div>
                <form method="POST" action="{{ route('supervisor.profile.update') }}" enctype="multipart/form-data" class="grid gap-6 p-8 md:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="signature_image" :value="__('E-Signature (PNG/JPG)')" />
                        @if($user->profile?->signature_path)<p class="mt-1 text-xs font-semibold text-emerald-600">E-signature uploaded.</p>@endif
                        <input id="signature_image" name="signature_image" type="file" accept="image/png,image/jpeg" class="mt-2 block w-full" />
                        <x-input-error class="mt-2" :messages="$errors->get('signature_image')" />
                    </div>
                    <div>
                        <x-input-label for="company_stamp" :value="__('Company Stamp (PNG/JPG)')" />
                        @if($user->profile?->stamp_path)<p class="mt-1 text-xs font-semibold text-emerald-600">Company stamp uploaded.</p>@endif
                        <input id="company_stamp" name="company_stamp" type="file" accept="image/png,image/jpeg" class="mt-2 block w-full" />
                        <x-input-error class="mt-2" :messages="$errors->get('company_stamp')" />
                    </div>
                    <div class="md:col-span-2 flex justify-end border-t border-slate-100 pt-4">
                        <x-primary-button>Save Approval Assets</x-primary-button>
                    </div>
                </form>
            </section>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="p-8">@include('profile.partials.update-password-form')</div>
            </div>
        </div>
    </div>
</x-app-layout>
