<?php

namespace Tests\Feature;

use App\Mail\SupervisorWelcomeMail;
use App\Models\Application;
use App\Models\FinalClearance;
use App\Models\Logbook;
use App\Models\PerformanceEvaluation;
use App\Models\PlacementClearance;
use App\Models\User;
use App\Services\StudentDocumentReadinessService;
use Carbon\Carbon;
use Database\Seeders\SusUsabilitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AcademicMentorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.brevo.key' => null,
            'services.brevo.use_api' => false,
        ]);
    }

    public function test_placement_approval_generates_weeks_and_admin_sends_supervisor_login(): void
    {
        Mail::fake();
        [$student, $mentor] = $this->assignedStudent();
        $admin = User::factory()->create(['role' => 'admin']);
        $placement = $this->placement($student);

        $this->actingAs($mentor)
            ->patch(route('mentor.clearances.approve', $placement))
            ->assertRedirect(route('mentor.clearances.index'))
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

    public function test_brevo_supervisor_welcome_credentials_create_a_working_login(): void
    {
        config([
            'services.brevo.key' => 'test-api-key',
            'services.brevo.use_api' => true,
            'mail.from.address' => 'verified@example.com',
            'mail.from.name' => 'InternTrack',
        ]);
        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => '<supervisor@brevo>'], 201),
        ]);

        [$student, $mentor] = $this->assignedStudent();
        $admin = User::factory()->create(['role' => 'admin']);
        $placement = $this->placement($student);

        $this->actingAs($mentor)
            ->patch(route('mentor.clearances.approve', $placement))
            ->assertRedirect(route('mentor.clearances.index'))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('admin.clearances.generate-supervisor', $placement))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $temporaryPassword = null;
        Http::assertSent(function ($request) use (&$temporaryPassword): bool {
            if (
                $request->url() !== 'https://api.brevo.com/v3/smtp/email'
                || data_get($request->data(), 'to.0.email') !== 'industry.login@example.com'
                || ! array_key_exists('textContent', $request->data())
            ) {
                return false;
            }

            preg_match('/^Password: (.+)$/m', $request['textContent'], $matches);
            $temporaryPassword = trim($matches[1] ?? '');

            return $request->hasHeader('api-key', 'test-api-key')
                && $request['to'][0]['email'] === 'industry.login@example.com'
                && filled($temporaryPassword);
        });

        $supervisor = User::where('email', 'industry.login@example.com')->firstOrFail();
        $this->post(route('logout'));

        $this->post(route('login'), [
            'email' => $supervisor->email,
            'password' => $temporaryPassword,
        ])->assertRedirect(route('supervisor.dashboard', absolute: false));

        $this->assertAuthenticatedAs($supervisor);
    }

    public function test_brevo_ip_block_keeps_the_account_and_offers_a_resend_action(): void
    {
        config([
            'services.brevo.key' => 'test-api-key',
            'services.brevo.use_api' => true,
        ]);
        Http::fake([
            'api.brevo.com/*' => Http::response([
                'message' => 'We have detected you are using an unrecognised IP address 175.145.39.3.',
                'code' => 'unauthorized',
            ], 401),
        ]);

        [$student] = $this->assignedStudent();
        $admin = User::factory()->create(['role' => 'admin']);
        $placement = $this->placement($student);
        $placement->update(['status' => 'approved', 'approved_at' => now()]);

        $this->actingAs($admin)
            ->from(route('admin.clearances.index'))
            ->post(route('admin.clearances.generate-supervisor', $placement))
            ->assertRedirect(route('admin.clearances.index'))
            ->assertSessionHas('success', fn (string $message): bool => str_contains($message, 'Brevo blocked email from IP 175.145.39.3')
                && str_contains($message, 'Resend supervisor login'));

        $this->assertDatabaseHas('users', [
            'email' => 'industry.login@example.com',
            'role' => 'supervisor',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.clearances.index'))
            ->assertOk()
            ->assertSee('Resend supervisor login')
            ->assertSee('industry.login@example.com');
    }

    public function test_admin_one_has_a_presentation_ready_supervisor_login_action(): void
    {
        Storage::fake('local');
        $this->seed(SusUsabilitySeeder::class);

        $admin = User::where('email', 'admin1@gmail.com')->firstOrFail();
        $student = User::where('email', 'nadia.presentation@example.test')->firstOrFail();
        $placement = $student->latestPlacementClearance()->firstOrFail();

        $this->assertNull($student->supervisor_id);
        $this->assertSame('approved', $placement->status);
        $this->assertSame('mastervirey@gmail.com', $placement->supervisor_personal_email);
        $this->assertDatabaseMissing('users', ['email' => 'mastervirey@gmail.com']);
        $this->assertDatabaseHas('logbooks', [
            'user_id' => $student->id,
            'week_number' => 1,
            'status' => 'open',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.clearances.index'))
            ->assertOk()
            ->assertSee('Create supervisor login')
            ->assertSee('mastervirey@gmail.com');
    }

    public function test_seeded_students_follow_the_three_presentation_timeline_stages(): void
    {
        Storage::fake('local');
        $this->seed();

        $adam = User::where('email', 'adam@gmail.com')->firstOrFail();
        $adamPlacement = $adam->latestPlacementClearance()->firstOrFail();
        $this->assertSame('pending', $adamPlacement->status);
        $this->assertNull($adam->supervisor_id);
        $this->assertSame(0, $adam->logbooks()->count());

        $this->actingAs($adam)
            ->get(route('student.clearance.create'))
            ->assertOk()
            ->assertSee('You already have a pending submission')
            ->assertSee('DataCore Malaysia');

        $this->actingAs($adam->mentor)
            ->get(route('mentor.clearances.index'))
            ->assertOk()
            ->assertSee('Adam Lee')
            ->assertSee('DataCore Malaysia')
            ->assertSee('pending');

        $aisha = User::where('email', 'aisha@gmail.com')->firstOrFail();
        $aishaPlacement = $aisha->latestPlacementClearance()->firstOrFail();
        $aishaLogbooks = $aisha->logbooks()->orderBy('week_number')->get();
        $this->assertCount(16, $aishaLogbooks);
        $this->assertSame('approved', $aishaLogbooks[0]->status);
        $this->assertSame('rejected', $aishaLogbooks[1]->status);
        $this->assertSame('pending', $aishaLogbooks[2]->status);
        $this->assertSame('open', $aishaLogbooks[3]->status);
        $this->assertTrue($aishaLogbooks->slice(4)->every(fn (Logbook $logbook): bool => $logbook->status === 'scheduled'));
        $this->assertCount(5, $aishaLogbooks[0]->attendance_entries);
        $this->assertNotNull($aishaLogbooks[0]->evidence_file_path);
        $this->assertNotNull($aishaLogbooks[0]->approval_signature_path);
        $this->assertNotNull($aishaLogbooks[0]->approval_stamp_path);
        $this->assertSame($aishaPlacement->start_date->toDateString(), $aishaLogbooks->first()->start_date->toDateString());
        $this->assertSame($aishaPlacement->end_date->toDateString(), $aishaLogbooks->last()->end_date->toDateString());

        $this->actingAs($aisha)
            ->get(route('student.logbook.show', $aishaLogbooks[0]))
            ->assertOk()
            ->assertSee('Weekly objectives')
            ->assertSee('Content, activities and skills applied')
            ->assertSee('Attendance & Rendered Hours', false)
            ->assertSee('Attached Evidence')
            ->assertSee('Digitally verified by the Industrial Supervisor');

        $daniel = User::where('email', 'daniel@gmail.com')->firstOrFail();
        $danielPlacement = $daniel->latestPlacementClearance()->firstOrFail();
        $danielLogbooks = $daniel->logbooks()->orderBy('week_number')->get();
        $this->assertCount(16, $danielLogbooks);
        $this->assertTrue($danielLogbooks->every(fn (Logbook $logbook): bool => $logbook->status === 'approved'));
        $this->assertTrue($danielLogbooks->every(fn (Logbook $logbook): bool => filled($logbook->description)
            && filled($logbook->evidence_file_path)
            && $logbook->verified_minutes === 2400
            && $logbook->approved_by_id === $daniel->supervisor_id
            && $logbook->approved_at !== null));
        $this->assertTrue($danielLogbooks->last()->end_date->lte(today()));
        $this->assertSame($danielPlacement->start_date->toDateString(), $danielLogbooks->first()->start_date->toDateString());
        $this->assertSame($danielPlacement->end_date->toDateString(), $danielLogbooks->last()->end_date->toDateString());
        $this->assertSame('supervisor3@gmail.com', $daniel->supervisor?->email);

        $danielEvaluation = PerformanceEvaluation::where('student_id', $daniel->id)
            ->where('type', PerformanceEvaluation::TYPE_FINAL)
            ->firstOrFail();
        $this->assertSame(PerformanceEvaluation::STATUS_SUBMITTED, $danielEvaluation->status);
        $this->assertSame(9, $danielEvaluation->overall_grade);

        $danielClearance = FinalClearance::where('student_id', $daniel->id)->firstOrFail();
        $this->assertSame(FinalClearance::STATUS_PENDING, $danielClearance->status);
        $this->assertSame(FinalClearance::STATUS_PENDING, $danielClearance->mentor_status);
        $this->assertSame(FinalClearance::STATUS_PENDING, $danielClearance->supervisor_status);
        $this->assertNotNull($danielClearance->report_path);
        $this->assertNotNull($danielClearance->report_clearance_form_path);

        $this->actingAs($daniel->supervisor)
            ->get(route('supervisor.logbooks.history', ['student' => $daniel->id, 'status' => 'approved']))
            ->assertOk()
            ->assertSee('Daniel Tan')
            ->assertSee('approved');

        $this->actingAs($daniel->mentor)
            ->get(route('mentor.logbooks.index', ['student' => $daniel->id, 'status' => 'approved']))
            ->assertOk()
            ->assertSee('Daniel Tan')
            ->assertSee('16/16 approved');

        $this->actingAs($daniel->mentor)
            ->get(route('mentor.final-clearances.index'))
            ->assertOk()
            ->assertSee('Daniel Tan')
            ->assertSee('Approve and sign');

        $this->actingAs($daniel->supervisor)
            ->get(route('supervisor.final-clearances.index'))
            ->assertOk()
            ->assertSee('Daniel Tan')
            ->assertSee('Approve and sign');
    }

    public function test_lecturer_two_sees_internship_monitoring_for_a_completed_placement(): void
    {
        Storage::fake('local');
        $this->seed();

        $lecturer = User::where('email', 'lecturerapu2@gmail.com')->firstOrFail();

        $this->actingAs($lecturer)
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertSeeInOrder([
                'Current event',
                '3. Internship monitoring',
                'Review every student’s weekly logbook progress and exceptions.',
            ]);
    }

    public function test_lecturer_three_sees_completion_for_daniels_submitted_final_clearance(): void
    {
        Storage::fake('local');
        $this->seed();

        $lecturer = User::where('email', 'lecturerapu3@gmail.com')->firstOrFail();

        $this->actingAs($lecturer)
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertSeeInOrder([
                'Current event',
                '4. Completion',
                'Review final clearance and record the final result.',
            ]);
    }

    public function test_seeded_demo_accounts_cover_each_role_and_students_have_complete_resumes(): void
    {
        Storage::fake('local');
        $this->seed(SusUsabilitySeeder::class);

        $accounts = [
            'admin1@gmail.com' => 'admin',
            'adam@gmail.com' => 'student',
            'lecturerapu1@gmail.com' => 'mentor',
            'supervisor1@gmail.com' => 'supervisor',
        ];

        foreach ($accounts as $email => $role) {
            $user = User::where('email', $email)->firstOrFail();

            $this->assertSame($role, $user->role);
            $this->assertTrue(Hash::check('123456789', $user->password));
        }

        $readinessService = app(StudentDocumentReadinessService::class);

        User::whereIn('email', [
            'adam@gmail.com',
            'aisha@gmail.com',
            'daniel@gmail.com',
            'nadia.presentation@example.test',
        ])->get()->each(function (User $student) use ($readinessService): void {
            $readiness = $readinessService->resume($student);

            $this->assertTrue($readiness['complete'], $student->email.' has missing required resume data.');
            $this->assertSame([], collect($readiness['recommended'])->where('complete', false)->pluck('label')->all());
            $this->assertNotEmpty($student->education);
            $this->assertNotEmpty($student->skills);
        });
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
            'description' => "=== Type(s) & Objective(s) ===\nComplete the assigned development work.\n\n=== Content & Skills ===\nApplied Laravel and PostgreSQL skills.",
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
            ->assertSee('Two hours were excluded after verification.')
            ->assertSee('Weekly objectives')
            ->assertSee('Complete the assigned development work.')
            ->assertSee('Content, activities and skills applied')
            ->assertSee('Applied Laravel and PostgreSQL skills.');
    }

    public function test_mentor_dashboard_labels_missing_placement_as_not_available(): void
    {
        [$student, $mentor] = $this->assignedStudent();

        $this->actingAs($mentor)
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertSee('Internship event timeline', false)
            ->assertSee('Placement approval')
            ->assertSee('Not available');

        $this->actingAs($mentor)
            ->get(route('mentor.clearances.index'))
            ->assertOk()
            ->assertSee('Placement approval: Not available');
    }

    public function test_mentor_can_filter_the_multi_student_logbook_monitor(): void
    {
        [$student, $mentor] = $this->assignedStudent();
        $otherStudent = User::factory()->create(['role' => 'student', 'mentor_id' => $mentor->id]);
        Logbook::create([
            'user_id' => $student->id,
            'week_number' => 1,
            'start_date' => now()->startOfWeek(),
            'end_date' => now()->startOfWeek()->addDays(4),
            'description' => 'Approved work.',
            'status' => 'approved',
        ]);

        $this->actingAs($mentor)
            ->get(route('mentor.logbooks.index', ['student' => $student->id, 'status' => 'approved']))
            ->assertOk()
            ->assertSee('Logbook status guide')
            ->assertSee($student->name)
            ->assertSee('1 student shown')
            ->assertSee('Week 1')
            ->assertSee('Approved');
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
