<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\MessageMentionMail;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use App\Services\EmailReplyTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * EmailReplyController — Handles Inbound Email & 1-Click Web Replies to SRE Channels
 *
 * Implements:
 *   - 1-Click Fast Web Reply Screen from Email receipts
 *   - Inbound Email Webhook processor for direct mail replies
 *   - PDF and Image attachment ingestion converted to Base64 blobs
 */
class EmailReplyController extends Controller
{
    /**
     * Show the 1-Click Instant Email Reply composer.
     */
    public function show(string $token): View
    {
        $context = EmailReplyTokenService::validateToken($token);

        if (! $context) {
            abort(403, 'The operational email reply token has expired or is invalid.');
        }

        $user = $context['user'];
        $conversation = $context['conversation'];
        $originalMessage = $context['message_id'] ? Message::find($context['message_id']) : null;

        $recentMessages = Message::where('conversation_id', $conversation->id)
            ->with('sender')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->reverse();

        return view('messages.email_reply', compact(
            'token',
            'user',
            'conversation',
            'originalMessage',
            'recentMessages'
        ));
    }

    /**
     * Store a reply sent via the 1-Click Email Reply form.
     */
    public function store(Request $request, string $token): View|RedirectResponse
    {
        $context = EmailReplyTokenService::validateToken($token);

        if (! $context) {
            abort(403, 'The operational email reply token has expired or is invalid.');
        }

        $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,gif', 'max:10240'],
        ]);

        $body = trim((string) $request->input('body'));
        $file = $request->file('attachment');

        if ($body === '' && ! $file) {
            return back()->withErrors(['body' => 'Please enter reply text or attach a PDF or image.'])->withInput();
        }

        $user = $context['user'];
        $conversation = $context['conversation'];

        $attachmentName = null;
        $attachmentMime = null;
        $attachmentSize = null;
        $attachmentBlob = null;

        if ($file) {
            $attachmentMime = $file->getMimeType();
            $attachmentName = $file->getClientOriginalName();
            $attachmentSize = $file->getSize();
            $attachmentBlob = 'data:'.$attachmentMime.';base64,'.base64_encode(file_get_contents($file->getRealPath()));
        }

        if ($body === '' && $attachmentName) {
            $body = $attachmentMime === 'application/pdf'
                ? "📎 Shared a document: {$attachmentName}"
                : "📷 Shared an image: {$attachmentName}";
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $body,
            'attachment_name' => $attachmentName,
            'attachment_mime' => $attachmentMime,
            'attachment_size' => $attachmentSize,
            'attachment_blob' => $attachmentBlob,
        ]);

        $conversation->touch();

        // Update read status for sender
        ConversationParticipant::updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id],
            ['last_read_at' => now()]
        );

        // Dispatch notifications to channel participants
        $this->notifyParticipants($message, $conversation, $user);

        if (Auth::check()) {
            return redirect()->route('messages.index', ['c' => $conversation->id])
                ->with('success', 'Your reply has been posted to the channel.');
        }

        return view('messages.email_reply_success', [
            'conversation' => $conversation,
            'message' => $message,
            'user' => $user,
        ]);
    }

    /**
     * Inbound Email Webhook API endpoint.
     * Accepts POST requests from email service providers (Sendgrid, Mailgun, Postmark, AWS SES, or custom).
     */
    public function inbound(Request $request): JsonResponse
    {
        // 1. Resolve token from parameters, headers, or recipient address
        $token = $request->input('token')
            ?? $request->header('X-Reply-Token')
            ?? $this->extractTokenFromAddress((string) $request->input('to', ''));

        if (! $token) {
            return response()->json(['error' => 'Missing reply token.'], 422);
        }

        $context = EmailReplyTokenService::validateToken($token);
        if (! $context) {
            return response()->json(['error' => 'Invalid or expired token.'], 403);
        }

        $user = $context['user'];
        $conversation = $context['conversation'];

        // 2. Extract and clean email body
        $rawBody = (string) ($request->input('text') ?? $request->input('stripped-text') ?? $request->input('body') ?? '');
        $cleanedBody = $this->cleanEmailBody($rawBody);

        if (trim($cleanedBody) === '') {
            return response()->json(['error' => 'Empty message body.'], 422);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $cleanedBody,
        ]);

        $conversation->touch();

        ConversationParticipant::updateOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $user->id],
            ['last_read_at' => now()]
        );

        $this->notifyParticipants($message, $conversation, $user);

        return response()->json([
            'success' => true,
            'message_id' => $message->id,
            'conversation_id' => $conversation->id,
            'channel' => $conversation->title ?? 'Direct Chat',
        ]);
    }

    /**
     * Clean incoming email text by stripping quotation blocks and signatures.
     */
    protected function cleanEmailBody(string $text): string
    {
        // Remove lines starting with >
        $lines = explode("\n", $text);
        $cleanLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Stop at common reply headers
            if (preg_match('/^(on .+\bwrote:?|-----original message-----|from:)/i', $trimmed)) {
                break;
            }
            if (str_starts_with($trimmed, '>')) {
                continue;
            }
            $cleanLines[] = $line;
        }

        return trim(implode("\n", $cleanLines));
    }

    /**
     * Extract token from 'reply+{token}@domain.com' format.
     */
    protected function extractTokenFromAddress(string $to): ?string
    {
        if (preg_match('/reply\+([^@]+)@/i', $to, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Dispatch email notifications to participants.
     */
    protected function notifyParticipants(Message $message, Conversation $conversation, User $sender): void
    {
        $participants = $conversation->participants()->where('users.id', '!=', $sender->id)->get();

        foreach ($participants as $recipient) {
            if ($recipient->email) {
                try {
                    Mail::to($recipient->email)->send(new MessageMentionMail(
                        chatMessage: $message,
                        conversation: $conversation,
                        recipient: $recipient,
                        isBroadcast: false,
                        isDirectMessage: $conversation->type === 'direct'
                    ));
                } catch (\Throwable $e) {
                    logger()->warning("Failed to dispatch email reply notification to {$recipient->email}: {$e->getMessage()}");
                }
            }
        }
    }
}
