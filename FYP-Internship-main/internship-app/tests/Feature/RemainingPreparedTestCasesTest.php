<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\EvaluationForm;
use App\Models\Logbook;
use App\Models\PlacementClearance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RemainingPreparedTestCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_acc4_login_requires_email_and_password(): void
    {
        $this->post(route('login'), [])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_acc8_password_reset_rejects_invalid_and_unknown_email(): void
    {
        $this->post(route('password.email'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors('email');

        $this->post(route('password.email'), ['email' => 'unknown@example.test'])
            ->assertSessionHasErrors('email');
    }

    public function test_acc10_password_reset_rejects_mismatch_and_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.store'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors('password');

        $this->post(route('password.store'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_stu4_profile_rejects_invalid_fields(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->put(route('student.profile.update'), [
                'personal_email' => 'invalid-email',
                'internship_status' => 'unsupported',
                'linkedin_url' => 'not-a-url',
            ])
            ->assertSessionHasErrors(['personal_email', 'internship_status', 'linkedin_url']);

        $this->assertDatabaseMissing('profiles', ['user_id' => $student->id]);
    }

    public function test_stu5_and_stu6_manage_owned_education_and_skills_only(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->post(route('student.education.store'), [
            'institution_name' => 'Asia Pacific University',
            'degree' => 'BSc (Hons)',
            'field_of_study' => 'Software Engineering',
            'start_date' => '2023-01-01',
            'end_date' => '2026-12-31',
        ])->assertSessionHasNoErrors();

        $this->actingAs($student)->post(route('student.skill.store'), [
            'name' => 'Laravel',
            'proficiency' => 'Advanced',
        ])->assertSessionHasNoErrors();

        $education = $student->education()->firstOrFail();
        $skill = $student->skills()->firstOrFail();

        $this->actingAs($otherStudent)
            ->delete(route('student.education.destroy', $education))
            ->assertForbidden();
        $this->actingAs($otherStudent)
            ->delete(route('student.skill.destroy', $skill))
            ->assertForbidden();

        $this->actingAs($student)
            ->delete(route('student.education.destroy', $education))
            ->assertSessionHasNoErrors();
        $this->actingAs($student)
            ->delete(route('student.skill.destroy', $skill))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('education', ['id' => $education->id]);
        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
    }

    public function test_doc8_rejects_unsupported_and_oversized_documents(): void
    {
        Storage::fake('local');
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->post(route('student.resume.upload'), [
            'title' => 'Unsupported',
            'document' => UploadedFile::fake()->create('resume.exe', 10, 'application/octet-stream'),
        ])->assertSessionHasErrors('document');

        $this->actingAs($student)->post(route('student.resume.upload'), [
            'title' => 'Too Large',
            'document' => UploadedFile::fake()->create('resume.pdf', 10241, 'application/pdf'),
        ])->assertSessionHasErrors('document');

        $this->assertDatabaseCount('student_documents', 0);
    }

    public function test_app1_to_app5_company_application_crud_validation_and_ownership(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->post(route('student.companies.store'), [
            'company_name' => 'Example Industries',
            'position_title' => 'Software Intern',
            'status' => 'Applied',
        ])->assertRedirect(route('student.companies.index'));

        $application = $student->applications()->firstOrFail();
        $this->assertSame('Example Industries', $application->company_name);

        $this->actingAs($student)->post(route('student.companies.store'), [
            'company_name' => '',
            'status' => 'Invalid Status',
            'contact_email' => 'invalid-email',
            'job_url' => 'not-a-url',
        ])->assertSessionHasErrors(['company_name', 'status', 'contact_email', 'job_url']);

        $this->actingAs($student)->put(route('student.companies.update', $application), [
            'company_name' => 'Updated Industries',
            'status' => 'Interviewing',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'company_name' => 'Updated Industries',
            'status' => 'Interviewing',
        ]);

        $this->actingAs($otherStudent)
            ->put(route('student.companies.update', $application), [
                'company_name' => 'Hijacked',
                'status' => 'Accepted',
            ])->assertForbidden();
        $this->actingAs($otherStudent)
            ->delete(route('student.companies.destroy', $application))
            ->assertForbidden();

        $this->actingAs($student)
            ->delete(route('student.companies.destroy', $application))
            ->assertRedirect(route('student.companies.index'));
        $this->assertDatabaseMissing('applications', ['id' => $application->id]);
    }

    public function test_app7_rejects_unsupported_and_oversized_offer_letters(): void
    {
        Storage::fake('local');
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->post(route('student.companies.store'), [
            'company_name' => 'Invalid File Company',
            'status' => 'Offered',
            'offer_letter' => UploadedFile::fake()->create('offer.docx', 10, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ])->assertSessionHasErrors('offer_letter');

        $this->actingAs($student)->post(route('student.companies.store'), [
            'company_name' => 'Oversized File Company',
            'status' => 'Offered',
            'offer_letter' => UploadedFile::fake()->create('offer.pdf', 10241, 'application/pdf'),
        ])->assertSessionHasErrors('offer_letter');

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_plc4_rejected_placement_can_be_resubmitted(): void
    {
        Storage::fake('local');
        $mentor = User::factory()->create(['role' => 'mentor']);
        $student = User::factory()->create(['role' => 'student', 'mentor_id' => $mentor->id]);

        PlacementClearance::create(array_merge($this->placementData($student, $mentor), [
            'status' => 'rejected',
            'rejection_reason' => 'Correct the company information.',
        ]));

        $this->actingAs($student)->post(route('student.clearance.store'), [
            'company_name' => 'Corrected Company',
            'office_address' => 'Kuala Lumpur',
            'start_date' => '2026-08-03',
            'end_date' => '2026-11-20',
            'supervisor_name' => 'Industry Supervisor',
            'supervisor_email' => 'supervisor@company.test',
            'supervisor_personal_email' => 'supervisor.personal@example.test',
            'job_offer' => UploadedFile::fake()->create('offer.pdf', 50, 'application/pdf'),
            'indemnity_letter' => UploadedFile::fake()->create('indemnity.pdf', 50, 'application/pdf'),
            'placement_agreement' => UploadedFile::fake()->create('agreement.pdf', 50, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('placement_clearances', [
            'student_id' => $student->id,
            'company_name' => 'Corrected Company',
            'status' => 'pending',
        ]);
        $this->assertSame(2, PlacementClearance::where('student_id', $student->id)->count());
    }

    public function test_men5_mentor_rejects_placement_with_feedback(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $student = User::factory()->create(['role' => 'student', 'mentor_id' => $mentor->id]);
        $placement = PlacementClearance::create(array_merge($this->placementData($student, $mentor), [
            'status' => 'pending',
        ]));

        $this->actingAs($mentor)
            ->patch(route('mentor.clearances.reject', $placement), [
                'rejection_reason' => 'The placement dates must be corrected.',
            ])->assertRedirect(route('mentor.clearances.index'));

        $this->assertDatabaseHas('placement_clearances', [
            'id' => $placement->id,
            'status' => 'rejected',
            'rejection_reason' => 'The placement dates must be corrected.',
        ]);
        $this->assertNotNull($placement->refresh()->rejected_at);
    }

    public function test_sup9_supervisor_can_view_decision_history(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student = User::factory()->create(['role' => 'student', 'supervisor_id' => $supervisor->id]);
        Logbook::create([
            'user_id' => $student->id,
            'week_number' => 1,
            'start_date' => '2026-08-03',
            'end_date' => '2026-08-07',
            'description' => 'Completed assigned development tasks.',
            'status' => 'rejected',
            'supervisor_remarks' => 'Add measurable outcomes.',
        ]);

        $this->actingAs($supervisor)
            ->get(route('supervisor.logbooks.history'))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('Add measurable outcomes.');
    }

    public function test_adm1_to_adm4_admin_user_crud_and_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $existing = User::factory()->create(['email' => 'existing@example.test']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Student',
            'email' => 'new.student@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'student',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'new.student@example.test')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => '',
            'email' => $existing->email,
            'password' => 'short',
            'password_confirmation' => 'different',
            'role' => 'unsupported',
        ])->assertSessionHasErrors(['name', 'email', 'password', 'role']);

        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => 'New Mentor',
            'email' => 'new.mentor@example.test',
            'role' => 'mentor',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'mentor',
            'email' => 'new.mentor@example.test',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_adm14_and_adm15_create_and_activate_competing_evaluation_forms(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.evaluation-forms.store'), [
            'type' => EvaluationForm::TYPE_MIDTERM,
            'title' => 'Midterm Form V1',
            'version' => '1.0',
            'criteria_text' => "Technical skill\nCommunication",
            'activate' => true,
        ])->assertSessionHasNoErrors();

        $first = EvaluationForm::where('title', 'Midterm Form V1')->firstOrFail();
        $this->assertTrue($first->is_active);
        $this->assertSame('Technical skill', $first->criteria['criterion_1']);

        $this->actingAs($admin)->post(route('admin.evaluation-forms.store'), [
            'type' => EvaluationForm::TYPE_MIDTERM,
            'title' => 'Midterm Form V2',
            'version' => '2.0',
            'criteria_text' => "Technical delivery\nProfessionalism",
        ])->assertSessionHasNoErrors();

        $second = EvaluationForm::where('title', 'Midterm Form V2')->firstOrFail();
        $this->assertFalse($second->is_active);

        $this->actingAs($admin)
            ->patch(route('admin.evaluation-forms.activate', $second))
            ->assertSessionHasNoErrors();

        $this->assertTrue($second->refresh()->is_active);
        $this->assertFalse($first->refresh()->is_active);
    }

    public function test_adm18_empty_progress_export_contains_headers_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('admin.clearances.export', ['search' => 'no-matching-student']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertDownload();

        $lines = array_values(array_filter(preg_split('/\R/', trim($response->streamedContent()))));
        $this->assertCount(1, $lines);
        $this->assertStringContainsString('Student', $lines[0]);
    }

    public function test_sys14_large_cohort_loads_with_accurate_pagination_and_totals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(60)->create(['role' => 'student']);

        $startedAt = microtime(true);
        $response = $this->actingAs($admin)
            ->get(route('admin.clearances.index'))
            ->assertOk()
            ->assertViewHas('students', function ($students): bool {
                return $students->total() === 60
                    && $students->count() === 25
                    && $students->lastPage() === 3;
            })
            ->assertViewHas('summary', fn (array $summary): bool => $summary['total'] === 60);

        $this->assertLessThan(5.0, microtime(true) - $startedAt);
        $response->assertSee('Next');
    }

    private function placementData(User $student, User $mentor): array
    {
        return [
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'company_name' => 'Example Company',
            'office_address' => 'Kuala Lumpur',
            'start_date' => '2026-08-03',
            'end_date' => '2026-11-20',
            'supervisor_name' => 'Industry Supervisor',
            'supervisor_email' => 'supervisor@company.test',
            'supervisor_personal_email' => 'supervisor.personal@example.test',
            'job_offer_path' => 'clearances/offer.pdf',
            'indemnity_path' => 'clearances/indemnity.pdf',
            'placement_agreement_path' => 'clearances/agreement.pdf',
        ];
    }
}
