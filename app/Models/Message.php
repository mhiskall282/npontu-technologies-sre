<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Message — Operational Chat Message Event Model
 *
 * @property int $id
 * @property int $conversation_id
 * @property int $sender_id
 * @property string $body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
        'attachment_blob',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function hasAttachment(): bool
    {
        return ! empty($this->attachment_blob);
    }

    public function isImage(): bool
    {
        return ! empty($this->attachment_mime) && str_starts_with($this->attachment_mime, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->attachment_mime === 'application/pdf';
    }

    public function formattedAttachmentSize(): string
    {
        if (! $this->attachment_size) {
            return '0 KB';
        }

        if ($this->attachment_size >= 1048576) {
            return round($this->attachment_size / 1048576, 1).' MB';
        }

        return round($this->attachment_size / 1024, 0).' KB';
    }
}
