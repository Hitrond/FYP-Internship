<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">University workspace</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                {{ __('Placement Clearance Reviews') }}
            </h2>
            <p class="text-sm text-slate-500">Review student submissions and approve placements.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
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
                <div class="p-6">
                    <form method="GET" action="{{ route('mentor.clearances.index') }}" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[1fr_220px_auto_auto]">
                        <label>
                            <span class="sr-only">Search placements</span>
                            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search student, company, supervisor, email..." class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </label>
                        <label>
                            <span class="sr-only">Status</span>
                            <select name="status" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All statuses</option>
                                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                            </select>
                        </label>
                        <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Filter</button>
                        <a href="{{ route('mentor.clearances.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
                    </form>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-sm uppercase tracking-wider text-slate-500">
                                    <th class="py-4 px-6 font-semibold">Student</th>
                                    <th class="py-4 px-6 font-semibold">Company</th>
                                    <th class="py-4 px-6 font-semibold">Supervisor</th>
                                    <th class="py-4 px-6 font-semibold">Personal Email</th>
                                    <th class="py-4 px-6 font-semibold">Status</th>
                                    <th class="py-4 px-6 font-semibold">Submitted</th>
                                    <th class="py-4 px-6 font-semibold text-right">Review</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($clearances as $clearance)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-4 px-6 font-medium text-slate-900">{{ $clearance->student?->name ?? 'Student' }}</td>
                                        <td class="py-4 px-6 text-slate-700">{{ $clearance->company_name }}</td>
                                        <td class="py-4 px-6 text-slate-600">{{ $clearance->supervisor_name }}</td>
                                        <td class="py-4 px-6 text-slate-500 text-sm">{{ $clearance->supervisor_personal_email }}</td>
                                        <td class="py-4 px-6">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize
                                                @if($clearance->status === 'approved') bg-emerald-100 text-emerald-800
                                                @elseif($clearance->status === 'rejected') bg-red-100 text-red-800
                                                @else bg-amber-100 text-amber-800 @endif
                                            ">
                                                {{ $clearance->status }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-slate-500 text-sm">{{ $clearance->created_at->format('M d, Y') }}</td>
                                        <td class="py-4 px-6 text-right">
                                            <a href="{{ route('mentor.clearances.show', $clearance) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm">Review</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-12 text-center">
                                            <p class="font-bold text-slate-700">Placement approval: Not available</p>
                                            <p class="mt-1 text-sm text-slate-500">No student placement has been submitted for review yet.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($clearances->hasPages())
                        <div class="mt-6 border-t border-slate-200 pt-4">{{ $clearances->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
