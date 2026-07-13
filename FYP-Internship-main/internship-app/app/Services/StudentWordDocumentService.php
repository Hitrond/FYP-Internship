<?php

namespace App\Services;

use App\Models\CoverLetter;
use App\Models\User;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class StudentWordDocumentService
{
    public function resume(User $user, string $template): string
    {
        $word = new PhpWord;
        $word->setDefaultFontName($template === 'classic' ? 'Times New Roman' : 'Arial');
        $word->setDefaultFontSize(10);
        $section = $word->addSection([
            'paperSize' => 'A4',
            'marginTop' => Converter::cmToTwip(1.3),
            'marginBottom' => Converter::cmToTwip(1.3),
            'marginLeft' => Converter::cmToTwip(1.5),
            'marginRight' => Converter::cmToTwip(1.5),
        ]);

        match ($template) {
            'traditional' => $this->traditionalResume($section, $user),
            'prime-ats' => $this->primeResume($section, $user),
            default => $this->classicResume($section, $user),
        };

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

    private function classicResume($section, User $user): void
    {
        $p = $user->profile;
        $name = $p?->full_name ?: $user->name;
        $title = $p?->course_name ?: 'Software Engineering Student';
        $section->addText($name.', '.$title, ['bold' => true, 'size' => 18], ['alignment' => Jc::CENTER, 'spaceAfter' => 60]);
        $section->addText(($p?->contact_number ?: '').' | '.($p?->personal_email ?: $user->email), ['size' => 9, 'color' => '4B5563'], [
            'alignment' => Jc::CENTER, 'spaceAfter' => 180, 'borderBottomColor' => 'D1D5DB', 'borderBottomSize' => 6,
        ]);
        $this->labelledBlock($section, 'PROFILE', $p?->bio ?: 'Add your profile summary in Student Profile.');
        foreach ($user->education as $education) {
            $dates = ($education->start_date?->format('M Y') ?: '').' - '.($education->end_date?->format('M Y') ?: 'Present');
            $text = $education->institution_name."\n".$education->degree.($education->field_of_study ? ' ('.$education->field_of_study.')' : '').($education->notes ? "\n".$education->notes : '');
            $this->labelledBlock($section, 'EDUCATION'."\n".$dates, $text);
        }
        $this->labelledBlock($section, 'EXPERIENCE', $p?->projects_summary ?: 'Add your projects in Student Profile.');
        $skills = $user->skills->map(fn ($s) => $s->name.($s->proficiency ? ' - '.$s->proficiency : ''))->implode("\n");
        $this->labelledBlock($section, 'SKILLS', $skills ?: 'No skills listed yet.');
        $section->addText('Languages: '.($p?->languages_summary ?: 'English, Malay'), ['size' => 9, 'color' => '4B5563'], ['spaceBefore' => 120]);
    }

    private function traditionalResume($section, User $user): void
    {
        $p = $user->profile;
        $table = $section->addTable(['borderSize' => 4, 'borderColor' => 'D1D5DB', 'cellMargin' => 180, 'layout' => 'fixed']);
        $table->addRow();
        $left = $table->addCell(3000, ['bgColor' => 'F3F4F6', 'valign' => 'top']);
        $right = $table->addCell(6000, ['valign' => 'top']);
        $left->addText('INFO', ['bold' => true, 'size' => 12]);
        $left->addText('Phone', ['bold' => true, 'size' => 9], ['spaceBefore' => 140]);
        $left->addText($p?->contact_number ?: '-', ['size' => 9]);
        $left->addText('Email', ['bold' => true, 'size' => 9], ['spaceBefore' => 140]);
        $left->addText($p?->personal_email ?: $user->email, ['size' => 9]);
        $left->addText('SKILLS', ['bold' => true, 'size' => 12], ['spaceBefore' => 260]);
        foreach ($user->skills as $skill) {
            $left->addText($skill->name.($skill->proficiency ? ' - '.$skill->proficiency : ''), ['size' => 9], ['spaceAfter' => 70]);
        }
        $right->addText($p?->full_name ?: $user->name, ['bold' => true, 'size' => 26], ['spaceAfter' => 40]);
        $right->addText($p?->course_name ?: 'Software Engineering Student', ['size' => 11, 'color' => '6B7280'], ['spaceAfter' => 240, 'borderBottomColor' => 'D1D5DB', 'borderBottomSize' => 6]);
        $this->cellSection($right, 'PROFILE', $p?->bio ?: 'Add your profile summary in Student Profile.');
        $this->cellSection($right, 'PROJECT HISTORY', $p?->projects_summary ?: 'Add your projects in Student Profile.');
        if ($user->education->isNotEmpty()) {
            $education = $user->education->map(fn ($e) => $e->degree.' - '.$e->institution_name)->implode("\n");
            $this->cellSection($right, 'EDUCATION', $education);
        }
    }

    private function primeResume($section, User $user): void
    {
        $p = $user->profile;
        $header = $section->addTable(['cellMargin' => 0, 'layout' => 'fixed']);
        $header->addRow();
        $header->addCell(5500)->addText($p?->full_name ?: $user->name, ['size' => 25, 'color' => '2563EB']);
        $contact = $header->addCell(3500);
        $contact->addText($p?->personal_email ?: $user->email, ['size' => 9, 'color' => '3B82F6'], ['alignment' => Jc::END]);
        $contact->addText($p?->contact_number ?: '', ['size' => 9, 'color' => '3B82F6'], ['alignment' => Jc::END]);
        $section->addText($p?->course_name ?: 'Software Engineering Student', ['size' => 11, 'color' => '3B82F6'], ['spaceAfter' => 160]);
        $section->addText($p?->bio ?: 'Add your profile summary in Student Profile.', ['size' => 10], ['spaceAfter' => 180]);
        $this->primeSection($section, 'Project Experience', $p?->projects_summary ?: 'Add your projects in Student Profile.');
        $education = $user->education->map(fn ($e) => $e->degree.' - '.$e->institution_name.' ('.($e->start_date?->format('M Y') ?: '').' - '.($e->end_date?->format('M Y') ?: 'Present').')')->implode("\n");
        $this->primeSection($section, 'Education', $education ?: 'No education records added yet.');
        $this->primeSection($section, 'Areas of Expertise', $user->skills->pluck('name')->implode(' | ') ?: 'No skills listed yet.');
    }

    private function labelledBlock($section, string $label, string $content): void
    {
        $table = $section->addTable(['cellMargin' => 80, 'layout' => 'fixed']);
        $table->addRow();
        $table->addCell(2200)->addText($label, ['bold' => true, 'size' => 9]);
        $cell = $table->addCell(6800);
        foreach (preg_split('/\R/', $content) ?: [] as $line) {
            $cell->addText($line, ['size' => 10], ['spaceAfter' => 60]);
        }
        $section->addText('', [], ['spaceAfter' => 80, 'borderBottomColor' => 'D1D5DB', 'borderBottomSize' => 4]);
    }

    private function cellSection($cell, string $heading, string $content): void
    {
        $cell->addText($heading, ['bold' => true, 'size' => 11], ['spaceBefore' => 180, 'spaceAfter' => 100]);
        foreach (preg_split('/\R/', $content) ?: [] as $line) {
            $cell->addText($line, ['size' => 9], ['spaceAfter' => 70]);
        }
    }

    private function primeSection($section, string $heading, string $content): void
    {
        $section->addText($heading, ['size' => 15, 'color' => '2563EB'], ['spaceBefore' => 120, 'spaceAfter' => 100, 'borderBottomColor' => 'DBEAFE', 'borderBottomSize' => 6]);
        foreach (preg_split('/\R/', $content) ?: [] as $line) {
            $section->addText($line, ['size' => 10], ['spaceAfter' => 70]);
        }
    }

    private function save(PhpWord $word): string
    {
        $path = tempnam(sys_get_temp_dir(), 'wims-docx-');
        IOFactory::createWriter($word, 'Word2007')->save($path);
        $contents = file_get_contents($path);
        @unlink($path);

        return $contents;
    }
}
