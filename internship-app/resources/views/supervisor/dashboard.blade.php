<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-slate-800 leading-tight">
            {{ __('Supervisor Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-6 text-slate-900 space-y-4">
                    <p class="text-slate-600">Welcome to the supervisor portal. This area will host logbook approvals and the final evaluation form.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="{{ route('supervisor.logbooks.index') }}" class="block p-5 border border-slate-200 rounded-lg bg-slate-50 hover:bg-white hover:shadow-sm transition">
                            <h3 class="font-semibold text-slate-800">Weekly Logbook Approvals</h3>
                            <p class="text-sm text-slate-600 mt-1">Review and approve or reject student weekly submissions.</p>
                        </a>
                        <a href="{{ route('supervisor.evaluation.create') }}" class="block p-5 border border-slate-200 rounded-lg bg-slate-50 hover:bg-white hover:shadow-sm transition">
                            <h3 class="font-semibold text-slate-800">Final Evaluation</h3>
                            <p class="text-sm text-slate-600 mt-1">Complete the digital assessment checklist at the end of placement.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
