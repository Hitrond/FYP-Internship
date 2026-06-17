<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-2xl text-slate-800 leading-tight">
                {{ __('Placement Clearance Submission') }}
            </h2>
            <p class="text-sm text-slate-500">Submit company details and required documents for mentor approval.</p>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 shadow-sm border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="p-4 text-sm text-amber-800 rounded-lg bg-amber-50 shadow-sm border border-amber-200">
                    {{ session('warning') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 shadow-sm border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-8 space-y-8">
                    <form method="POST" action="{{ route('student.clearance.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        @if ($latestClearance && $latestClearance->status === 'pending')
                            <div class="p-4 border border-amber-200 rounded-lg bg-amber-50 text-amber-800">
                                You already have a pending submission. Please wait for mentor review before resubmitting.
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="company_name" :value="__('Company Name')" />
                                <input id="company_name" name="company_name" type="text" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('company_name', $prefillClearance->company_name ?? '') }}" required @if($latestClearance && $latestClearance->status === 'pending') disabled @endif />
                                <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                            </div>
                            <div>
                                <x-input-label for="office_address" :value="__('Office Address')" />
                                <input id="office_address" name="office_address" type="text" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('office_address', $prefillClearance->office_address ?? '') }}" required @if($latestClearance && $latestClearance->status === 'pending') disabled @endif />
                                <x-input-error class="mt-2" :messages="$errors->get('office_address')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="supervisor_name" :value="__('Supervisor Name')" />
                                <input id="supervisor_name" name="supervisor_name" type="text" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('supervisor_name', $prefillClearance->supervisor_name ?? '') }}" required @if($latestClearance && $latestClearance->status === 'pending') disabled @endif />
                                <x-input-error class="mt-2" :messages="$errors->get('supervisor_name')" />
                            </div>
                            <div>
                                <x-input-label for="supervisor_email" :value="__('Supervisor Company Email')" />
                                <input id="supervisor_email" name="supervisor_email" type="email" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('supervisor_email', $prefillClearance->supervisor_email ?? '') }}" required @if($latestClearance && $latestClearance->status === 'pending') disabled @endif />
                                <x-input-error class="mt-2" :messages="$errors->get('supervisor_email')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="supervisor_personal_email" :value="__('Supervisor Personal Email (Login)')" />
                            <input id="supervisor_personal_email" name="supervisor_personal_email" type="email" class="mt-1 block w-full" style="width: 100%; min-height: 2.5rem; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;" value="{{ old('supervisor_personal_email', $prefillClearance->supervisor_personal_email ?? '') }}" required @if($latestClearance && $latestClearance->status === 'pending') disabled @endif />
                            <x-input-error class="mt-2" :messages="$errors->get('supervisor_personal_email')" />
                        </div>

                        <div class="space-y-4">
                            <div>
                                <x-input-label for="job_offer" :value="__('Job Offer Letter (PDF)')" />
                                <input id="job_offer" name="job_offer" type="file" accept="application/pdf" class="mt-1 block w-full" required @if($latestClearance && $latestClearance->status === 'pending') disabled @endif />
                                <x-input-error class="mt-2" :messages="$errors->get('job_offer')" />
                            </div>
                            <div>
                                <x-input-label for="indemnity_letter" :value="__('Indemnity Letter (PDF)')" />
                                <input id="indemnity_letter" name="indemnity_letter" type="file" accept="application/pdf" class="mt-1 block w-full" required @if($latestClearance && $latestClearance->status === 'pending') disabled @endif />
                                <x-input-error class="mt-2" :messages="$errors->get('indemnity_letter')" />
                            </div>
                            <div>
                                <x-input-label for="placement_agreement" :value="__('Placement Agreement (PDF)')" />
                                <input id="placement_agreement" name="placement_agreement" type="file" accept="application/pdf" class="mt-1 block w-full" required @if($latestClearance && $latestClearance->status === 'pending') disabled @endif />
                                <x-input-error class="mt-2" :messages="$errors->get('placement_agreement')" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700" :disabled="$latestClearance && $latestClearance->status === 'pending'">Submit for Approval</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($latestClearance)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                    <div class="p-6 text-sm text-slate-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-slate-800">Latest Submission</p>
                                <p class="text-slate-500">Status: <span class="font-semibold capitalize">{{ $latestClearance->status }}</span></p>
                            </div>
                            <div class="text-slate-400">{{ $latestClearance->created_at->format('M d, Y') }}</div>
                        </div>
                        @if ($latestClearance->status === 'rejected' && $latestClearance->rejection_reason)
                            <div class="mt-4 p-4 border border-red-200 rounded-lg bg-red-50 text-red-700">
                                <p class="font-semibold">Rejection Reason</p>
                                <p class="mt-1">{{ $latestClearance->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
