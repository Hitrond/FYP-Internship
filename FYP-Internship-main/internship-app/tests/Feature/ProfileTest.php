<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_cannot_delete_their_own_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ])
            ->assertForbidden();

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh());
    }

    public function test_student_can_edit_previously_read_only_profile_fields(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->put(route('student.profile.update'), [
            'full_name' => 'Updated Student Name',
            'tp_number' => 'TP012345',
            'course_name' => 'Computer Science',
            'specialization' => 'Data Analytics',
            'intake_code' => 'UC3F2601',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('profiles', [
            'user_id' => $student->id,
            'full_name' => 'Updated Student Name',
            'tp_number' => 'TP012345',
            'course_name' => 'Computer Science',
            'specialization' => 'Data Analytics',
            'intake_code' => 'UC3F2601',
        ]);
    }

    public function test_admin_can_delete_another_users_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertNull($user->fresh());
    }
}
