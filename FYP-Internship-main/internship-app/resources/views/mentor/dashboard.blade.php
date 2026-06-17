<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-2xl text-slate-800 leading-tight">
                {{ __('Mentor Dashboard') }}
            </h2>
            <p class="text-sm text-slate-500">Monitor placement submissions and act quickly.</p>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-wider text-slate-400">Pending Clearances</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $pendingCount }}</p>
                        <p class="text-sm text-slate-500 mt-1">Students awaiting placement approval.</p>
                    </div>
                    <a href="{{ route('mentor.clearances.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Review Submissions
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-slate-800">Latest Pending Submissions</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-sm uppercase tracking-wider text-slate-500">
                                    <th class="py-3 px-4 font-semibold">Student ID</th>
                                    <th class="py-3 px-4 font-semibold">Company</th>
                                    <th class="py-3 px-4 font-semibold">Supervisor</th>
                                    <th class="py-3 px-4 font-semibold">Submitted</th>
                                    <th class="py-3 px-4 font-semibold text-right">Review</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($pendingClearances as $clearance)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 text-slate-700">{{ $clearance->student_id }}</td>
                                        <td class="py-3 px-4 text-slate-900">{{ $clearance->company_name }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ $clearance->supervisor_name }}</td>
                                        <td class="py-3 px-4 text-slate-500 text-sm">{{ $clearance->created_at->format('M d, Y') }}</td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('mentor.clearances.show', $clearance) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm">Review</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-slate-500">No pending submissions.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
