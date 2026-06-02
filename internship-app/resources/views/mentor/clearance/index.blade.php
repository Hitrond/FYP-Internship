<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-2xl text-slate-800 leading-tight">
                {{ __('Placement Clearance Reviews') }}
            </h2>
            <p class="text-sm text-slate-500">Review student submissions and approve placements.</p>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
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
                                        <td colspan="7" class="py-8 text-center text-slate-500">No placement submissions yet.</td>
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
