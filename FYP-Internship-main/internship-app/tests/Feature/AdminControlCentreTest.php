<?php

namespace Tests\Feature;

use App\Models\PlacementClearance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControlCentreTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_displays_system_statistics_and_standard_dashboard_redirects(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'student', 'mentor_id' => null]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin Control Centre')
            ->assertSee('Unassigned students')
            ->assertSee('1');
    }

    public function test_non_admin_users_cannot_access_admin_control_centre(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('admin.clearances.export'))
            ->assertForbidden();
    }

    public function test_admin_can_assign_and_remove_an_academic_mentor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $mentor = User::factory()->create(['role' => 'mentor']);

        $this->actingAs($admin)
            ->patch(route('admin.users.assign-mentor', $student), ['mentor_id' => $mentor->id])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'mentor_id' => $mentor->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.assign-mentor', $student), ['mentor_id' => ''])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'mentor_id' => null,
        ]);
    }

    public function test_assignment_rejects_a_non_mentor_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.assign-mentor', $student), ['mentor_id' => $otherStudent->id])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors('mentor_id');

        $this->assertNull($student->fresh()->mentor_id);
    }

    public function test_admin_can_update_a_user_without_replacing_their_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'name' => 'Original Name',
            'email' => 'original@example.test',
        ]);
        $originalPassword = $student->password;

        $this->actingAs($admin)
            ->put(route('admin.users.update', $student), [
                'name' => 'Updated Name',
                'email' => 'updated@example.test',
                'role' => 'student',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.users.index'));

        $student->refresh();

        $this->assertSame('Updated Name', $student->name);
        $this->assertSame('updated@example.test', $student->email);
        $this->assertSame($originalPassword, $student->password);
    }

    public function test_admin_can_view_and_export_consolidated_cohort_progress(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mentor = User::factory()->create(['role' => 'mentor']);
        $student = User::factory()->create([
            'role' => 'student',
            'name' => 'Cohort Student',
            'mentor_id' => $mentor->id,
        ]);

        PlacementClearance::create([
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'company_name' => 'Example Industries',
            'office_address' => 'Kuala Lumpur',
            'supervisor_name' => 'Industry Reviewer',
            'supervisor_email' => 'reviewer@example.test',
            'supervisor_personal_email' => 'reviewer.personal@example.test',
            'job_offer_path' => 'placements/offer.pdf',
            'indemnity_path' => 'placements/indemnity.pdf',
            'placement_agreement_path' => 'placements/agreement.pdf',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.clearances.index'))
            ->assertOk()
            ->assertSee('Admin Clearance & Supervisor Accounts', false)
            ->assertSee('Cohort Student')
            ->assertSee('Example Industries');

        $this->actingAs($admin)
            ->get(route('admin.clearances.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertDownload();
    }
}
