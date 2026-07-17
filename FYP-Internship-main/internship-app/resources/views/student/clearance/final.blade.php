<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Student workspace</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Final Internship Clearance</h2>
            <p class="text-sm text-slate-500">Submit your final report and signed company form after completing the internship.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if(!$approvedPlacement)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    Final clearance is not available until your placement has been approved.
                    <a href="{{ route('student.clearance.create') }}" class="ml-1 font-bold underline">View placement submission</a>
                </div>
            @endif
            @include('student.clearance.partials.final-clearance')
        </div>
    </div>
</x-app-layout>
