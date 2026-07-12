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

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success']);
        }

        return redirect()->route('student.cover-letter.create')
            ->with('success', 'Cover letter draft saved.');
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
        $contents = $this->wordCoverLetter($user, $coverLetter);
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

    private function wordCoverLetter($user, CoverLetter $coverLetter): string
    {
        $profile = $user->profile;
        $name = $this->rtf($user->name ?: 'Your Name');
        $email = $this->rtf($profile?->personal_email ?: $user->email);
        $phone = $this->rtf($profile?->contact_number ?: '+60 12-345 6789');
        $manager = $this->rtf($coverLetter->hiring_manager ?: 'Hiring Manager');
        $company = $this->rtf($coverLetter->company_name ?: 'Company Name');
        $date = $this->rtf(now()->format('F j, Y'));
        $body = $this->rtf($coverLetter->body_text ?: '');

        // A4 with 18 mm side margins and 15 mm top/bottom margins, matching
        // the PDF export. RTF is a real Word-compatible document format and
        // avoids the inconsistent layout caused by HTML disguised as .doc.
        return '{\\rtf1\\ansi\\deff0'
            .'{\\fonttbl{\\f0 Arial;}}'
            .'\\paperw11907\\paperh16840\\margl1021\\margr1021\\margt850\\margb850'
            .'\\widowctrl\\viewkind4\\uc1'
            .'\\pard\\f0\\fs48\\b\\cf0 '.$name.'\\b0\\par'
            .'\\pard\\f0\\fs20\\cf0 '.$email.' | '.$phone.'\\par'
            .'\\pard\\brdrb\\brdrs\\brdrw20\\brsp200\\sa400\\par'
            .'\\pard\\f0\\fs22\\sa240 '.$date.'\\par'
            .'\\pard\\f0\\fs22\\b '.$manager.'\\b0\\line '.$company.'\\line Malaysia\\par'
            .'\\pard\\f0\\fs22\\sa360\\par'
            .'\\pard\\f0\\fs22\\qj\\sl352\\slmult1 Dear '.$manager.',\\par\\par '
            .str_replace("\n", '\\par ', $body)
            .'\\par\\par Sincerely,\\par\\par\\par '
            .'\\b '.$name.'\\b0\\par}';
    }

    private function rtf(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = str_replace(['\\', '{', '}'], ['\\\\', '\\{', '\\}'], $value);

        return preg_replace_callback('/[^\x00-\x7F]/u', function (array $match): string {
            $code = mb_ord($match[0]);

            return '\\u'.($code > 32767 ? $code - 65536 : $code).'?';
        }, $value);
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
