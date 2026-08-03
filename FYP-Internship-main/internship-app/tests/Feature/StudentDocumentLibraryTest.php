<?php

namespace Tests\Feature;

use App\Models\CoverLetter;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentDocumentLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_upload_download_and_delete_a_private_resume(): void
    {
        Storage::fake('local');

        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post(route('student.resume.upload'), [
                'title' => 'Software Resume',
                'document' => UploadedFile::fake()->create(
                    'software-resume.pdf',
                    150,
                    'application/pdf'
                ),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('student.resume.builder'));

        $document = StudentDocument::firstOrFail();
        $this->assertSame(StudentDocument::TYPE_RESUME, $document->type);
        $this->assertSame('uploaded', $document->source);
        Storage::disk('local')->assertExists($document->file_path);

        $this->actingAs($student)
            ->get(route('student.documents.download', $document))
            ->assertOk()
            ->assertDownload('software-resume.pdf');

        $this->actingAs($otherStudent)
            ->get(route('student.documents.download', $document))
            ->assertForbidden();

        $path = $document->file_path;

        $this->actingAs($student)
            ->delete(route('student.documents.destroy', $document))
            ->assertRedirect(route('student.resume.builder'));

        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseMissing('student_documents', ['id' => $document->id]);
    }

    public function test_cover_letter_upload_uses_the_cover_letter_library(): void
    {
        Storage::fake('local');

        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post(route('student.cover-letter.upload'), [
                'title' => 'Example Company Letter',
                'document' => UploadedFile::fake()->create(
                    'cover-letter.docx',
                    80,
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('student.cover-letter.create'));

        $this->assertDatabaseHas('student_documents', [
            'user_id' => $student->id,
            'type' => StudentDocument::TYPE_COVER_LETTER,
            'source' => 'uploaded',
            'original_name' => 'cover-letter.docx',
        ]);
    }

    public function test_generated_resume_and_cover_letter_are_saved_to_history(): void
    {
        Storage::fake('local');

        $student = User::factory()->create(['role' => 'student']);
        $student->profile()->create([
            'full_name' => $student->name,
            'personal_email' => $student->email,
            'contact_number' => '+60 12-345 6789',
            'bio' => 'Computing student with practical software development experience.',
        ]);
        $student->education()->create([
            'institution_name' => 'Example University & Innovation College',
            'degree' => 'Bachelor',
            'field_of_study' => 'Computing',
            'start_date' => '2023-01-01',
        ]);
        $student->skills()->create([
            'name' => 'Laravel',
            'proficiency' => 'Advanced',
        ]);

        $this->actingAs($student)
            ->get(route('student.resume.download', ['template' => 'classic']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertDownload(str($student->name)->slug().'-ats-resume.pdf');

        $this->actingAs($student)
            ->get(route('student.resume.download-doc', ['template' => 'classic']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->assertDownload(str($student->name)->slug().'-ats-resume.docx');

        CoverLetter::create([
            'user_id' => $student->id,
            'company_name' => 'History Company',
            'hiring_manager' => 'Hiring Manager',
            'role' => 'Intern',
            'body_text' => 'Generated cover letter content.',
        ]);

        $this->actingAs($student)
            ->get(route('student.cover-letter.download'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $documents = StudentDocument::where('user_id', $student->id)
            ->where('source', 'generated')
            ->get();

        $this->assertCount(3, $documents);
        $this->assertEqualsCanonicalizing(
            [StudentDocument::TYPE_RESUME, StudentDocument::TYPE_RESUME, StudentDocument::TYPE_COVER_LETTER],
            $documents->pluck('type')->all()
        );

        foreach ($documents as $document) {
            Storage::disk('local')->assertExists($document->file_path);
        }
    }

    public function test_three_ats_templates_generate_matching_previews_pdfs_and_editable_docx_files(): void
    {
        Storage::fake('local');
        $student = User::factory()->create(['role' => 'student']);
        $student->profile()->create([
            'full_name' => 'Document Test Student',
            'course_name' => 'Software Engineering Student',
            'personal_email' => $student->email,
            'contact_number' => '+60 12-345 6789',
            'bio' => 'A complete profile summary for document verification.',
            'linkedin_url' => 'https://linkedin.com/in/document-test',
            'github_url' => 'https://github.com/document-test',
            'portfolio_url' => 'https://document-test.example.com',
            'projects_summary' => "Project Alpha: Built an internship application portal.\nProject Beta: Designed a reporting dashboard.",
            'languages_summary' => "English (Fluent)\nMalay (Conversational)",
            'references_summary' => 'Available upon request.',
        ]);
        $student->education()->create([
            'institution_name' => 'Example University & Innovation College',
            'degree' => 'Bachelor of Computing',
            'field_of_study' => 'Software Engineering',
            'start_date' => '2023-01-01',
            'end_date' => '2026-12-31',
            'description' => 'Final Year Project: Internship Management System.',
        ]);
        $student->skills()->create(['name' => 'Laravel', 'proficiency' => 'Advanced']);

        $templates = [
            'classic' => ['label' => 'Classic ATS', 'color' => '111827'],
            'prime-ats' => ['label' => 'Modern ATS', 'color' => '1D4ED8'],
            'traditional' => ['label' => 'Two-Column ATS', 'color' => '0F172A'],
        ];
        $pdfHashes = [];

        foreach ($templates as $template => $expectation) {
            $this->actingAs($student)
                ->get(route('student.resume.builder', compact('template')))
                ->assertOk()
                ->assertSee($expectation['label'])
                ->assertSee('data-resume-template="'.$template.'"', false)
                ->assertSee('data-layout="'.($template === 'traditional' ? 'two-column' : 'single-column').'"', false)
                ->assertSee('PROFESSIONAL SUMMARY')
                ->assertSee('Project Alpha: Built an internship application portal.');

            $pdfResponse = $this->actingAs($student)
                ->get(route('student.resume.download', compact('template')))
                ->assertOk()
                ->assertHeader('Content-Type', 'application/pdf')
                ->assertDownload('document-test-student-ats-resume.pdf')
                ->assertSee('%PDF-', false);
            $pdfHashes[] = hash('sha256', $pdfResponse->getContent());

            $response = $this->actingAs($student)
                ->get(route('student.resume.download-doc', compact('template')))
                ->assertOk()
                ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                ->assertDownload('document-test-student-ats-resume.docx');

            $docx = $response->getContent();

            $this->assertStringStartsWith('PK', $docx);
            $this->assertStringContainsString('word/document.xml', $docx);

            $xml = $this->extractDocxXml($docx);
            $text = $this->extractDocxText($docx);
            $this->assertStringContainsString($expectation['color'], $xml);

            if ($template === 'traditional') {
                $this->assertStringContainsString('w:num="2"', $xml);
                $this->assertStringContainsString('w:type="column"', $xml);
                $this->assertStringNotContainsString('ATS_COLUMN_BREAK', $xml);
            } else {
                $this->assertStringNotContainsString('w:type="column"', $xml);
            }

            $this->assertStringContainsString('PROFESSIONAL SUMMARY', $text);
            $this->assertStringContainsString('PROJECTS', $text);
            $this->assertStringContainsString('EDUCATION', $text);
            $this->assertStringContainsString('SKILLS', $text);
            $this->assertStringContainsString('LANGUAGES', $text);
            $this->assertStringContainsString('REFERENCES', $text);
            $this->assertStringContainsString('Project Alpha: Built an internship application portal.', $text);
            $this->assertStringContainsString('Final Year Project: Internship Management System.', $text);
            $this->assertStringNotContainsString('Led backend architecture', $text);
        }

        $this->assertCount(3, array_unique($pdfHashes));

        $this->actingAs($student)
            ->get(route('student.resume.builder', ['template' => 'not-a-template']))
            ->assertOk()
            ->assertSee('data-resume-template="prime-ats"', false);
    }

    public function test_generated_documents_require_a_complete_student_profile(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('student.resume.download', ['template' => 'classic']))
            ->assertRedirect(route('student.resume.builder', ['template' => 'classic']))
            ->assertSessionHas('document-error');

        CoverLetter::create([
            'user_id' => $student->id,
            'company_name' => 'Example Company',
            'role' => 'Intern',
            'body_text' => 'Application body.',
        ]);

        $this->actingAs($student)
            ->get(route('student.cover-letter.download'))
            ->assertRedirect(route('student.cover-letter.create'))
            ->assertSessionHas('document-error');
    }

    public function test_legacy_document_urls_redirect_to_canonical_builders(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get('/student/documents/resume?template=classic')
            ->assertRedirect(route('student.resume.builder', ['template' => 'classic']));

        $this->actingAs($student)
            ->get('/student/documents/cover-letter')
            ->assertRedirect(route('student.cover-letter.create'));
    }

    public function test_student_can_explicitly_save_a_cover_letter_draft(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post(route('student.cover-letter.store'), [
                'company_name' => 'SUS Test Company',
                'hiring_manager' => 'Hiring Manager',
                'role' => 'Software Engineering Intern',
                'body_text' => 'This is simulated cover letter content for usability testing.',
            ])
            ->assertRedirect(route('student.cover-letter.create'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cover_letters', [
            'user_id' => $student->id,
            'company_name' => 'SUS Test Company',
            'role' => 'Software Engineering Intern',
        ]);
    }

    private function extractDocxText(string $contents): string
    {
        $xml = $this->extractDocxXml($contents);
        $withParagraphs = preg_replace('/<\/w:p>/', "\n", $xml);

        return trim(html_entity_decode(strip_tags($withParagraphs), ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }

    private function extractDocxXml(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'ats-resume-test-');
        file_put_contents($path, $contents);

        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($xml);
        $document = new \DOMDocument;
        $this->assertTrue($document->loadXML($xml));

        return $xml;
    }
}
