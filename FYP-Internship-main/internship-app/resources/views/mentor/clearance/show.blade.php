<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Placement review</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {{ __('Review Placement Submission') }}
            </h2>
            <p class="text-sm text-slate-500">Verify documents and approve the placement.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 shadow-sm border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 shadow-sm border border-red-200">
                    Please provide a rejection reason before submitting.
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400">Student</p>
                            <p class="text-lg font-semibold text-slate-900">{{ $clearance->student?->name ?? 'Student' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400">Supervisor</p>
                            <p class="text-lg font-semibold text-slate-900">{{ $clearance->supervisor_name }}</p>
                            <p class="text-sm text-slate-500">Company: {{ $clearance->supervisor_email }}</p>
                            <p class="text-sm text-slate-500">Personal: {{ $clearance->supervisor_personal_email }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-400">Company</p>
                        <p class="text-lg font-semibold text-slate-900">{{ $clearance->company_name }}</p>
                        <p class="text-sm text-slate-500">{{ $clearance->office_address }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 rounded-lg border border-indigo-100 bg-indigo-50 p-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-500">Official start date</p>
                            <p class="mt-1 font-bold text-indigo-950">{{ $clearance->start_date?->format('M d, Y') ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-500">Official end date</p>
                            <p class="mt-1 font-bold text-indigo-950">{{ $clearance->end_date?->format('M d, Y') ?? 'Not provided' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('mentor.clearances.download', [$clearance, 'job_offer']) }}" class="block p-4 border border-slate-200 rounded-lg bg-slate-50 hover:bg-white">Job Offer Letter</a>
                        <a href="{{ route('mentor.clearances.download', [$clearance, 'indemnity_letter']) }}" class="block p-4 border border-slate-200 rounded-lg bg-slate-50 hover:bg-white">Indemnity Letter</a>
                        <a href="{{ route('mentor.clearances.download', [$clearance, 'placement_agreement']) }}" class="block p-4 border border-slate-200 rounded-lg bg-slate-50 hover:bg-white">Placement Agreement</a>
                    </div>

                    <div class="flex flex-col gap-4 pt-4 border-t border-slate-100">
                        <p class="text-sm text-slate-500">Status: <span class="font-semibold capitalize">{{ $clearance->status }}</span></p>
                        <div class="flex flex-col gap-4">
                            <form method="POST" action="{{ route('mentor.clearances.approve', $clearance) }}">
                                @csrf
                                @method('PATCH')
                                <x-primary-button class="bg-emerald-600 hover:bg-emerald-700" :disabled="$clearance->status !== 'pending'">
                                    Approve Placement
                                </x-primary-button>
                            </form>

                            <form method="POST" action="{{ route('mentor.clearances.reject', $clearance) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <x-input-label for="rejection_reason" :value="__('Rejection Reason')" />
                                    <textarea id="rejection_reason" name="rejection_reason" rows="3" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm" placeholder="Explain what must be corrected" @if($clearance->status !== 'pending') disabled @endif>{{ old('rejection_reason') }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('rejection_reason')" />
                                </div>
                                <x-secondary-button type="submit" class="border-red-200 text-red-700 hover:bg-red-50" :disabled="$clearance->status !== 'pending'">
                                    Reject Placement
                                </x-secondary-button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
