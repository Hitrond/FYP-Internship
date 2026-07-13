<?php

namespace Tests\Feature;

use App\Mail\SupervisorWelcomeMail;
use App\Models\Application;
use App\Models\Logbook;
use App\Models\PerformanceEvaluation;
use App\Models\PlacementClearance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AcademicMentorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_placement_approval_generates_weeks_and_admin_sends_supervisor_login(): void
    {
        Mail::fake();
        [$student, $mentor] = $this->assignedStudent();
        $admin = User::factory()->create(['role' => 'admin']);
        $placement = $this->placement($student);

        $this->actingAs($mentor)
            ->patch(route('mentor.clearances.approve', $placement))
            ->assertSessionHasNoErrors();

        $placement->refresh();
        $this->assertSame('approved', $placement->status);
        $this->assertNull($placement->supervisor_user_id);
        $this->assertNull($student->refresh()->supervisor_id);

        $weeks = Logbook::where('user_id', $student->id)
            ->orderBy('week_number')
            ->get();
        $this->assertCount(16, $weeks);
        $this->assertSame('2026-07-06', $weeks->first()->start_date->format('Y-m-d'));
        $this->assertSame('2026-07-10', $weeks->first()->end_date->format('Y-m-d'));
        $this->assertSame('2026-07-17', $weeks->first()->submission_due_at->format('Y-m-d'));
        $this->assertTrue($weeks->every->timeline_generated);

        $this->actingAs($admin)
            ->post(route('admin.clearances.generate-supervisor', $placement))
            ->assertSessionHasNoErrors();

        $placement->refresh();
        $this->assertNotNull($placement->supervisor_user_id);
        $this->assertSame($placement->supervisor_user_id, $student->refresh()->supervisor_id);
        $this->assertSame('supervisor', User::findOrFail($placement->supervisor_user_id)->role);
        Mail::assertSent(SupervisorWelcomeMail::class);
    }

    public function test_locked_student_can_request_and_receive_mentor_extension(): void
    {
        [$student, $mentor] = $this->assignedStudent();
        $logbook = Logbook::create([
            'user_id' => $student->id,
            'week_number' => 1,
            'timeline_generated' => true,
            'start_date' => '2026-05-04',
            'end_date' => '2026-05-08',
            'submission_due_at' => '2026-05-15 23:59:59',
            'description' => null,
            'status' => 'open',
        ]);

        $this->actingAs($student)
            ->post(route('student.logbook.extension.request', $logbook), [
                'extension_reason' => 'Hospitalisation prevented submission.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('requested', $logbook->refresh()->extension_status);
        $this->assertSame('overdue_locked', $logbook->status);

        $extensionUntil = now()->addWeek()->format('Y-m-d H:i:s');
        $this->actingAs($mentor)
            ->patch(route('mentor.logbooks.extension.approve', $logbook), [
                'extension_until' => $extensionUntil,
                'extension_decision_note' => 'Approved based on medical evidence.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('open', $logbook->refresh()->status);
        $this->assertSame('approved', $logbook->extension_status);
    }

    public function test_mentor_sees_pipeline_and_can_view_assigned_offer_only(): void
    {
        Storage::fake('local');
        [$student, $mentor] = $this->assignedStudent();
        $otherMentor = User::factory()->create(['role' => 'mentor']);
        Storage::disk('local')->put('application-offers/offer.pdf', 'offer');
        $application = Application::create([
            'user_id' => $student->id,
            'company_name' => 'Pipeline Company',
            'status' => 'Accepted',
            'offer_letter_path' => 'application-offers/offer.pdf',
        ]);

        $this->actingAs($mentor)
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertSee('Pre-placement pipeline oversight')
            ->assertSee('Pipeline Company', false);

        $this->actingAs($mentor)
            ->get(route('applications.offer-letter', $application))
            ->assertOk()
            ->assertDownload();

        $this->actingAs($otherMentor)
            ->get(route('applications.offer-letter', $application))
            ->assertForbidden();
    }

    public function test_mentor_can_view_declared_and_verified_logbook_hours_without_daily_attendance(): void
    {
        [$student, $mentor] = $this->assignedStudent();
        $logbook = Logbook::create([
            'user_id' => $student->id,
            'week_number' => 1,
            'start_date' => now()->startOfWeek(),
            'end_date' => now()->startOfWeek()->addDays(4),
            'description' => 'Completed the assigned development work.',
            'rendered_minutes' => 2400,
            'verified_minutes' => 2280,
            'attendance_entries' => null,
            'attendance_remarks' => 'Two hours were excluded after verification.',
            'status' => 'approved',
        ]);

        $this->actingAs($mentor)
            ->get(route('logbooks.show', $logbook))
            ->assertOk()
            ->assertSee('Student declared:')
            ->assertSee('40.00 hrs')
            ->assertSee('Supervisor verified:')
            ->assertSee('38.00 hrs')
            ->assertSee('Two hours were excluded after verification.');
    }

    public function test_mentor_selects_locks_and_exports_final_result(): void
    {
        [$student, $mentor] = $this->assignedStudent();
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student->update(['supervisor_id' => $supervisor->id]);

        foreach (range(1, 16) as $week) {
            Logbook::create([
                'user_id' => $student->id,
                'week_number' => $week,
                'timeline_generated' => true,
                'start_date' => Carbon::parse('2026-01-05')->addWeeks($week - 1),
                'end_date' => Carbon::parse('2026-01-09')->addWeeks($week - 1),
                'description' => 'Completed weekly reflection.',
                'status' => 'approved',
            ]);
        }

        PerformanceEvaluation::create([
            'student_id' => $student->id,
            'supervisor_id' => $supervisor->id,
            'type' => 'final',
            'ratings' => $this->ratings(),
            'overall_grade' => 8,
            'overall_comments' => 'Strong workplace performance.',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($mentor)
            ->post(route('mentor.results.store', $student), [
                'result' => 'fail',
                'rationale' => 'Academic Mentor reviewed additional concerns and decided the student did not meet the module outcome.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('internship_results', [
            'student_id' => $student->id,
            'result' => 'fail',
            'approved_logbooks' => 16,
            'supervisor_score' => 8,
        ]);

        $this->actingAs($mentor)
            ->get(route('mentor.results.export'))
            ->assertOk()
            ->assertDownload('academic-mentor-cohort-results.csv');
    }

    private function assignedStudent(): array
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $student = User::factory()->create([
            'role' => 'student',
            'mentor_id' => $mentor->id,
        ]);

        return [$student, $mentor];
    }

    private function placement(User $student): PlacementClearance
    {
        return PlacementClearance::create([
            'student_id' => $student->id,
            'mentor_id' => $student->mentor_id,
            'company_name' => 'Timeline Company',
            'office_address' => '1 Timeline Street',
            'start_date' => '2026-07-06',
            'end_date' => '2026-10-23',
            'supervisor_name' => 'Industry Supervisor',
            'supervisor_email' => 'industry@example.com',
            'supervisor_personal_email' => 'industry.login@example.com',
            'job_offer_path' => 'clearances/job-offer.pdf',
            'indemnity_path' => 'clearances/indemnity.pdf',
            'placement_agreement_path' => 'clearances/agreement.pdf',
            'status' => 'pending',
        ]);
    }

    private function ratings(): array
    {
        return collect(PerformanceEvaluation::CRITERIA)
            ->mapWithKeys(fn ($label, $key) => [
                $key => ['rating' => 'B', 'comment' => 'Meets expectations.'],
            ])
            ->all();
    }
}
