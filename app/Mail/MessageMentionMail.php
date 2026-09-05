<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
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
        public bool $isBroadcast = false,
        public bool $isDirectMessage = false
    ) {}

    /**
     * Build the message.
     */
    public function build(): self
    {
        $senderName = $this->chatMessage->sender?->name ?? 'SRE Operator';
        $channelTitle = $this->conversation->title ?? 'Direct Message';

        if ($this->isDirectMessage) {
            $prefix = '[SRE Direct]';
            $subject = "{$prefix} New operational message from {$senderName}";
        } elseif ($this->isBroadcast) {
            $prefix = '[SRE Broadcast]';
            $subject = "{$prefix} {$senderName} announced to all in #{$channelTitle}";
        } else {
            $prefix = '[Comms Alert]';
            $subject = "{$prefix} {$senderName} mentioned you in {$channelTitle}";
        }

        return $this->subject($subject)
            ->view('emails.message_mention');
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
