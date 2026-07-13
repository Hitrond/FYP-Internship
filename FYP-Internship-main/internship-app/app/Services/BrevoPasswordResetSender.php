<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class BrevoPasswordResetSender
{
    public function send(User $user, string $token): void
    {
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ]);

        $name = e($user->name);
        $safeUrl = e($resetUrl);
        $minutes = (int) config('auth.passwords.users.expire', 60);

        Http::withHeaders([
            'api-key' => config('services.brevo.key'),
            'accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => config('mail.from.name'),
                'email' => config('mail.from.address'),
            ],
            'to' => [[
                'name' => $user->name,
                'email' => $user->getEmailForPasswordReset(),
            ]],
            'subject' => 'Reset your InternTrack password',
            'htmlContent' => <<<HTML
                <div style="font-family:Arial,sans-serif;line-height:1.6;color:#1e293b;max-width:600px;margin:auto">
                    <h2 style="color:#17233f">Reset your password</h2>
                    <p>Hello {$name},</p>
                    <p>We received a request to reset the password for your InternTrack account.</p>
                    <p><a href="{$safeUrl}" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:12px 20px;border-radius:8px;font-weight:bold">Reset password</a></p>
                    <p>This link expires in {$minutes} minutes. If you did not request a password reset, you can ignore this email.</p>
                    <p>InternTrack</p>
                </div>
                HTML,
        ])->throw();
    }
}
