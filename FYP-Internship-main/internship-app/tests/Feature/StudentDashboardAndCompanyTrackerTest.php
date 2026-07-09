<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Logbook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentDashboardAndCompanyTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_shows_only_the_students_pipeline_and_logbook_status(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        $student->profile()->create(['internship_status' => 'interviewing']);
        $this->makeTrackedApplication($student, [
            'company_name' => 'Accepted Labs',
            'status' => 'Accepted',
        ]);
        $this->makeTrackedApplication($student, [
            'company_name' => 'Follow Up Tech',
            'status' => 'Applied',
            'next_followup_on' => now()->addDays(2)->toDateString(),
        ]);
        $this->makeTrackedApplication($otherStudent, [
            'company_name' => 'Another Student Company',
            'status' => 'Accepted',
        ]);

        Logbook::create([
            'user_id' => $student->id,
            'week_number' => 1,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-07',
            'description' => 'Weekly progress',
            'status' => 'rejected',
            'supervisor_remarks' => 'Add more detail.',
        ]);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Accepted Labs')
            ->assertSee('Follow Up Tech')
            ->assertSee('Add more detail.')
            ->assertDontSee('Another Student Company');
    }

    public function test_student_can_store_and_privately_download_an_offer_letter(): void
    {
        Storage::fake('local');

        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->post(route('student.companies.store'), [
            'company_name' => 'Example Industries',
            'position_title' => 'Software Intern',
            'status' => 'Offered',
            'contact_name' => 'Hiring Manager',
            'contact_email' => 'manager@example.com',
            'contact_phone' => '+60123456789',
            'next_followup_on' => '2026-07-10',
            'job_url' => 'https://example.com/jobs/intern',
            'offer_letter' => UploadedFile::fake()->create(
                'offer.pdf',
                100,
                'application/pdf'
            ),
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('student.companies.index'));

        $application = Application::where('user_id', $student->id)->firstOrFail();

        $this->assertSame('Hiring Manager', $application->contact_name);
        Storage::disk('local')->assertExists($application->offer_letter_path);

        $this->actingAs($student)
            ->get(route('student.companies.offer-letter', $application))
            ->assertOk()
            ->assertDownload('example-industries-offer-letter.pdf');

        $this->actingAs($otherStudent)
            ->get(route('student.companies.offer-letter', $application))
            ->assertForbidden();
    }

    public function test_replacing_and_deleting_an_offer_letter_cleans_up_private_files(): void
    {
        Storage::fake('local');

        $student = User::factory()->create(['role' => 'student']);
        $oldPath = UploadedFile::fake()->create('old.pdf', 50, 'application/pdf')
            ->store('application-offers', 'local');
        $application = $this->makeTrackedApplication($student, [
            'offer_letter_path' => $oldPath,
        ]);

        $this->actingAs($student)
            ->put(route('student.companies.update', $application), [
                'company_name' => $application->company_name,
                'status' => 'Accepted',
                'offer_letter' => UploadedFile::fake()->create(
                    'new.pdf',
                    75,
                    'application/pdf'
                ),
            ])
            ->assertSessionHasNoErrors();

        $application->refresh();
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($application->offer_letter_path);

        $newPath = $application->offer_letter_path;

        $this->actingAs($student)
            ->delete(route('student.companies.destroy', $application))
            ->assertRedirect(route('student.companies.index'));

        Storage::disk('local')->assertMissing($newPath);
        $this->assertDatabaseMissing('applications', ['id' => $application->id]);
    }

    public function test_legacy_company_tracker_url_redirects_to_canonical_url(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get('/student/company-tracker')
            ->assertRedirect(route('student.companies.index'));
    }

    private function makeTrackedApplication(User $student, array $overrides = []): Application
    {
        return Application::create(array_merge([
            'user_id' => $student->id,
            'company_name' => 'Example Company',
            'position_title' => 'Intern',
            'status' => 'Applied',
        ], $overrides));
    }
}
