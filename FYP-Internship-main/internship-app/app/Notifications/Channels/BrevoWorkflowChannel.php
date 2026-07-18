<?php

namespace App\Notifications\Channels;

use App\Notifications\WorkflowAlertNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class BrevoWorkflowChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notification instanceof WorkflowAlertNotification) {
            return;
        }

        $name = e($notifiable->name);
        $title = e($notification->title);
        $message = e($notification->message);
        $url = e($notification->url);

        Http::withHeaders([
            'api-key' => config('services.brevo.key'),
            'accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => config('mail.from.name'),
                'email' => config('mail.from.address'),
            ],
            'to' => [[
                'name' => $notifiable->name,
                'email' => $notifiable->routeNotificationFor('mail'),
            ]],
            'subject' => $notification->title,
            'htmlContent' => <<<HTML
                <div style="font-family:Arial,sans-serif;line-height:1.6;color:#1e293b;max-width:600px;margin:auto">
                    <h2 style="color:#17233f">{$title}</h2>
                    <p>Hello {$name},</p>
                    <p>{$message}</p>
                    <p><a href="{$url}" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:12px 20px;border-radius:8px;font-weight:bold">Open InternTrack</a></p>
                    <p>This is an automated internship workflow notification.</p>
                </div>
                HTML,
        ])->throw();
    }
}
