<?php

namespace Tests\Feature;

use App\Models\InternshipCycle;
use App\Models\InternshipCycleStudent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SemesterManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_draft_semester(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.semesters.create'))
            ->assertOk()
            ->assertSee('Create a semester');

        $response = $this->actingAs($admin)->post(route('admin.semesters.store'), [
            'name' => 'September 2026 Internship',
            'intake_code' => 'INT-SEP-2026',
            'academic_year' => '2026/2027',
            'placement_window_start' => '2026-08-03',
            'placement_window_end' => '2026-09-28',
            'duration_weeks' => 16,
            'timezone' => 'Asia/Singapore',
        ]);

        $cycle = InternshipCycle::first();

        $response->assertRedirect(route('admin.semesters.show', $cycle));
        $this->assertDatabaseHas('internship_cycles', [
            'intake_code' => 'INT-SEP-2026',
            'duration_weeks' => 16,
            'status' => InternshipCycle::STATUS_DRAFT,
        ]);
    }

    public function test_admin_can_build_and_activate_a_cohort_with_mentor_assignments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mentor = User::factory()->create(['role' => 'mentor']);
        $students = User::factory()->count(2)->create(['role' => 'student']);
        $cycle = $this->cycle();

        $this->actingAs($admin)
            ->post(route('admin.semesters.students.store', $cycle), [
                'student_ids' => $students->pluck('id')->all(),
                'mentor_id' => $mentor->id,
            ])
            ->assertRedirect();

        $this->assertSame(2, $cycle->assignments()->count());

        $this->actingAs($admin)
            ->patch(route('admin.semesters.activate', $cycle))
            ->assertRedirect();

        $this->assertSame(InternshipCycle::STATUS_ACTIVE, $cycle->fresh()->status);
        $students->each(
            fn (User $student) => $this->assertSame($mentor->id, $student->fresh()->mentor_id)
        );
    }

    public function test_only_one_semester_can_be_active(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $active = $this->cycle(['status' => InternshipCycle::STATUS_ACTIVE]);
        $draft = $this->cycle([
            'name' => 'January 2027 Internship',
            'intake_code' => 'INT-JAN-2027',
            'placement_window_start' => '2027-01-04',
            'placement_window_end' => '2027-02-01',
        ]);
        InternshipCycleStudent::create([
            'internship_cycle_id' => $draft->id,
            'student_id' => $student->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.semesters.activate', $draft))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(InternshipCycle::STATUS_ACTIVE, $active->fresh()->status);
        $this->assertSame(InternshipCycle::STATUS_DRAFT, $draft->fresh()->status);
    }

    public function test_active_semester_is_attached_to_placement_and_generated_logbooks(): void
    {
        Storage::fake('local');
        Mail::fake();

        $student = User::factory()->create(['role' => 'student']);
        $mentor = User::factory()->create(['role' => 'mentor']);
        $cycle = $this->cycle(['status' => InternshipCycle::STATUS_ACTIVE]);
        InternshipCycleStudent::create([
            'internship_cycle_id' => $cycle->id,
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'assigned_at' => now(),
        ]);
        $student->update(['mentor_id' => $mentor->id]);

        $this->actingAs($student)
            ->post(route('student.clearance.store'), [
                'company_name' => 'Timeline Industries',
                'office_address' => 'Kuala Lumpur',
                'start_date' => '2026-08-03',
                'end_date' => '2026-11-20',
                'supervisor_name' => 'Industry Supervisor',
                'supervisor_email' => 'supervisor@company.test',
                'supervisor_personal_email' => 'supervisor.personal@example.test',
                'job_offer' => UploadedFile::fake()->create('offer.pdf', 100, 'application/pdf'),
                'indemnity_letter' => UploadedFile::fake()->create('indemnity.pdf', 100, 'application/pdf'),
                'placement_agreement' => UploadedFile::fake()->create('agreement.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('student.clearance.create'));

        $placement = $student->placementClearances()->first();
        $this->assertSame($cycle->id, $placement->internship_cycle_id);

        $this->actingAs($mentor)
            ->patch(route('mentor.clearances.approve', $placement))
            ->assertRedirect(route('mentor.clearances.index'));

        $this->assertSame(16, $student->logbooks()->count());
        $this->assertSame(16, $student->logbooks()->where('internship_cycle_id', $cycle->id)->count());
    }

    public function test_closed_semester_blocks_new_placement_submissions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $cycle = $this->cycle(['status' => InternshipCycle::STATUS_ACTIVE]);

        $this->actingAs($admin)
            ->patch(route('admin.semesters.close', $cycle))
            ->assertRedirect();

        $this->actingAs($student)
            ->from(route('student.clearance.create'))
            ->post(route('student.clearance.store'), [])
            ->assertRedirect(route('student.clearance.create'))
            ->assertSessionHas('error', 'Placement submissions are closed because there is no active internship semester.');
    }

    public function test_legacy_completed_placement_generates_timeline_after_dates_are_added(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $placement = $student->placementClearances()->create([
            'company_name' => 'Legacy Industries',
            'office_address' => 'Kuala Lumpur',
            'supervisor_name' => 'Industry Supervisor',
            'supervisor_email' => 'supervisor@legacy.test',
            'supervisor_personal_email' => 'supervisor.personal@legacy.test',
            'job_offer_path' => 'clearances/offer.pdf',
            'indemnity_path' => 'clearances/indemnity.pdf',
            'placement_agreement_path' => 'clearances/agreement.pdf',
            'status' => 'completed',
        ]);

        $this->actingAs($student)
            ->patch(route('student.clearance.dates.update', $placement), [
                'start_date' => '2026-07-06',
                'end_date' => '2026-10-23',
            ])
            ->assertRedirect();

        $this->assertSame(16, $student->logbooks()->count());
        $this->assertDatabaseHas('logbooks', [
            'user_id' => $student->id,
            'week_number' => 1,
            'start_date' => '2026-07-06',
            'end_date' => '2026-07-10',
            'submission_due_at' => '2026-07-17 23:59:59',
            'timeline_generated' => true,
        ]);
    }

    public function test_admin_progress_report_is_isolated_by_semester(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $firstStudent = User::factory()->create(['role' => 'student', 'name' => 'First Cohort Student']);
        $secondStudent = User::factory()->create(['role' => 'student', 'name' => 'Second Cohort Student']);
        $firstCycle = $this->cycle();
        $secondCycle = $this->cycle([
            'name' => 'January 2027 Internship',
            'intake_code' => 'INT-JAN-2027',
            'placement_window_start' => '2027-01-04',
            'placement_window_end' => '2027-02-01',
        ]);

        InternshipCycleStudent::create([
            'internship_cycle_id' => $firstCycle->id,
            'student_id' => $firstStudent->id,
        ]);
        InternshipCycleStudent::create([
            'internship_cycle_id' => $secondCycle->id,
            'student_id' => $secondStudent->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.clearances.index', ['semester' => $firstCycle->id]))
            ->assertOk()
            ->assertSee('First Cohort Student')
            ->assertDontSee('Second Cohort Student');
    }

    private function cycle(array $attributes = []): InternshipCycle
    {
        return InternshipCycle::create(array_merge([
            'name' => 'September 2026 Internship',
            'intake_code' => 'INT-SEP-2026',
            'academic_year' => '2026/2027',
            'placement_window_start' => '2026-08-03',
            'placement_window_end' => '2026-09-28',
            'duration_weeks' => 16,
            'deadline_weekday' => 5,
            'deadline_time' => '23:59:00',
            'timezone' => 'Asia/Singapore',
            'status' => InternshipCycle::STATUS_DRAFT,
        ], $attributes));
    }
}
