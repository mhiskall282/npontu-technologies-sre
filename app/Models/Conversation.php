<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Conversation — SRE Operations Communications Channel Model
 *
 * Supports direct 1-on-1 private messaging, team shift channels, and specialized operational groups.
 *
 * @property int $id
 * @property string $type ('direct' | 'team' | 'group')
 * @property string|null $title
 * @property string|null $description
 * @property bool $is_private
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Conversation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'title',
        'description',
        'is_private',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }

    /**
     * User who created this channel.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Participants in this conversation.
     *
     * @return BelongsToMany<User, $this>
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    /**
     * Participant records.
     *
     * @return HasMany<ConversationParticipant, $this>
     */
    public function participantRecords(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /**
     * Messages posted to this conversation.
     *
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * The latest message sent in this conversation.
     *
     * @return HasOne<Message, $this>
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Determine display title for a given viewing user.
     *
     * For 1-on-1 direct conversations, displays the other user's name.
     */
    public function displayTitleFor(User $viewer): string
    {
        if ($this->type === 'direct') {
            $other = $this->participants->firstWhere('id', '!==', $viewer->id);

            return $other ? $other->name : ($this->title ?? 'Direct Message');
        }

        return $this->title ?? 'Team Channel';
    }

    /**
     * Check if conversation is 1-on-1 direct chat.
     */
    public function isDirect(): bool
    {
        return $this->type === 'direct';
    }

    /**
     * Scope to conversations visible to a user.
     *
     * @param  Builder<Conversation>  $query
     */
    public function scopeForUser(Builder $query, int $userId): void
    {
        $query->where(function (Builder $q) use ($userId) {
            // User is an explicit participant
            $q->whereHas('participants', function (Builder $pq) use ($userId) {
                $pq->where('user_id', $userId);
            })
            // Or public team channels
                ->orWhere(function (Builder $tq) {
                    $tq->where('type', 'team')->where('is_private', false);
                });
        });
    }

    /**
     * Compute unread message count for a specific user in this conversation.
     */
    public function unreadCountFor(int $userId): int
    {
        $participant = ConversationParticipant::where('conversation_id', $this->id)
            ->where('user_id', $userId)
            ->first();

        $query = Message::where('conversation_id', $this->id)
            ->where('sender_id', '!=', $userId);

        if ($participant?->last_read_at) {
            $query->where('created_at', '>', $participant->last_read_at);
        }

        return $query->count();
    }
}
