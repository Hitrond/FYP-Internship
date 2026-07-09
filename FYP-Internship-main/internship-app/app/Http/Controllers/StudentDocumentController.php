<?php

namespace App\Http\Controllers;

use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentDocumentController extends Controller
{
    public function upload(Request $request, string $type)
    {
        $type = $this->resolveType($type);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $file = $request->file('document');
        $path = $file->store(
            'student-documents/'.$request->user()->id.'/'.$type,
            'local'
        );

        $request->user()->studentDocuments()->create([
            'type' => $type,
            'title' => $validated['title'],
            'source' => 'uploaded',
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return redirect()->route($this->libraryRoute($type))
            ->with('document-success', 'Document uploaded successfully.');
    }

    public function download(Request $request, StudentDocument $document)
    {
        $this->authorizeOwner($request, $document);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name
        );
    }

    public function destroy(Request $request, StudentDocument $document)
    {
        $this->authorizeOwner($request, $document);

        Storage::disk('local')->delete($document->file_path);
        $type = $document->type;
        $document->delete();

        return redirect()->route($this->libraryRoute($type))
            ->with('document-success', 'Document deleted.');
    }

    private function authorizeOwner(Request $request, StudentDocument $document): void
    {
        abort_unless((int) $document->user_id === (int) $request->user()->id, 403);
    }

    private function resolveType(string $type): string
    {
        $normalized = Str::of($type)->replace('-', '_')->toString();

        abort_unless(in_array($normalized, [
            StudentDocument::TYPE_RESUME,
            StudentDocument::TYPE_COVER_LETTER,
        ], true), 404);

        return $normalized;
    }

    private function libraryRoute(string $type): string
    {
        return $type === StudentDocument::TYPE_RESUME
            ? 'student.resume.builder'
            : 'student.cover-letter.create';
    }
}
