<?php

namespace Tests\Feature;

use App\Models\FinalClearance;
use App\Models\PlacementClearance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinalClearanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_needs_both_reviewers_before_submitting(): void
    {
        Storage::fake('local');
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post(route('student.final-clearance.store'), $this->files())
            ->assertSessionHas('final-error');

        $this->assertDatabaseCount('final_clearances', 0);
    }

    public function test_student_needs_an_approved_placement_submission(): void
    {
        Storage::fake('local');
        [$student] = $this->assignedUsers();

        $this->actingAs($student)
            ->post(route('student.final-clearance.store'), $this->files())
            ->assertSessionHas('final-error');

        $this->assertDatabaseCount('final_clearances', 0);
    }

    public function test_submission_files_are_private_to_the_workflow_participants(): void
    {
        Storage::fake('local');
        [$student, $mentor, $supervisor] = $this->assignedUsers();
        $outsider = User::factory()->create(['role' => 'student']);

        $this->submit($student);
        $clearance = FinalClearance::firstOrFail();

        Storage::disk('local')->assertExists($clearance->report_path);
        Storage::disk('local')->assertExists($clearance->report_clearance_form_path);
        $this->assertNull($clearance->slides_path);
        $this->assertNotNull($clearance->placement_clearance_id);

        foreach ([$student, $mentor, $supervisor] as $participant) {
            foreach ([
                'report' => 'final-report.pdf',
                'report-clearance-form' => 'report-clearance-form.pdf',
            ] as $type => $name) {
                $this->actingAs($participant)
                    ->get(route('final-clearances.download', [$clearance, $type]))
                    ->assertOk()
                    ->assertDownload($name);
            }
        }

        $this->actingAs($outsider)
            ->get(route('final-clearances.download', [$clearance, 'report']))
            ->assertForbidden();

        foreach ([$student, $mentor, $supervisor] as $participant) {
            $this->actingAs($participant)
                ->get(route('placement-clearances.view', [
                    $clearance->placementClearance,
                    'placement-agreement',
                ]))
                ->assertOk()
                ->assertHeader('content-disposition', 'inline; filename=placement-agreement.pdf');
        }

        $this->actingAs($outsider)
            ->get(route('placement-clearances.view', [
                $clearance->placementClearance,
                'placement-agreement',
            ]))
            ->assertForbidden();

        $this->actingAs($mentor)
            ->get(route('mentor.final-clearances.index'))
            ->assertOk()
            ->assertSee($student->name);

        $this->actingAs($supervisor)
            ->get(route('supervisor.final-clearances.index'))
            ->assertOk()
            ->assertSee($student->name);
    }

    public function test_both_signatures_are_required_to_complete_clearance(): void
    {
        Storage::fake('local');
        [$student, $mentor, $supervisor] = $this->assignedUsers();
        $this->submit($student);
        $clearance = FinalClearance::firstOrFail();

        $this->actingAs($mentor)
            ->patch(route('mentor.final-clearances.approve', $clearance))
            ->assertSessionHasNoErrors();

        $clearance->refresh();
        $this->assertSame(FinalClearance::STATUS_APPROVED, $clearance->mentor_status);
        $this->assertSame(FinalClearance::STATUS_PENDING, $clearance->status);
        $this->assertFalse($clearance->industrial_hours_completed);
        $this->assertFalse($clearance->company_property_cleared);

        $this->actingAs($supervisor)
            ->patch(route('supervisor.final-clearances.approve', $clearance), [
                'industrial_hours_completed' => '1',
                'company_property_cleared' => '1',
            ])
            ->assertSessionHasNoErrors();

        $clearance->refresh();
        $this->assertSame(FinalClearance::STATUS_COMPLETED, $clearance->status);
        $this->assertSame(FinalClearance::STATUS_APPROVED, $clearance->supervisor_status);
        $this->assertTrue($clearance->industrial_hours_completed);
        $this->assertTrue($clearance->company_property_cleared);
        $this->assertNotNull($clearance->mentor_signed_at);
        $this->assertNotNull($clearance->supervisor_signed_at);
        $this->assertNotNull($clearance->completed_at);
    }

    public function test_rejection_allows_resubmission_and_resets_both_reviews(): void
    {
        Storage::fake('local');
        [$student, $mentor, $supervisor] = $this->assignedUsers();
        $this->submit($student);
        $clearance = FinalClearance::firstOrFail();
        $oldReport = $clearance->report_path;
        $oldReportClearanceForm = $clearance->report_clearance_form_path;

        $this->actingAs($supervisor)
            ->patch(route('supervisor.final-clearances.approve', $clearance), [
                'industrial_hours_completed' => '1',
                'company_property_cleared' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($mentor)
            ->patch(route('mentor.final-clearances.reject', $clearance), [
                'feedback' => 'Please correct the reported industrial hours.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(
            FinalClearance::STATUS_REJECTED,
            $clearance->refresh()->status
        );

        $this->actingAs($student)
            ->post(route('student.final-clearance.store'), [
                'final_report' => UploadedFile::fake()->create(
                    'corrected-report.docx',
                    250,
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ),
                'report_clearance_form' => UploadedFile::fake()->create(
                    'corrected-clearance-form.pdf',
                    100,
                    'application/pdf'
                ),
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('final-success');

        $clearance->refresh();
        $this->assertSame(FinalClearance::STATUS_PENDING, $clearance->status);
        $this->assertSame(FinalClearance::STATUS_PENDING, $clearance->mentor_status);
        $this->assertSame(FinalClearance::STATUS_PENDING, $clearance->supervisor_status);
        $this->assertNull($clearance->mentor_feedback);
        $this->assertNull($clearance->supervisor_signed_at);
        Storage::disk('local')->assertMissing($oldReport);
        Storage::disk('local')->assertMissing($oldReportClearanceForm);
        Storage::disk('local')->assertExists($clearance->report_path);
        Storage::disk('local')->assertExists($clearance->report_clearance_form_path);
        $this->assertSame('corrected-report.docx', $clearance->report_original_name);
    }

    public function test_unassigned_reviewers_cannot_act_on_a_clearance(): void
    {
        Storage::fake('local');
        [$student] = $this->assignedUsers();
        $otherMentor = User::factory()->create(['role' => 'mentor']);
        $this->submit($student);

        $this->actingAs($otherMentor)
            ->patch(route('mentor.final-clearances.approve', FinalClearance::first()), [
                'industrial_hours_completed' => '1',
                'company_property_cleared' => '1',
            ])
            ->assertForbidden();
    }

    private function assignedUsers(): array
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student = User::factory()->create([
            'role' => 'student',
            'mentor_id' => $mentor->id,
            'supervisor_id' => $supervisor->id,
        ]);

        return [$student, $mentor, $supervisor];
    }

    private function submit(User $student): void
    {
        if (! PlacementClearance::where('student_id', $student->id)->exists()) {
            $this->createPlacementClearance($student);
        }

        $this->actingAs($student)
            ->post(route('student.final-clearance.store'), $this->files())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('final-success');
    }

    private function files(): array
    {
        return [
            'final_report' => UploadedFile::fake()->create(
                'final-report.pdf',
                300,
                'application/pdf'
            ),
            'report_clearance_form' => UploadedFile::fake()->create(
                'report-clearance-form.pdf',
                100,
                'application/pdf'
            ),
        ];
    }

    private function createPlacementClearance(User $student): PlacementClearance
    {
        $base = 'placement-clearances/'.$student->id;
        Storage::disk('local')->put($base.'/job-offer.pdf', 'job offer');
        Storage::disk('local')->put($base.'/indemnity.pdf', 'indemnity');
        Storage::disk('local')->put($base.'/placement-agreement.pdf', 'placement form');

        return PlacementClearance::create([
            'student_id' => $student->id,
            'mentor_id' => $student->mentor_id,
            'supervisor_user_id' => $student->supervisor_id,
            'company_name' => 'Example Company',
            'office_address' => '1 Example Street',
            'supervisor_name' => 'Company Supervisor',
            'supervisor_email' => 'supervisor@example.com',
            'supervisor_personal_email' => 'supervisor.personal@example.com',
            'job_offer_path' => $base.'/job-offer.pdf',
            'indemnity_path' => $base.'/indemnity.pdf',
            'placement_agreement_path' => $base.'/placement-agreement.pdf',
            'status' => 'approved',
        ]);
    }
}
