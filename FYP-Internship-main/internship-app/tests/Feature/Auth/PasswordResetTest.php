<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_render_can_send_reset_link_through_brevo_https_api(): void
    {
        config([
            'services.brevo.key' => 'test-api-key',
            'services.brevo.use_api' => true,
            'mail.from.address' => 'verified@example.com',
            'mail.from.name' => 'InternTrack',
        ]);
        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => '<test@brevo>'], 201),
        ]);
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        Http::assertSent(function ($request) use ($user) {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request->hasHeader('api-key', 'test-api-key')
                && $request['to'][0]['email'] === $user->email
                && str_contains($request['htmlContent'], 'reset-password');
        });
    }

    public function test_brevo_failure_returns_a_validation_error_instead_of_server_error(): void
    {
        config([
            'services.brevo.key' => 'invalid-api-key',
            'services.brevo.use_api' => true,
            'mail.from.address' => 'verified@example.com',
        ]);
        Http::fake([
            'api.brevo.com/*' => Http::response(['message' => 'Unauthorized'], 401),
        ]);
        $user = User::factory()->create();

        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect('/forgot-password')
            ->assertSessionHasErrors([
                'email' => 'The password reset email could not be sent. Please try again later or contact the administrator.',
            ]);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            $this->assertTrue(Hash::check('password', $user->fresh()->password));

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])->assertRedirect(route('dashboard', absolute: false));
            $this->assertAuthenticatedAs($user);

            return true;
        });
    }
}
