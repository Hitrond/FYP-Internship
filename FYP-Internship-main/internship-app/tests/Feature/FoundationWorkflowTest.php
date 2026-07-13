<?php

namespace Tests\Feature;

use App\Models\CoverLetter;
use App\Models\Logbook;
use App\Models\PlacementClearance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FoundationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_pages_reject_other_roles(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);

        $this->actingAs($supervisor)
            ->get(route('student.logbook.index'))
            ->assertForbidden();
    }

    public function test_supervisor_rejection_feedback_is_saved_for_assigned_student(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student = User::factory()->create([
            'role' => 'student',
            'supervisor_id' => $supervisor->id,
        ]);
        $logbook = $this->createLogbook($student);

        $this->actingAs($supervisor)
            ->patch(route('supervisor.logbooks.reject', $logbook), [
                'issue_type' => 'content',
                'reason' => 'Please include measurable outcomes.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('logbooks', [
            'id' => $logbook->id,
            'status' => 'rejected',
            'supervisor_remarks' => 'Please include measurable outcomes.',
        ]);

        $this->actingAs($supervisor)
            ->get(route('supervisor.logbooks.history'))
            ->assertOk()
            ->assertSee('Please include measurable outcomes.');
    }

    public function test_supervisor_can_verify_total_hours_without_daily_attendance_entries(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student = User::factory()->create(['role' => 'student', 'supervisor_id' => $supervisor->id]);
        $this->createLogbook($student, [
            'rendered_minutes' => 2400,
            'attendance_entries' => null,
        ]);

        $this->actingAs($supervisor)
            ->get(route('supervisor.logbooks.index'))
            ->assertOk()
            ->assertSee('Student declared: 40.00 hrs')
            ->assertSee('No daily attendance breakdown was recorded.')
            ->assertSee('Verified hours');
    }

    public function test_approved_logbook_cannot_be_edited(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $logbook = $this->createLogbook($student, ['status' => 'approved']);

        $this->actingAs($student)
            ->get(route('student.logbook.edit', $logbook))
            ->assertForbidden();
    }

    public function test_student_can_edit_a_pending_logbook_and_replace_evidence(): void
    {
        Storage::fake('public');

        $student = User::factory()->create(['role' => 'student']);
        $oldPath = UploadedFile::fake()->create('old.pdf', 50, 'application/pdf')
            ->store('logbook-evidence', 'public');
        $logbook = $this->createLogbook($student, [
            'status' => 'pending',
            'evidence_file_path' => $oldPath,
        ]);

        $this->actingAs($student)
            ->get(route('student.logbook.edit', $logbook))
            ->assertOk();

        $this->actingAs($student)
            ->put(route('student.logbook.update', $logbook), [
                'start_date' => '2026-06-02',
                'end_date' => '2026-06-08',
                'objectives' => 'Updated objectives',
                'content' => 'Updated skills and activities',
                'evidence' => UploadedFile::fake()->create(
                    'replacement.pdf',
                    80,
                    'application/pdf'
                ),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('student.logbook.show', $logbook));

        $logbook->refresh();
        $this->assertSame('pending', $logbook->status);
        $this->assertStringContainsString('Updated objectives', $logbook->description);
        $this->assertStringContainsString('Updated skills and activities', $logbook->description);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($logbook->evidence_file_path);
    }

    public function test_student_can_fix_a_rejected_logbook_but_not_another_students_entry(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $logbook = $this->createLogbook($student, [
            'status' => 'rejected',
            'supervisor_remarks' => 'Add more technical detail.',
        ]);

        $this->actingAs($student)
            ->put(route('student.logbook.update', $logbook), [
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-07',
                'objectives' => 'Corrected objectives',
                'content' => 'Expanded technical details',
            ])
            ->assertSessionHasNoErrors();

        $logbook->refresh();
        $this->assertSame('pending', $logbook->status);
        $this->assertNull($logbook->supervisor_remarks);

        $this->actingAs($otherStudent)
            ->get(route('student.logbook.edit', $logbook))
            ->assertNotFound();
    }

    public function test_supervisor_and_academic_mentor_can_view_assigned_logbook(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $mentor = User::factory()->create(['role' => 'mentor']);
        $otherMentor = User::factory()->create(['role' => 'mentor']);
        $student = User::factory()->create([
            'role' => 'student',
            'supervisor_id' => $supervisor->id,
            'mentor_id' => $mentor->id,
        ]);
        $logbook = $this->createLogbook($student, [
            'description' => 'Private assigned-student weekly progress.',
        ]);

        $this->actingAs($supervisor)
            ->get(route('logbooks.show', $logbook))
            ->assertOk()
            ->assertSee('Private assigned-student weekly progress.');

        $this->actingAs($mentor)
            ->get(route('logbooks.show', $logbook))
            ->assertOk()
            ->assertSee('Private assigned-student weekly progress.');

        $this->actingAs($otherMentor)
            ->get(route('logbooks.show', $logbook))
            ->assertForbidden();
    }

    public function test_industrial_supervisor_has_a_real_dashboard_with_recent_logbooks(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student = User::factory()->create([
            'role' => 'student',
            'supervisor_id' => $supervisor->id,
        ]);
        $this->createLogbook($student);

        $this->actingAs($supervisor)
            ->get(route('supervisor.dashboard'))
            ->assertOk()
            ->assertSee('Industrial Supervisor Dashboard')
            ->assertSee('View logbook');
    }

    public function test_weekly_logbook_tracks_mc_and_supervisor_verified_hours(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student = User::factory()->create([
            'role' => 'student',
            'supervisor_id' => $supervisor->id,
        ]);
        Storage::disk('local')->put('supervisors/signatures/test-signature.png', 'signature');
        Storage::disk('local')->put('supervisors/stamps/test-stamp.png', 'stamp');
        $supervisor->profile()->create([
            'company_name' => 'Example Company',
            'signature_path' => 'supervisors/signatures/test-signature.png',
            'stamp_path' => 'supervisors/stamps/test-stamp.png',
        ]);

        $response = $this->actingAs($student)
            ->post(route('student.logbook.store'), [
                'week_number' => 1,
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-05',
                'objectives' => 'Complete weekly tasks',
                'content' => 'Applied technical and communication skills',
                'attendance' => $this->attendanceWithMedicalLeave(true),
            ]);

        $response->assertSessionHasNoErrors();
        $logbook = Logbook::firstOrFail();
        $this->assertSame(1680, $logbook->rendered_minutes);
        $this->assertSame('medical_leave', $logbook->attendance_entries[1]['status']);
        $this->assertArrayNotHasKey('tasks', $logbook->attendance_entries[0]);
        Storage::disk('local')->assertExists(
            $logbook->attendance_entries[1]['mc_evidence_path']
        );

        $this->actingAs($supervisor)
            ->patch(route('supervisor.logbooks.approve', $logbook), [
                'verified_hours' => 27,
                'attendance_remarks' => 'One hour was not supported by the submitted record.',
            ])
            ->assertSessionHasNoErrors();

        $logbook->refresh();
        $this->assertSame('approved', $logbook->status);
        $this->assertSame(1620, $logbook->verified_minutes);
        $this->assertSame(
            'One hour was not supported by the submitted record.',
            $logbook->attendance_remarks
        );
        $this->assertSame($supervisor->id, $logbook->approved_by_id);
        $this->assertNotNull($logbook->approved_at);
        $this->assertSame('Example Company', $logbook->approval_company_name);

        $this->actingAs($student)
            ->get(route('logbooks.approval-asset', [$logbook, 'signature']))
            ->assertOk();

        $outsider = User::factory()->create(['role' => 'student']);
        $this->actingAs($outsider)
            ->get(route('logbooks.approval-asset', [$logbook, 'stamp']))
            ->assertForbidden();
    }

    public function test_supervisor_must_upload_signature_and_stamp_before_approval(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student = User::factory()->create([
            'role' => 'student',
            'supervisor_id' => $supervisor->id,
        ]);
        $logbook = $this->createLogbook($student);

        $this->actingAs($supervisor)
            ->patch(route('supervisor.logbooks.approve', $logbook))
            ->assertSessionHas('error');

        $this->assertSame('pending', $logbook->refresh()->status);
        $this->assertNull($logbook->approved_at);
    }

    public function test_mc_day_requires_a_medical_certificate(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post(route('student.logbook.store'), [
                'week_number' => 1,
                'start_date' => '2026-06-01',
                'end_date' => '2026-06-05',
                'objectives' => 'Complete weekly tasks',
                'content' => 'Applied technical skills',
                'attendance' => $this->attendanceWithMedicalLeave(),
            ])
            ->assertSessionHasErrors('attendance.1.mc_evidence');

        $this->assertDatabaseCount('logbooks', 0);
    }

    public function test_attendance_dates_must_match_the_selected_monday_to_friday_week(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post(route('student.logbook.store'), [
                'week_number' => 1,
                'start_date' => '2026-06-02',
                'end_date' => '2026-06-06',
                'objectives' => 'Complete weekly tasks',
                'content' => 'Applied technical skills',
                'attendance' => $this->attendanceWithMedicalLeave(true),
            ])
            ->assertSessionHasErrors(['start_date', 'end_date']);

        $this->assertDatabaseCount('logbooks', 0);
    }

    public function test_attendance_rejection_appears_on_assigned_academic_mentor_dashboard(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $otherMentor = User::factory()->create(['role' => 'mentor']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student = User::factory()->create([
            'role' => 'student',
            'mentor_id' => $mentor->id,
            'supervisor_id' => $supervisor->id,
        ]);
        $logbook = $this->createLogbook($student);

        $this->actingAs($supervisor)
            ->patch(route('supervisor.logbooks.reject', $logbook), [
                'issue_type' => 'attendance',
                'reason' => 'Student was absent on Thursday without approval.',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($mentor)
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertSee('Attendance intervention required')
            ->assertSee('Student was absent on Thursday without approval.');

        $this->actingAs($otherMentor)
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertDontSee('Student was absent on Thursday without approval.');
    }

    public function test_mentor_only_sees_clearances_for_assigned_students(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $otherMentor = User::factory()->create(['role' => 'mentor']);
        $assignedStudent = User::factory()->create([
            'role' => 'student',
            'mentor_id' => $mentor->id,
        ]);
        $otherStudent = User::factory()->create([
            'role' => 'student',
            'mentor_id' => $otherMentor->id,
        ]);

        $assigned = $this->createClearance($assignedStudent, 'Assigned Company');
        $other = $this->createClearance($otherStudent, 'Private Company');

        $this->actingAs($mentor)
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertSee('Assigned Company')
            ->assertDontSee('Private Company');

        $this->actingAs($mentor)
            ->get(route('mentor.clearances.show', $other))
            ->assertForbidden();

        $this->actingAs($mentor)
            ->get(route('mentor.clearances.show', $assigned))
            ->assertOk();
    }

    public function test_role_profiles_and_cover_letter_pdf_are_functional(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($mentor)->get(route('mentor.profile.edit'))->assertOk();
        $this->actingAs($supervisor)->get(route('supervisor.profile.edit'))->assertOk();

        CoverLetter::create([
            'user_id' => $student->id,
            'company_name' => 'Example Company',
            'hiring_manager' => 'Hiring Manager',
            'role' => 'Software Intern',
            'body_text' => 'I am applying for the internship role.',
        ]);
        $student->profile()->create([
            'personal_email' => $student->email,
            'contact_number' => '+60 12-345 6789',
        ]);
        $student->skills()->create([
            'name' => 'Laravel',
            'proficiency' => 'Advanced',
        ]);

        $this->actingAs($student)
            ->get(route('student.cover-letter.download'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $word = $this->actingAs($student)
            ->get(route('student.cover-letter.download-doc'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/msword')
            ->assertDownload();

        $this->assertStringStartsWith('{\\rtf1', $word->getContent());
        $this->assertStringNotContainsString('<html', $word->getContent());
    }

    private function createLogbook(User $student, array $overrides = []): Logbook
    {
        return Logbook::create(array_merge([
            'user_id' => $student->id,
            'week_number' => 1,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-07',
            'description' => 'Weekly objectives and skills.',
            'status' => 'pending',
        ], $overrides));
    }

    private function createClearance(User $student, string $company): PlacementClearance
    {
        return PlacementClearance::create([
            'student_id' => $student->id,
            'mentor_id' => $student->mentor_id,
            'company_name' => $company,
            'office_address' => '1 Example Street',
            'supervisor_name' => 'Industry Supervisor',
            'supervisor_email' => 'work-'.uniqid().'@example.com',
            'supervisor_personal_email' => 'personal-'.uniqid().'@example.com',
            'job_offer_path' => 'clearances/job-offer.pdf',
            'indemnity_path' => 'clearances/indemnity.pdf',
            'placement_agreement_path' => 'clearances/agreement.pdf',
            'status' => 'pending',
        ]);
    }

    private function attendanceWithMedicalLeave(bool $withCertificate = false): array
    {
        return [
            [
                'date' => '2026-06-01',
                'status' => 'present',
                'rendered_hours' => 7,
            ],
            [
                'date' => '2026-06-02',
                'status' => 'medical_leave',
                'note' => 'Medical leave certified by clinic.',
                'mc_evidence' => $withCertificate
                    ? UploadedFile::fake()->create(
                        'tuesday-medical-certificate.pdf',
                        100,
                        'application/pdf'
                    )
                    : null,
            ],
            [
                'date' => '2026-06-03',
                'status' => 'present',
                'rendered_hours' => 7,
            ],
            [
                'date' => '2026-06-04',
                'status' => 'present',
                'rendered_hours' => 7,
            ],
            [
                'date' => '2026-06-05',
                'status' => 'present',
                'rendered_hours' => 7,
            ],
        ];
    }

    public function test_render_forwarded_https_generates_secure_asset_and_login_urls(): void
    {
        $this->withHeaders([
            'Host' => 'wims-sus.onrender.com',
            'X-Forwarded-Host' => 'wims-sus.onrender.com',
            'X-Forwarded-Port' => '443',
            'X-Forwarded-Proto' => 'https',
        ])->get('/')
            ->assertOk()
            ->assertSee('https://wims-sus.onrender.com/build/assets/', false)
            ->assertSee('https://wims-sus.onrender.com/login', false)
            ->assertDontSee('http://wims-sus.onrender.com/build/assets/', false);
    }
}
