<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupervisorWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $supervisor;
    public $rawPassword;
    public $studentName;

    public function __construct($supervisor, $rawPassword, $studentName)
    {
        $this->supervisor = $supervisor;
        $this->rawPassword = $rawPassword;
        $this->studentName = $studentName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to InternTrack - Supervisor Account Details',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.supervisor-welcome',
        );
    }
}
