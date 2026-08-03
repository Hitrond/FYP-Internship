<?php

namespace App\Services;

use App\Mail\SupervisorWelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BrevoTransactionalMailer
{
    public function configured(): bool
    {
        return (bool) config('services.brevo.use_api')
            && filled(config('services.brevo.key'));
    }

    public function sendSupervisorWelcome(User $supervisor, string $rawPassword, string $studentName): void
    {
        $mailable = new SupervisorWelcomeMail($supervisor, $rawPassword, $studentName);
        $html = $mailable->render();

        $response = Http::connectTimeout(5)
            ->timeout(15)
            ->withHeaders([
                'accept' => 'application/json',
                'api-key' => config('services.brevo.key'),
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name' => config('mail.from.name', 'InternTrack'),
                    'email' => config('mail.from.address'),
                ],
                'to' => [[
                    'email' => $supervisor->email,
                    'name' => $supervisor->name,
                ]],
                'replyTo' => [
                    'email' => config('mail.from.address'),
                    'name' => config('mail.from.name', 'InternTrack'),
                ],
                'subject' => 'Welcome to InternTrack - Supervisor Account Details',
                'htmlContent' => $html,
                'textContent' => "Welcome to InternTrack.\n\n"
                    ."You have been registered as the Industrial Supervisor for {$studentName}.\n\n"
                    .'Login URL: '.url('/login')."\n"
                    ."Email: {$supervisor->email}\n"
                    ."Password: {$rawPassword}\n",
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Brevo email failed: '.$response->status().' '.$response->body()
            );
        }
    }
}
