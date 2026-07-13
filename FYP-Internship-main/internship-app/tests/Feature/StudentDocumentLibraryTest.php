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
            'institution_name' => 'Example University',
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
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($student)
            ->get(route('student.resume.download-doc', ['template' => 'classic']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->assertDownload();

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

    public function test_each_resume_template_generates_valid_pdf_and_editable_docx(): void
    {
        Storage::fake('local');
        $student = User::factory()->create(['role' => 'student']);
        $student->profile()->create([
            'full_name' => 'Document Test Student',
            'personal_email' => $student->email,
            'contact_number' => '+60 12-345 6789',
            'bio' => 'A complete profile summary for document verification.',
            'projects_summary' => "Project Alpha\nProject Beta",
        ]);
        $student->education()->create([
            'institution_name' => 'Example University',
            'degree' => 'Bachelor of Computing',
            'field_of_study' => 'Software Engineering',
            'start_date' => '2023-01-01',
        ]);
        $student->skills()->create(['name' => 'Laravel', 'proficiency' => 'Advanced']);

        foreach (['classic', 'traditional', 'prime-ats'] as $template) {
            $this->actingAs($student)
                ->get(route('student.resume.download', compact('template')))
                ->assertOk()
                ->assertHeader('Content-Type', 'application/pdf')
                ->assertSee('%PDF-', false);

            $docx = $this->actingAs($student)
                ->get(route('student.resume.download-doc', compact('template')))
                ->assertOk()
                ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                ->getContent();

            $this->assertStringStartsWith('PK', $docx);
            $this->assertStringContainsString('word/document.xml', $docx);
        }
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
}
