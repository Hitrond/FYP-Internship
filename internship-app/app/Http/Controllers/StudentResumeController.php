<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mpdf\Mpdf;

class StudentResumeController extends Controller
{
    private const TEMPLATE_MAP = [
        'classic' => 'student.documents.templates.resume-classic',
        'traditional' => 'student.documents.templates.resume-traditional',
        'prime-ats' => 'student.documents.templates.resume-prime-ats',
    ];

    public function builder(Request $request)
    {
        $user = $request->user();
        $user->load(['profile', 'education', 'skills']);

        $selectedTemplate = $this->resolveTemplate($request->query('template'));

        return view('student.documents.builder', [
            'user' => $user,
            'selectedTemplate' => $selectedTemplate,
            'templates' => array_keys(self::TEMPLATE_MAP),
        ]);
    }

    public function download(Request $request)
    {
        $user = $request->user();
        $user->load(['profile', 'education', 'skills']);

        $selectedTemplate = $this->resolveTemplate($request->query('template'));
        $templateView = self::TEMPLATE_MAP[$selectedTemplate];

        $html = view($templateView, [
            'user' => $user,
            'isPdf' => true,
        ])->render();

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->WriteHTML($html);

        $fileName = 'resume-'.$user->id.'.pdf';

        return response($mpdf->Output($fileName, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
    }

    private function resolveTemplate(?string $template): string
    {
        if ($template && array_key_exists($template, self::TEMPLATE_MAP)) {
            return $template;
        }

        return 'classic';
    }
}
