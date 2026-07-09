<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-6 py-4">
        <h3 class="text-lg font-semibold text-slate-900">Saved {{ $documentTypeLabel }}s</h3>
        <p class="text-sm text-slate-500">Generated files are saved automatically. You can also upload PDF or Word documents.</p>
    </div>

    @if (session('document-success'))
        <div class="mx-6 mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
            {{ session('document-success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
        <form method="POST" action="{{ $uploadRoute }}" enctype="multipart/form-data" class="space-y-4 rounded-lg bg-slate-50 p-5">
            @csrf
            <div>
                <x-input-label :for="'document_title_'.$documentType" value="Document title" />
                <x-text-input
                    :id="'document_title_'.$documentType"
                    name="title"
                    :value="old('title')"
                    class="mt-1 block w-full"
                    :placeholder="$documentTypeLabel.' title'"
                    required
                />
            </div>
            <div>
                <x-input-label :for="'document_file_'.$documentType" value="PDF or Word file" />
                <input
                    id="{{ 'document_file_'.$documentType }}"
                    type="file"
                    name="document"
                    accept=".pdf,.doc,.docx"
                    class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm"
                    required
                />
            </div>
            <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white hover:bg-slate-700">
                Upload {{ $documentTypeLabel }}
            </button>
        </form>

        <div class="lg:col-span-2">
            @if ($documents->isEmpty())
                <div class="flex min-h-48 items-center justify-center rounded-lg border border-dashed border-slate-300 p-8 text-center">
                    <div>
                        <p class="font-semibold text-slate-700">No saved {{ strtolower($documentTypeLabel) }}s yet</p>
                        <p class="mt-1 text-sm text-slate-500">Generate one below or upload an existing file.</p>
                    </div>
                </div>
            @else
                <div class="divide-y divide-slate-100 rounded-lg border border-slate-200">
                    @foreach ($documents as $document)
                        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="truncate font-semibold text-slate-900">{{ $document->title }}</p>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $document->source === 'generated' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ ucfirst($document->source) }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $document->original_name }}
                                    @if ($document->size)
                                        · {{ number_format($document->size / 1024, 1) }} KB
                                    @endif
                                    · {{ $document->created_at->format('M d, Y H:i') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('student.documents.download', $document) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Download</a>
                                <form method="POST" action="{{ route('student.documents.destroy', $document) }}" onsubmit="return confirm('Delete this document?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
