<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-2xl text-slate-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <p class="text-sm text-slate-500">Quick overview of your latest activity.</p>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (Auth::user()->isMentor())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                    <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-wider text-slate-400">Pending Clearances</p>
                            <p class="text-3xl font-bold text-slate-900">{{ $pendingClearances }}</p>
                            <p class="text-sm text-slate-500 mt-1">Students awaiting placement approval.</p>
                        </div>
                        <a href="{{ route('mentor.clearances.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Review Submissions
                        </a>
                    </div>
                </div>
            @endif

            @if (Auth::user()->isStudent())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                    <div class="p-6 space-y-3">
                        <div class="flex items-center justify-between">
                            <p class="text-sm uppercase tracking-wider text-slate-400">Placement Clearance</p>
                            <a href="{{ route('student.clearance.create') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">View Submission</a>
                        </div>
                        @if ($latestClearance)
                            <div class="flex items-center justify-between">
                                <p class="text-lg font-semibold text-slate-900">Latest Status</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize
                                    @if($latestClearance->status === 'approved') bg-emerald-100 text-emerald-800
                                    @elseif($latestClearance->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-amber-100 text-amber-800 @endif
                                ">
                                    {{ $latestClearance->status }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-500">Submitted {{ $latestClearance->created_at->format('M d, Y') }}</p>
                            @if ($latestClearance->status === 'rejected' && $latestClearance->rejection_reason)
                                <div class="mt-2 p-4 border border-red-200 rounded-lg bg-red-50 text-red-700">
                                    <p class="font-semibold">Rejection Reason</p>
                                    <p class="mt-1">{{ $latestClearance->rejection_reason }}</p>
                                </div>
                            @endif
                        @else
                            <p class="text-slate-600">No placement clearance submission yet.</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                    <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="text-sm uppercase tracking-wider text-slate-400">Resume Builder</p>
                            <p class="text-lg font-semibold text-slate-900">Generate your ATS-ready resume</p>
                            <p class="text-sm text-slate-500 mt-1">Choose a template and download a PDF.</p>
                        </div>
                        <a href="{{ route('student.resume.builder') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            Open Resume Builder
                        </a>
                    </div>
                </div>
            @endif

            @if (!Auth::user()->isMentor() && !Auth::user()->isStudent())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                    <div class="p-6 text-slate-900">
                        {{ __("You're logged in!") }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
