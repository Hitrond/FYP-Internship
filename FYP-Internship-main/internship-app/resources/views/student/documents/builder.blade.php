<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1">
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600">Document studio</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
                    {{ __('Resume Builder') }}
                </h2>
                <p class="text-sm text-slate-500">Choose from three ATS-ready designs and download the selected layout.</p>
            </div>
            @if ($readiness['complete'])
                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('student.resume.download', ['template' => $selectedTemplate]) }}" class="flex min-h-[3.25rem] w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/15 transition hover:bg-emerald-700">
                        Download PDF Resume
                    </a>
                    <a href="{{ route('student.resume.download-doc', ['template' => $selectedTemplate]) }}" class="flex min-h-[3.25rem] w-full items-center justify-center rounded-xl bg-white px-4 py-3 text-sm font-bold text-emerald-700 ring-1 ring-emerald-200 transition hover:bg-emerald-50">
                        Download Editable DOCX
                    </a>
                </div>
            @else
                <a href="{{ route('student.profile.edit') }}#document-profile" class="flex min-h-[3.25rem] w-full items-center justify-center rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-amber-700/15 transition hover:bg-amber-600">
                    Complete Profile to Download
                </a>
            @endif
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('document-error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800">{{ session('document-error') }}</div>
            @endif

            @include('student.documents.partials.profile-readiness', ['readiness' => $readiness])

            @include('student.documents.partials.library', [
                'documentType' => 'resume',
                'documentTypeLabel' => 'Resume',
                'uploadRoute' => route('student.resume.upload'),
                'documents' => $documents,
            ])

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">Template Gallery</h3>
                            <p class="text-sm text-slate-500">Classic and Modern are single-column; Two-Column ATS uses a structured left-to-right reading order. Every preview matches its PDF and DOCX.</p>
                        </div>
                        <a href="{{ route('student.profile.edit') }}" class="text-sm text-indigo-600 hover:text-indigo-700">
                            Add account, summary, projects, education, skills, languages, and references
                        </a>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                        @foreach ($templates as $template => $definition)
                            @php
                                $previewAccent = match ($template) {
                                    'classic' => 'bg-slate-900',
                                    'traditional' => 'bg-slate-700',
                                    default => 'bg-blue-600',
                                };
                                $previewAlignment = $template === 'prime-ats' ? 'mx-auto' : '';
                            @endphp
                            <a href="{{ route('student.resume.builder', ['template' => $template]) }}" class="border rounded-lg p-4 transition hover:border-indigo-300 hover:shadow-sm {{ $selectedTemplate === $template ? 'border-indigo-500 ring-1 ring-indigo-200' : 'border-slate-200' }}">
                                <div class="h-24 rounded-md border border-slate-200 bg-white p-3">
                                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                                        {{ $definition['label'] }}
                                    </div>
                                    @if ($template === 'traditional')
                                        <div class="mt-2">
                                            <div class="h-2 w-1/2 rounded bg-slate-700"></div>
                                            <div class="mt-1 h-1 w-2/3 rounded bg-slate-300"></div>
                                            <div class="mt-2 grid grid-cols-[1fr_2fr] gap-2">
                                                <div class="space-y-1 border-r border-slate-300 pr-2">
                                                    <div class="h-1 rounded bg-slate-400"></div>
                                                    <div class="h-1 w-4/5 rounded bg-slate-100"></div>
                                                </div>
                                                <div class="space-y-1">
                                                    <div class="h-1 rounded bg-slate-400"></div>
                                                    <div class="h-1 w-5/6 rounded bg-slate-100"></div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-2 space-y-1">
                                            <div class="{{ $previewAlignment }} h-2 w-1/2 rounded {{ $previewAccent }}"></div>
                                            <div class="{{ $previewAlignment }} h-1 w-2/3 rounded bg-slate-300"></div>
                                            <div class="mt-2 h-1 w-full rounded {{ $template === 'prime-ats' ? 'bg-blue-200' : 'bg-slate-300' }}"></div>
                                            <div class="h-1 w-5/6 rounded bg-slate-100"></div>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-3 flex items-start justify-between gap-2">
                                    <span class="text-sm font-medium text-slate-700">
                                        {{ $definition['label'] }}
                                    </span>
                                    @if ($selectedTemplate === $template)
                                        <span class="text-xs font-semibold uppercase text-indigo-600">Selected</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-xs leading-5 text-slate-500">{{ $definition['description'] }}</p>
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
                            {{ $templates[$selectedTemplate]['label'] }}
                        </p>
                        @if ($selectedTemplate === 'traditional')
                            <p class="mt-1 text-xs text-amber-700">Two-column layouts can be less reliable in older ATS software. Use Classic or Modern when maximum parsing compatibility is required.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-slate-200">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    <h3 class="text-lg font-semibold text-slate-800">Live Preview</h3>
                </div>
                <div class="bg-slate-200 p-3 sm:p-8">
                    @include('student.documents.partials.resume-document', [
                        'user' => $user,
                        'resume' => $resume,
                        'template' => $selectedTemplate,
                        'preview' => true,
                    ])
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
