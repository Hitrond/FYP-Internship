<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Student workspace</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {{ __('Clearance Hub') }}
            </h2>
            <p class="text-sm text-slate-500">Manage placement setup and final internship sign-off.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
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

            @if ($activeCycle)
                <div class="flex flex-col gap-3 rounded-xl border border-indigo-200 bg-indigo-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-indigo-900">{{ $activeCycle->name }}</p>
                        <p class="mt-1 text-xs text-indigo-700">
                            Placement start window: {{ $activeCycle->placement_window_start->format('d M Y') }} – {{ $activeCycle->placement_window_end->format('d M Y') }} · Duration: {{ $activeCycle->duration_weeks }} weeks
                        </p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $cycleAssignment ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        {{ $cycleAssignment ? 'Enrolled' : 'Not enrolled' }}
                    </span>
                </div>
            @elseif ($cyclesConfigured)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-800">
                    Placement submissions are currently closed. The administrator must activate an internship semester before you can submit.
                </div>
            @endif

            @include('student.clearance.partials.final-clearance')

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="p-8 space-y-8">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">Placement Setup</h3>
                        <p class="text-sm text-slate-500">Company and appointment documents used before the internship begins.</p>
                    </div>
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
                                <x-input-label for="start_date" :value="__('Official Internship Start Date')" />
                                <input id="start_date" name="start_date" type="date" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2" value="{{ old('start_date', $prefillClearance?->start_date?->format('Y-m-d')) }}" required @if($latestClearance && $latestClearance->status === 'pending') disabled @endif />
                                <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('Official Internship End Date')" />
                                <input id="end_date" name="end_date" type="date" class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2" value="{{ old('end_date', $prefillClearance?->end_date?->format('Y-m-d')) }}" required @if($latestClearance && $latestClearance->status === 'pending') disabled @endif />
                                <p class="mt-1 text-xs text-slate-500">Use the end date required by the active semester duration. The start date can be any day inside the placement window.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
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
                            <div class="flex flex-col items-end gap-2">
                                <div class="text-slate-400">{{ $latestClearance->created_at->format('M d, Y') }}</div>
                                <a href="{{ route('placement-clearances.view', [$latestClearance, 'placement-agreement']) }}" target="_blank" rel="noopener" class="inline-flex rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-indigo-700">
                                    View placement form
                                </a>
                            </div>
                        </div>
                        @if ($latestClearance->status === 'rejected' && $latestClearance->rejection_reason)
                            <div class="mt-4 p-4 border border-red-200 rounded-lg bg-red-50 text-red-700">
                                <p class="font-semibold">Rejection Reason</p>
                                <p class="mt-1">{{ $latestClearance->rejection_reason }}</p>
                            </div>
                        @endif
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ route('placement-clearances.view', [$latestClearance, 'job-offer']) }}" target="_blank" rel="noopener" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">View job offer</a>
                            <a href="{{ route('placement-clearances.view', [$latestClearance, 'indemnity-letter']) }}" target="_blank" rel="noopener" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">View indemnity letter</a>
                        </div>
                        @if(!$latestClearance->start_date || !$latestClearance->end_date)
                            <form method="POST" action="{{ route('student.clearance.dates.update', $latestClearance) }}" class="mt-5 grid gap-4 rounded-lg border border-amber-200 bg-amber-50 p-4 md:grid-cols-[1fr_1fr_auto] md:items-end">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <x-input-label for="legacy_start_date" value="Official start date" />
                                    <input id="legacy_start_date" type="date" name="start_date" required class="mt-1 w-full rounded-md border-amber-300">
                                </div>
                                <div>
                                    <x-input-label for="legacy_end_date" value="Official end date" />
                                    <input id="legacy_end_date" type="date" name="end_date" required class="mt-1 w-full rounded-md border-amber-300">
                                </div>
                                <button class="rounded-lg bg-amber-600 px-4 py-2.5 text-xs font-bold uppercase text-white hover:bg-amber-700">Generate timeline</button>
                            </form>
                        @else
                            <p class="mt-4 text-sm text-slate-500">
                                Internship period: {{ $latestClearance->start_date->format('M d, Y') }} - {{ $latestClearance->end_date->format('M d, Y') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
