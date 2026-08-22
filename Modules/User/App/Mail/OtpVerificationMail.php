<?php

namespace Modules\User\App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\User\App\Models\PendingRegistration;

class OtpVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        private PendingRegistration $pending
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email - HajurKoAfno',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'user::emails.otp-verification',
            with: [
                'code' => $this->pending->otp_code,
                'expiresAt' => $this->pending->otp_expires_at->toDayDateTimeString(),
                'name' => $this->pending->first_name,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
