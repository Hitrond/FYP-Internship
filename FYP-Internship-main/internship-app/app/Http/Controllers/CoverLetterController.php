<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use App\Models\StudentDocument;
use App\Services\StudentDocumentReadinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class CoverLetterController extends Controller
{
    // 1. Load the Builder Interface
    public function create(StudentDocumentReadinessService $readinessService)
    {
        $user = Auth::user();
        $user->load(['profile', 'skills']);
        // Load existing draft if they have one
        $draft = CoverLetter::where('user_id', $user->id)->first();

        $documents = $user->studentDocuments()
            ->where('type', StudentDocument::TYPE_COVER_LETTER)
            ->latest()
            ->get();

        return view('student.documents.cover-letter', [
            'user' => $user,
            'draft' => $draft,
            'documents' => $documents,
            'readiness' => $readinessService->coverLetter($user),
        ]);
    }

    // 2. Auto-Save the Draft (via AJAX)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'hiring_manager' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'body_text' => 'nullable|string',
        ]);

        CoverLetter::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return response()->json(['status' => 'success']);
    }

    // 3. Generate and Download the PDF
    public function download(StudentDocumentReadinessService $readinessService)
    {
        [$user, $coverLetter, $redirect] = $this->readyCoverLetter($readinessService);

        if ($redirect) {
            return $redirect;
        }

        $data = [
            'user' => $user,
            'profile' => $user->profile,
            'coverLetter' => $coverLetter,
            'date' => now()->format('F j, Y'),
        ];

        $html = view('student.documents.pdf.cover-letter-pdf', $data)->render();
        $pdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_left' => 18,
            'margin_right' => 18,
        ]);
        $pdf->WriteHTML($html);

        $company = $coverLetter->company_name ?: 'general';
        $fileName = Str::slug($user->name.'-'.$company.'-cover-letter').'.pdf';
        $contents = $pdf->Output($fileName, 'S');
        $path = 'student-documents/'.$user->id.'/cover_letter/generated-'.Str::uuid().'.pdf';

        Storage::disk('local')->put($path, $contents);
        $user->studentDocuments()->create([
            'type' => StudentDocument::TYPE_COVER_LETTER,
            'title' => ($coverLetter->company_name ?: 'General').' Cover Letter - '.now()->format('M d, Y H:i'),
            'source' => 'generated',
            'original_name' => $fileName,
            'file_path' => $path,
            'mime_type' => 'application/pdf',
            'size' => strlen($contents),
        ]);

        return response($contents)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
    }

    public function downloadDoc(StudentDocumentReadinessService $readinessService)
    {
        [$user, $coverLetter, $redirect] = $this->readyCoverLetter($readinessService);

        if ($redirect) {
            return $redirect;
        }

        $company = $coverLetter->company_name ?: 'general';
        $fileName = Str::slug($user->name.'-'.$company.'-cover-letter').'.doc';
        $html = view('student.documents.pdf.cover-letter-pdf', [
            'user' => $user,
            'profile' => $user->profile,
            'coverLetter' => $coverLetter,
            'date' => now()->format('F j, Y'),
        ])->render();
        $contents = '<html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>';
        $path = 'student-documents/'.$user->id.'/cover_letter/generated-'.Str::uuid().'.doc';

        Storage::disk('local')->put($path, $contents);
        $user->studentDocuments()->create([
            'type' => StudentDocument::TYPE_COVER_LETTER,
            'title' => ($coverLetter->company_name ?: 'General').' Cover Letter DOC - '.now()->format('M d, Y H:i'),
            'source' => 'generated',
            'original_name' => $fileName,
            'file_path' => $path,
            'mime_type' => 'application/msword',
            'size' => strlen($contents),
        ]);

        return response($contents)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
    }

    private function readyCoverLetter(StudentDocumentReadinessService $readinessService): array
    {
        $user = Auth::user();
        $user->load(['profile', 'skills']);
        $readiness = $readinessService->coverLetter($user);

        if (! $readiness['complete']) {
            return [
                $user,
                null,
                redirect()->route('student.cover-letter.create')
                    ->with(
                        'document-error',
                        'Complete these Student Profile items before generating a cover letter: '.implode(', ', $readiness['missing']).'.'
                    ),
            ];
        }

        $coverLetter = CoverLetter::where('user_id', $user->id)->first();

        $missingDraftFields = collect([
            'Company name' => $coverLetter?->company_name,
            'Internship role' => $coverLetter?->role,
            'Letter content' => $coverLetter?->body_text,
        ])->filter(fn ($value) => blank($value))->keys();

        if ($missingDraftFields->isNotEmpty()) {
            return [
                $user,
                $coverLetter,
                redirect()->route('student.cover-letter.create')
                    ->with('error', 'Save the draft and complete these fields before downloading: '.$missingDraftFields->implode(', ').'.'),
            ];
        }

        return [$user, $coverLetter, null];
    }
}
