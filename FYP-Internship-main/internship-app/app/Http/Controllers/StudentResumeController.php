<?php

namespace App\Http\Controllers;

use App\Models\StudentDocument;
use App\Services\StudentDocumentReadinessService;
use App\Services\StudentResumeDataService;
use App\Services\StudentWordDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class StudentResumeController extends Controller
{
    private const DEFAULT_TEMPLATE = 'prime-ats';

    private const TEMPLATES = [
        'classic' => [
            'label' => 'Classic ATS',
            'description' => 'Formal black typography with a left-aligned identity block.',
        ],
        'prime-ats' => [
            'label' => 'Modern ATS',
            'description' => 'Contemporary blue headings with a centered identity block.',
        ],
        'traditional' => [
            'label' => 'Two-Column ATS',
            'description' => 'A full-width header with two structured content columns.',
        ],
    ];

    public function __construct(private readonly StudentResumeDataService $resumeDataService) {}

    public function builder(Request $request, StudentDocumentReadinessService $readinessService)
    {
        $user = $request->user();
        $user->load(['profile', 'education', 'skills']);

        $selectedTemplate = $this->resolveTemplate($request);

        return view('student.documents.builder', [
            'user' => $user,
            'resume' => $this->resumeDataService->for($user),
            'selectedTemplate' => $selectedTemplate,
            'templates' => self::TEMPLATES,
            'documents' => $user->studentDocuments()
                ->where('type', StudentDocument::TYPE_RESUME)
                ->latest()
                ->get(),
            'readiness' => $readinessService->resume($user),
        ]);
    }

    public function download(Request $request, StudentDocumentReadinessService $readinessService)
    {
        [$user, $selectedTemplate, $html, $redirect] = $this->readyResume($request, $readinessService);

        if ($redirect) {
            return $redirect;
        }

        $margins = match ($selectedTemplate) {
            'classic' => ['vertical' => 15, 'horizontal' => 17],
            'traditional' => ['vertical' => 12, 'horizontal' => 14.5],
            default => ['vertical' => 14, 'horizontal' => 16],
        };

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => $margins['vertical'],
            'margin_bottom' => $margins['vertical'],
            'margin_left' => $margins['horizontal'],
            'margin_right' => $margins['horizontal'],
        ]);

        $mpdf->WriteHTML($html);

        $fileName = Str::slug($user->profile?->full_name ?: $user->name).'-ats-resume.pdf';
        $contents = $mpdf->Output($fileName, 'S');
        $path = 'student-documents/'.$user->id.'/resume/generated-'.Str::uuid().'.pdf';

        Storage::disk('local')->put($path, $contents);
        $user->studentDocuments()->create([
            'type' => StudentDocument::TYPE_RESUME,
            'title' => self::TEMPLATES[$selectedTemplate]['label'].' Resume - '.now()->format('M d, Y H:i'),
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

    public function downloadDoc(Request $request, StudentDocumentReadinessService $readinessService, StudentWordDocumentService $wordService)
    {
        [$user, $selectedTemplate, $html, $redirect] = $this->readyResume($request, $readinessService);

        if ($redirect) {
            return $redirect;
        }

        $fileName = Str::slug($user->profile?->full_name ?: $user->name).'-ats-resume.docx';
        $contents = $wordService->resume($user, $selectedTemplate);
        $path = 'student-documents/'.$user->id.'/resume/generated-'.Str::uuid().'.docx';

        Storage::disk('local')->put($path, $contents);
        $user->studentDocuments()->create([
            'type' => StudentDocument::TYPE_RESUME,
            'title' => self::TEMPLATES[$selectedTemplate]['label'].' Resume DOCX - '.now()->format('M d, Y H:i'),
            'source' => 'generated',
            'original_name' => $fileName,
            'file_path' => $path,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'size' => strlen($contents),
        ]);

        return response($contents)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
    }

    private function readyResume(Request $request, StudentDocumentReadinessService $readinessService): array
    {
        $user = $request->user();
        $user->load(['profile', 'education', 'skills']);
        $readiness = $readinessService->resume($user);

        if (! $readiness['complete']) {
            return [
                $user,
                null,
                null,
                redirect()->route('student.resume.builder', ['template' => $this->resolveTemplate($request)])
                    ->with(
                        'document-error',
                        'Complete these Student Profile items before generating a resume: '.implode(', ', $readiness['missing']).'.'
                    ),
            ];
        }

        $selectedTemplate = $this->resolveTemplate($request);
        $html = view('student.documents.pdf.resume-pdf', [
            'user' => $user,
            'template' => $selectedTemplate,
            'resume' => $this->resumeDataService->for($user),
        ])->render();

        return [$user, $selectedTemplate, $html, null];
    }

    private function resolveTemplate(Request $request): string
    {
        $template = (string) $request->query('template', self::DEFAULT_TEMPLATE);

        return array_key_exists($template, self::TEMPLATES) ? $template : self::DEFAULT_TEMPLATE;
    }
}
