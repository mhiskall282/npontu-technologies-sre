<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ActivityIncidentEscalatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Activity $activity,
        public User $recipient,
        public ?User $escalatedBy = null,
        public ?string $severity = 'HIGH'
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $shortDesc = mb_substr($this->activity->description, 0, 45);

        return new Envelope(
            subject: "[SRE INCIDENT ALERT] [{$this->severity}] {$shortDesc}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.activity-escalated',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
