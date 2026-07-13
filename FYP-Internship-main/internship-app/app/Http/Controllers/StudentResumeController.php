<?php

namespace App\Http\Controllers;

use App\Models\StudentDocument;
use App\Services\StudentDocumentReadinessService;
use App\Services\StudentWordDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class StudentResumeController extends Controller
{
    private const TEMPLATE_MAP = [
        'classic' => 'student.documents.templates.resume-classic',
        'traditional' => 'student.documents.templates.resume-traditional',
        'prime-ats' => 'student.documents.templates.resume-prime-ats',
    ];

    public function builder(Request $request, StudentDocumentReadinessService $readinessService)
    {
        $user = $request->user();
        $user->load(['profile', 'education', 'skills']);

        $selectedTemplate = $this->resolveTemplate($request->query('template'));

        return view('student.documents.builder', [
            'user' => $user,
            'selectedTemplate' => $selectedTemplate,
            'templates' => array_keys(self::TEMPLATE_MAP),
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

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->WriteHTML($html);

        $fileName = Str::slug($user->name).'-'.$selectedTemplate.'-resume.pdf';
        $contents = $mpdf->Output($fileName, 'S');
        $path = 'student-documents/'.$user->id.'/resume/generated-'.Str::uuid().'.pdf';

        Storage::disk('local')->put($path, $contents);
        $user->studentDocuments()->create([
            'type' => StudentDocument::TYPE_RESUME,
            'title' => ucfirst($selectedTemplate).' Resume - '.now()->format('M d, Y H:i'),
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

        $fileName = Str::slug($user->name).'-'.$selectedTemplate.'-resume.docx';
        $contents = $wordService->resume($user, $selectedTemplate);
        $path = 'student-documents/'.$user->id.'/resume/generated-'.Str::uuid().'.docx';

        Storage::disk('local')->put($path, $contents);
        $user->studentDocuments()->create([
            'type' => StudentDocument::TYPE_RESUME,
            'title' => ucfirst($selectedTemplate).' Resume DOCX - '.now()->format('M d, Y H:i'),
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

    private function resolveTemplate(?string $template): string
    {
        if ($template && array_key_exists($template, self::TEMPLATE_MAP)) {
            return $template;
        }

        return 'classic';
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
                redirect()->route('student.resume.builder', $request->only('template'))
                    ->with(
                        'document-error',
                        'Complete these Student Profile items before generating a resume: '.implode(', ', $readiness['missing']).'.'
                    ),
            ];
        }

        $selectedTemplate = $this->resolveTemplate($request->query('template'));
        $html = view('student.documents.pdf.resume-pdf', [
            'user' => $user,
            'template' => $selectedTemplate,
        ])->render();

        return [$user, $selectedTemplate, $html, null];
    }
}
