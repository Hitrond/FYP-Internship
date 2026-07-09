@php
    $defaultCriteriaText = collect(\App\Models\EvaluationForm::defaultCriteria())->values()->implode("\n");
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Assessment setup</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-900">Evaluation Form Versions</h2>
            <p class="mt-1 text-sm text-slate-500">Upload a form version and define the criteria supervisors must complete.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-800">{{ $errors->first() }}</div>
            @endif

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="font-bold text-slate-900">Upload new version</h3>
                <p class="mt-1 text-sm text-slate-500">The uploaded file is kept as the official template. The criteria below control the live supervisor form.</p>

                <form method="POST" action="{{ route('admin.evaluation-forms.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-5 lg:grid-cols-2">
                    @csrf
                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Form title</span>
                        <input name="title" value="{{ old('title') }}" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Company Supervisor Assessment Form">
                    </label>
                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Version</span>
                        <input name="version" value="{{ old('version') }}" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="v2026.1">
                    </label>
                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Evaluation type</span>
                        <select name="type" required class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="midterm" @selected(old('type') === 'midterm')>Midterm</option>
                            <option value="final" @selected(old('type') === 'final')>Final</option>
                        </select>
                    </label>
                    <label>
                        <span class="mb-2 block text-sm font-bold text-slate-700">Semester scope</span>
                        <select name="internship_cycle_id" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All semesters</option>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}" @selected((string) old('internship_cycle_id') === (string) $cycle->id)>{{ $cycle->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="lg:col-span-2">
                        <span class="mb-2 block text-sm font-bold text-slate-700">Official form file</span>
                        <input type="file" name="form_file" accept=".pdf,.doc,.docx,.xls,.xlsx" class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm">
                    </label>
                    <label class="lg:col-span-2">
                        <span class="mb-2 block text-sm font-bold text-slate-700">Criteria, one per line</span>
                        <textarea name="criteria_text" rows="10" required class="w-full rounded-xl border-slate-300 font-mono text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('criteria_text', $defaultCriteriaText) }}</textarea>
                    </label>
                    <label class="lg:col-span-2">
                        <span class="mb-2 block text-sm font-bold text-slate-700">Instructions shown to supervisor</span>
                        <textarea name="instructions" rows="3" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('instructions') }}</textarea>
                    </label>
                    <label class="flex items-center gap-3 lg:col-span-2">
                        <input type="checkbox" name="activate" value="1" class="rounded border-slate-300 text-indigo-600">
                        <span class="text-sm font-semibold text-slate-700">Activate this version immediately</span>
                    </label>
                    <div class="lg:col-span-2">
                        <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Save form version</button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h3 class="font-bold text-slate-900">Saved versions</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[900px] w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Form</th>
                                <th class="px-6 py-4">Scope</th>
                                <th class="px-6 py-4">Criteria</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($forms as $form)
                                <tr>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-900">{{ $form->title }}</p>
                                        <p class="text-xs text-slate-500">{{ ucfirst($form->type) }}{{ $form->version ? ' · '.$form->version : '' }}</p>
                                    </td>
                                    <td class="px-6 py-4">{{ $form->cycle?->name ?? 'All semesters' }}</td>
                                    <td class="px-6 py-4">{{ count($form->criteria ?? []) }} fields</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $form->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $form->is_active ? 'Active' : 'Saved' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            @if($form->uploaded_file_path)
                                                <a href="{{ route('admin.evaluation-forms.download', $form) }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">Download file</a>
                                            @endif
                                            @unless($form->is_active)
                                                <form method="POST" action="{{ route('admin.evaluation-forms.activate', $form) }}">
                                                    @csrf @method('PATCH')
                                                    <button class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Activate</button>
                                                </form>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">No evaluation form versions uploaded yet. The system will use its built-in default criteria until one is active.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
