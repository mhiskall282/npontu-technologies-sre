<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * MessageMentionMail — SRE Comms Notification & Mention Receipt Mail
 *
 * Dispatched when an operator is mentioned (@name) or when an @all broadcast
 * is published in team shift channels or incident war rooms.
 */
class MessageMentionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new mailable instance.
     */
    public function __construct(
        public Message $chatMessage,
        public Conversation $conversation,
        public User $recipient,
        public bool $isBroadcast = false
    ) {}

    /**
     * Get the message envelope.
     */
    public function getEnvelope(): Envelope
    {
        $prefix = $this->isBroadcast ? '[SRE Broadcast]' : '[Comms Alert]';
        $senderName = $this->chatMessage->sender?->name ?? 'SRE Operator';
        $channelTitle = $this->conversation->title ?? 'Direct Message';

        return new Envelope(
            subject: "{$prefix} {$senderName} mentioned you in {$channelTitle}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function getContent(): Content
    {
        return new Content(
            view: 'emails.message_mention',
        );
    }

    /**
     * Attachments.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
