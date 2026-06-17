<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-slate-800 leading-tight">
            {{ __('Supervisor Profile') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'profile-updated')
                <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 shadow-sm border border-green-200">
                    Profile updated successfully.
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-8 space-y-6">
                    <form method="POST" action="{{ route('supervisor.profile.update') }}" class="space-y-6" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="supervisor_job_title" :value="__('Job Title')" />
                                <input id="supervisor_job_title" name="supervisor_job_title" type="text" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('supervisor_job_title', $user->profile->supervisor_job_title ?? '') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('supervisor_job_title')" />
                            </div>
                            <div>
                                <x-input-label for="supervisor_contact_number" :value="__('Contact Number')" />
                                <input id="supervisor_contact_number" name="supervisor_contact_number" type="text" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('supervisor_contact_number', $user->profile->supervisor_contact_number ?? '') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('supervisor_contact_number')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="personal_email" :value="__('Personal Email (Login)')" />
                                <input id="personal_email" name="personal_email" type="email" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('personal_email', $user->email) }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('personal_email')" />
                            </div>
                            <div>
                                <x-input-label for="company_email" :value="__('Company Email')" />
                                <input id="company_email" name="company_email" type="email" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('company_email', $user->profile->company_email ?? '') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('company_email')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="company_name" :value="__('Company Name')" />
                                <input id="company_name" name="company_name" type="text" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('company_name', $user->profile->company_name ?? '') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                            </div>
                            <div>
                                <x-input-label for="industry" :value="__('Industry')" />
                                <input id="industry" name="industry" type="text" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('industry', $user->profile->industry ?? '') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('industry')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="company_address" :value="__('Company Address')" />
                            <input id="company_address" name="company_address" type="text" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('company_address', $user->profile->company_address ?? '') }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('company_address')" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="signature_image" :value="__('E-Signature (PNG/JPG)')" />
                                <input id="signature_image" name="signature_image" type="file" accept="image/png,image/jpeg" class="mt-1 block w-full" />
                                <x-input-error class="mt-2" :messages="$errors->get('signature_image')" />
                            </div>
                            <div>
                                <x-input-label for="company_stamp" :value="__('Company Stamp (PNG/JPG)')" />
                                <input id="company_stamp" name="company_stamp" type="file" accept="image/png,image/jpeg" class="mt-1 block w-full" />
                                <x-input-error class="mt-2" :messages="$errors->get('company_stamp')" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">Save Profile</x-primary-button>
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
