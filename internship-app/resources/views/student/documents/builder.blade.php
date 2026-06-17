<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                <h2 class="font-semibold text-2xl text-slate-800 leading-tight">
                    {{ __('Resume Builder') }}
                </h2>
                <p class="text-sm text-slate-500">Choose a template and preview your ATS-ready resume.</p>
            </div>
            <a href="{{ route('student.resume.download', ['template' => $selectedTemplate]) }}" style="display:flex; align-items:center; justify-content:center; width:100%; min-height:3.25rem; padding:0.875rem 1rem; border-radius:0.75rem; background:#059669; color:#ffffff; font-size:0.75rem; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; text-decoration:none; box-shadow:0 10px 15px -3px rgba(0,0,0,0.12);">
                Download PDF Resume
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php
                $templateLabels = [
                    'classic' => 'Traditional / Corporate (Caleb Smith)',
                    'traditional' => 'Modern Two-Column (Arthur Sherman)',
                    'prime-ats' => 'Minimalist with Color Accents (Taylor Greene)',
                ];
            @endphp
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">Template Gallery</h3>
                            <p class="text-sm text-slate-500">Pick a design and the preview will update automatically.</p>
                        </div>
                        <a href="{{ route('student.profile.edit') }}" class="text-sm text-indigo-600 hover:text-indigo-700">
                            Add account, summary, projects, education, skills, languages, and references
                        </a>
                    </div>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach ($templates as $template)
                            <a href="{{ route('student.resume.builder', ['template' => $template]) }}" class="border rounded-lg p-4 transition hover:border-indigo-300 hover:shadow-sm {{ $selectedTemplate === $template ? 'border-indigo-500 ring-1 ring-indigo-200' : 'border-slate-200' }}">
                                <div class="h-24 rounded-md border border-slate-200 bg-slate-50 p-3">
                                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                                        {{ $templateLabels[$template] }}
                                    </div>
                                    <div class="mt-2 space-y-1">
                                        @if ($template === 'classic')
                                            <div class="h-2 w-2/3 rounded bg-slate-200"></div>
                                            <div class="h-2 w-full rounded bg-slate-100"></div>
                                            <div class="h-2 w-4/5 rounded bg-slate-100"></div>
                                        @elseif ($template === 'traditional')
                                            <div class="h-3 w-full rounded bg-slate-300"></div>
                                            <div class="h-2 w-11/12 rounded bg-slate-100"></div>
                                            <div class="h-2 w-3/5 rounded bg-slate-100"></div>
                                        @else
                                            <div class="h-2 w-1/2 rounded bg-blue-500"></div>
                                            <div class="h-2 w-full rounded bg-slate-100"></div>
                                            <div class="h-2 w-2/3 rounded bg-slate-100"></div>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-sm font-medium text-slate-700">
                                        {{ $templateLabels[$template] }}
                                    </span>
                                    @if ($selectedTemplate === $template)
                                        <span class="text-xs font-semibold uppercase text-indigo-600">Selected</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">Selected Template</h3>
                        <p class="text-sm text-slate-500">
                            {{ $templateLabels[$selectedTemplate] ?? ucfirst($selectedTemplate) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-slate-800">Live Preview</h3>
                </div>
                <div class="p-8">
                    @include('student.documents.templates.resume-'.$selectedTemplate, ['user' => $user, 'isPdf' => false])
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
