<?php

namespace App\Services;

use App\Models\CoverLetter;
use App\Models\User;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\ListItem;

class StudentWordDocumentService
{
    private const COLUMN_BREAK_MARKER = '[[ATS_COLUMN_BREAK]]';

    private const ATS_STYLES = [
        'classic' => [
            'label' => 'Classic ATS',
            'font' => 'Times New Roman',
            'baseSize' => 10.5,
            'marginVertical' => 1.5,
            'marginHorizontal' => 1.7,
            'nameSize' => 23,
            'nameColor' => '111827',
            'nameAlignment' => Jc::START,
            'uppercaseName' => false,
            'roleSize' => 10.5,
            'contactSize' => 9,
            'headingSize' => 11,
            'headingColor' => '111827',
            'headingBorder' => '111827',
            'headingBefore' => 100,
            'headingAfter' => 55,
            'headerAfter' => 130,
            'bodyLineHeight' => 1.15,
            'bodyAfter' => 80,
            'listAfter' => 45,
            'headerRule' => false,
        ],
        'prime-ats' => [
            'label' => 'Modern ATS',
            'font' => 'Arial',
            'baseSize' => 10,
            'marginVertical' => 1.4,
            'marginHorizontal' => 1.6,
            'nameSize' => 22,
            'nameColor' => '1D4ED8',
            'nameAlignment' => Jc::CENTER,
            'uppercaseName' => false,
            'roleSize' => 10,
            'contactSize' => 8.5,
            'headingSize' => 11,
            'headingColor' => '1D4ED8',
            'headingBorder' => 'CBD5E1',
            'headingBefore' => 90,
            'headingAfter' => 55,
            'headerAfter' => 140,
            'bodyLineHeight' => 1.15,
            'bodyAfter' => 80,
            'listAfter' => 45,
            'headerRule' => false,
        ],
        'traditional' => [
            'label' => 'Two-Column ATS',
            'font' => 'Arial',
            'baseSize' => 9.5,
            'marginVertical' => 1.2,
            'marginHorizontal' => 1.45,
            'nameSize' => 20,
            'nameColor' => '0F172A',
            'nameAlignment' => Jc::START,
            'uppercaseName' => true,
            'roleSize' => 9.5,
            'contactSize' => 8,
            'headingSize' => 10,
            'headingColor' => '0F172A',
            'headingBorder' => '64748B',
            'headingBefore' => 70,
            'headingAfter' => 35,
            'headerAfter' => 90,
            'bodyLineHeight' => 1.05,
            'bodyAfter' => 55,
            'listAfter' => 25,
            'headerRule' => true,
        ],
    ];

    public function __construct(private readonly StudentResumeDataService $resumeDataService) {}

    public function resume(User $user, string $template = 'prime-ats'): string
    {
        $resume = $this->resumeDataService->for($user);
        $style = self::ATS_STYLES[$template] ?? self::ATS_STYLES['prime-ats'];
        $word = new PhpWord;
        $word->setDefaultFontName($style['font']);
        $word->setDefaultFontSize($style['baseSize']);
        $word->getDocInfo()
            ->setTitle($resume['name'].' - '.$style['label'].' Resume')
            ->setSubject('Applicant Tracking System compatible '.$style['label'].' resume');
        $sectionStyle = [
            'paperSize' => 'A4',
            'orientation' => 'portrait',
            'pageSizeW' => Converter::cmToTwip(21),
            'pageSizeH' => Converter::cmToTwip(29.7),
            'marginTop' => Converter::cmToTwip($style['marginVertical']),
            'marginBottom' => Converter::cmToTwip($style['marginVertical']),
            'marginLeft' => Converter::cmToTwip($style['marginHorizontal']),
            'marginRight' => Converter::cmToTwip($style['marginHorizontal']),
        ];

        if ($template === 'traditional') {
            $headerSection = $word->addSection($sectionStyle);
            $this->atsHeader($headerSection, $resume, $style);
            $columnSection = $word->addSection([
                ...$sectionStyle,
                'breakType' => 'continuous',
                'colsNum' => 2,
                'colsSpace' => Converter::cmToTwip(0.7),
            ]);
            $this->twoColumnAtsResume($columnSection, $resume, $style);
        } else {
            $section = $word->addSection($sectionStyle);
            $this->atsResume($section, $resume, $style);
        }

        return $this->save($word);
    }

    public function coverLetter(User $user, CoverLetter $letter): string
    {
        $word = new PhpWord;
        $word->setDefaultFontName('Arial');
        $word->setDefaultFontSize(11);
        $section = $word->addSection([
            'paperSize' => 'A4',
            'marginTop' => Converter::cmToTwip(1.5),
            'marginBottom' => Converter::cmToTwip(1.5),
            'marginLeft' => Converter::cmToTwip(1.8),
            'marginRight' => Converter::cmToTwip(1.8),
        ]);
        $profile = $user->profile;
        $name = $profile?->full_name ?: $user->name;

        $section->addText($name, ['bold' => true, 'size' => 24, 'color' => '0F172A'], ['spaceAfter' => 40]);
        $section->addText(($profile?->personal_email ?: $user->email).' | '.($profile?->contact_number ?: ''), ['size' => 10, 'color' => '475569'], [
            'spaceAfter' => 220,
            'borderBottomColor' => 'E2E8F0',
            'borderBottomSize' => 12,
        ]);
        $section->addText(now()->format('F j, Y'), [], ['spaceAfter' => 180]);
        $section->addText($letter->hiring_manager ?: 'Hiring Manager', ['bold' => true]);
        $section->addText($letter->company_name ?: 'Company Name');
        $section->addText('Malaysia', [], ['spaceAfter' => 240]);
        $section->addText('Dear '.($letter->hiring_manager ?: 'Hiring Manager').',', [], ['spaceAfter' => 160]);

        foreach (preg_split('/\R{2,}/', trim($letter->body_text)) ?: [] as $paragraph) {
            $section->addText(trim($paragraph), [], ['alignment' => Jc::BOTH, 'lineHeight' => 1.6, 'spaceAfter' => 160]);
        }

        $section->addText('Sincerely,', [], ['spaceBefore' => 220, 'spaceAfter' => 360]);
        $section->addText($name, ['bold' => true]);

        return $this->save($word);
    }

    private function atsResume($section, array $resume, array $style): void
    {
        $this->atsHeader($section, $resume, $style);

        $this->atsHeading($section, 'PROFESSIONAL SUMMARY', $style);
        $section->addText($resume['summary'], ['size' => $style['baseSize']], [
            'lineHeight' => $style['bodyLineHeight'],
            'spaceAfter' => $style['bodyAfter'],
        ]);

        $this->addProjects($section, $resume, $style);
        $this->addEducation($section, $resume, $style);

        $this->atsHeading($section, 'SKILLS', $style);
        $section->addText(implode(', ', $resume['skills']), ['size' => $style['baseSize']], ['spaceAfter' => $style['bodyAfter']]);

        if ($resume['languages'] !== []) {
            $this->atsHeading($section, 'LANGUAGES', $style);
            $section->addText(implode(', ', $resume['languages']), ['size' => $style['baseSize']], ['spaceAfter' => $style['bodyAfter']]);
        }

        if ($resume['references'] !== []) {
            $this->atsHeading($section, 'REFERENCES', $style);
            foreach ($resume['references'] as $reference) {
                $section->addText($reference, ['size' => $style['baseSize']], ['spaceAfter' => $style['listAfter']]);
            }
        }
    }

    private function atsHeader($section, array $resume, array $style): void
    {
        $name = $style['uppercaseName'] ? mb_strtoupper($resume['name']) : $resume['name'];

        $section->addText($name, ['bold' => true, 'size' => $style['nameSize'], 'color' => $style['nameColor']], [
            'alignment' => $style['nameAlignment'],
            'spaceAfter' => 20,
        ]);
        $section->addText($resume['title'], ['bold' => true, 'size' => $style['roleSize'], 'color' => '334155'], [
            'alignment' => $style['nameAlignment'],
            'spaceAfter' => 30,
        ]);
        $contactParagraph = [
            'alignment' => $style['nameAlignment'],
            'spaceAfter' => $style['headerAfter'],
        ];

        if ($style['headerRule']) {
            $contactParagraph['borderBottomColor'] = $style['headingBorder'];
            $contactParagraph['borderBottomSize'] = 6;
        }

        $section->addText(
            implode(' | ', $resume['contact']),
            ['size' => $style['contactSize'], 'color' => '475569'],
            $contactParagraph
        );
    }

    private function twoColumnAtsResume($section, array $resume, array $style): void
    {
        $this->atsHeading($section, 'PROFESSIONAL SUMMARY', $style);
        $section->addText($resume['summary'], ['size' => $style['baseSize']], [
            'lineHeight' => $style['bodyLineHeight'],
            'spaceAfter' => $style['bodyAfter'],
        ]);

        $this->atsHeading($section, 'SKILLS', $style);
        foreach ($resume['skills'] as $skill) {
            $section->addText($skill, ['size' => $style['baseSize']], ['spaceAfter' => $style['listAfter']]);
        }

        if ($resume['languages'] !== []) {
            $this->atsHeading($section, 'LANGUAGES', $style);
            foreach ($resume['languages'] as $language) {
                $section->addText($language, ['size' => $style['baseSize']], ['spaceAfter' => $style['listAfter']]);
            }
        }

        if ($resume['references'] !== []) {
            $this->atsHeading($section, 'REFERENCES', $style);
            foreach ($resume['references'] as $reference) {
                $section->addText($reference, ['size' => $style['baseSize']], ['spaceAfter' => $style['listAfter']]);
            }
        }

        $section->addText(self::COLUMN_BREAK_MARKER, ['size' => 1], ['spaceAfter' => 0]);

        $this->addProjects($section, $resume, $style, true);
        $this->addEducation($section, $resume, $style);
    }

    private function addProjects($section, array $resume, array $style, bool $firstInColumn = false): void
    {
        if ($resume['projects'] !== []) {
            $headingStyle = $firstInColumn ? [...$style, 'headingBefore' => 0] : $style;
            $this->atsHeading($section, 'PROJECTS', $headingStyle);

            foreach ($resume['projects'] as $project) {
                $section->addListItem($project, 0, ['size' => $style['baseSize']], ListItem::TYPE_BULLET_FILLED, [
                    'spaceAfter' => $style['listAfter'],
                ]);
            }
        }
    }

    private function addEducation($section, array $resume, array $style): void
    {
        $this->atsHeading($section, 'EDUCATION', $style);

        foreach ($resume['education'] as $education) {
            $heading = $education['qualification'];

            if ($education['dates'] !== '') {
                $heading .= ' | '.$education['dates'];
            }

            $section->addText($heading, ['bold' => true, 'size' => $style['baseSize']], [
                'keepNext' => true,
                'spaceAfter' => 15,
            ]);
            $section->addText($education['institution'], ['italic' => true, 'size' => $style['baseSize'] - 0.5, 'color' => '334155'], [
                'keepNext' => $education['details'] !== [],
                'spaceAfter' => $education['details'] === [] ? $style['bodyAfter'] : 25,
            ]);

            foreach ($education['details'] as $detail) {
                $section->addListItem($detail, 0, ['size' => $style['baseSize'] - 0.5], ListItem::TYPE_BULLET_FILLED, [
                    'spaceAfter' => $style['listAfter'],
                ]);
            }
        }
    }

    private function atsHeading($section, string $heading, array $style): void
    {
        $section->addText($heading, ['bold' => true, 'size' => $style['headingSize'], 'color' => $style['headingColor']], [
            'borderBottomColor' => $style['headingBorder'],
            'borderBottomSize' => 6,
            'keepNext' => true,
            'spaceBefore' => $style['headingBefore'],
            'spaceAfter' => $style['headingAfter'],
        ]);
    }

    private function save(PhpWord $word): string
    {
        Settings::setOutputEscapingEnabled(true);

        $path = tempnam(sys_get_temp_dir(), 'wims-docx-');
        IOFactory::createWriter($word, 'Word2007')->save($path);
        $this->replaceColumnBreakMarker($path);
        $contents = file_get_contents($path);
        @unlink($path);

        return $contents;
    }

    private function replaceColumnBreakMarker(string $path): void
    {
        $zip = new \ZipArchive;

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Unable to open generated DOCX for column layout processing.');
        }

        $xml = $zip->getFromName('word/document.xml');

        if (! is_string($xml) || ! str_contains($xml, self::COLUMN_BREAK_MARKER)) {
            $zip->close();

            return;
        }

        $pattern = '~<w:p\b[^>]*>(?:(?!</w:p>).)*\[\[ATS_COLUMN_BREAK\]\](?:(?!</w:p>).)*</w:p>~s';
        $replacement = '<w:p><w:pPr><w:spacing w:before="0" w:after="0" w:line="1" w:lineRule="exact"/></w:pPr><w:r><w:rPr><w:sz w:val="2"/></w:rPr><w:br w:type="column"/></w:r></w:p>';
        $processed = preg_replace($pattern, $replacement, $xml, 1, $count);

        if ($count !== 1 || ! is_string($processed)) {
            $zip->close();

            throw new \RuntimeException('Unable to create the DOCX column break.');
        }

        $zip->addFromString('word/document.xml', $processed);
        $zip->close();
    }
}
