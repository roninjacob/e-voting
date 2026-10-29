<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationEmailOtp extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.evoting.from_address'), config('mail.from.name')),
            subject: 'Verify your E-VOTING email',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-email-otp',
            text: 'emails.registration-email-otp-text',
            with: ['code' => $this->code],
        );
    }
}
