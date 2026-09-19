<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ShiftHandover;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ShiftHandoverReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public ShiftHandover $handover,
        public User $recipient,
        public ?User $outgoingLead = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $shiftType = strtoupper($this->handover->shift_type ?? 'SHIFT');

        return new Envelope(
            subject: "[Shift Handover Ready] {$shiftType} Handover Pending Dual-Acceptance",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.shift-handover-ready',
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
