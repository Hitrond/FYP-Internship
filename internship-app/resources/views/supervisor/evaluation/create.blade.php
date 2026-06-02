<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="font-semibold text-2xl text-slate-800 leading-tight">
                {{ __('Final Evaluation Form') }}
            </h2>
            <p class="text-sm text-slate-500">Complete the digital checklist for the student at the end of placement.</p>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="student_name" :value="__('Student Name')" />
                            <x-text-input id="student_name" name="student_name" type="text" class="mt-1 block w-full" placeholder="Student full name" />
                        </div>
                        <div>
                            <x-input-label for="company_name" :value="__('Company')" />
                            <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" placeholder="Company name" />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-slate-800">Competency Checklist</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg">
                                <input type="checkbox" class="rounded border-slate-300 text-indigo-600" />
                                <span class="text-sm text-slate-700">Professional communication</span>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg">
                                <input type="checkbox" class="rounded border-slate-300 text-indigo-600" />
                                <span class="text-sm text-slate-700">Technical task completion</span>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg">
                                <input type="checkbox" class="rounded border-slate-300 text-indigo-600" />
                                <span class="text-sm text-slate-700">Team collaboration</span>
                            </label>
                            <label class="flex items-center gap-3 p-4 border border-slate-200 rounded-lg">
                                <input type="checkbox" class="rounded border-slate-300 text-indigo-600" />
                                <span class="text-sm text-slate-700">Problem-solving ability</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="overall_feedback" :value="__('Overall Feedback')" />
                        <textarea id="overall_feedback" name="overall_feedback" rows="5" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Add feedback for the student..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <x-secondary-button type="button">Save Draft</x-secondary-button>
                        <x-primary-button class="bg-indigo-600 hover:bg-indigo-700" type="button">Submit Evaluation</x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
