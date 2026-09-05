<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;

/**
 * EmailReplyTokenService — Cryptographic Token Generator for Email-to-Chat Replies
 *
 * Encodes authenticated user and conversation routing into tamper-proof HMAC tokens,
 * allowing engineers to reply to operational comms notifications directly from email
 * or via the 1-click web reply interface.
 */
class EmailReplyTokenService
{
    /**
     * Token validity window (14 days).
     */
    public const TOKEN_TTL_SECONDS = 86400 * 14;

    /**
     * Generate an HMAC signed token for replying via email.
     */
    public static function generateToken(User $user, Conversation $conversation, ?int $messageId = null): string
    {
        $payload = base64_encode((string) json_encode([
            'u' => $user->id,
            'c' => $conversation->id,
            'm' => $messageId,
            't' => time(),
        ]));

        $key = (string) config('app.key');
        $sig = hash_hmac('sha256', $payload, $key);

        return $payload.'.'.$sig;
    }

    /**
     * Validate and decode an email reply token.
     *
     * @return array{user: User, conversation: Conversation, message_id: int|null}|null
     */
    public static function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }

        [$payload, $sig] = $parts;
        $key = (string) config('app.key');
        $expectedSig = hash_hmac('sha256', $payload, $key);

        if (! hash_equals($expectedSig, $sig)) {
            return null;
        }

        $decoded = json_decode(base64_decode($payload, true) ?: '', true);
        if (! is_array($decoded) || ! isset($decoded['u'], $decoded['c'], $decoded['t'])) {
            return null;
        }

        // Validate expiry
        if (time() - (int) $decoded['t'] > self::TOKEN_TTL_SECONDS) {
            return null;
        }

        $user = User::find($decoded['u']);
        $conversation = Conversation::find($decoded['c']);

        if (! $user || ! $conversation) {
            return null;
        }

        // Verify that the user is actually a participant or permitted
        if (! $conversation->participants()->where('users.id', $user->id)->exists() && $conversation->type === 'direct') {
            return null;
        }

        return [
            'user' => $user,
            'conversation' => $conversation,
            'message_id' => isset($decoded['m']) ? (int) $decoded['m'] : null,
        ];
    }
}
