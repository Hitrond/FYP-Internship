<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-2xl text-slate-800 leading-tight">
                {{ __('Weekly Logbook Approvals') }}
            </h2>
            <p class="text-sm text-slate-500">Review submitted logbooks and approve or reject each week.</p>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-sm uppercase tracking-wider text-slate-500">
                                    <th class="py-4 px-6 font-semibold">Student</th>
                                    <th class="py-4 px-6 font-semibold">Week</th>
                                    <th class="py-4 px-6 font-semibold">Summary</th>
                                    <th class="py-4 px-6 font-semibold">Submitted</th>
                                    <th class="py-4 px-6 font-semibold text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-4 px-6 font-medium text-slate-900">Sample Student</td>
                                    <td class="py-4 px-6 text-slate-700">Week 4</td>
                                    <td class="py-4 px-6 text-slate-600">Completed API integration tasks.</td>
                                    <td class="py-4 px-6 text-slate-500 text-sm">May 18, 2026</td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <button type="button" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-emerald-200 text-emerald-700 hover:bg-emerald-50">Approve</button>
                                            <button type="button" class="px-3 py-1.5 text-xs font-semibold rounded-md border border-red-200 text-red-700 hover:bg-red-50">Reject</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-500">Live submissions will appear here.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
