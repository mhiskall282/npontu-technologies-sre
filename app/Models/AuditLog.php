<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * AuditLog — Immutable Security & Compliance Audit Trail
 *
 * ARCHITECTURAL DESIGN & SIEM READINESS:
 * This table records all user-initiated mutations across the application (creates, updates,
 * status changes, deletions) with before/after diffs, actor bio snapshots, and IP addresses.
 *
 * IMMUTABILITY GUARANTEE:
 * - Write-once: $timestamps = false with no updated_at column.
 * - Created timestamp is stamped at generation and cannot be modified.
 * - Morphable subject relation (`subject_type`, `subject_id`) allows tracking any entity.
 *
 * @property int $id
 * @property int|null $actor_id
 * @property string $actor_name
 * @property string|null $actor_role
 * @property string|null $actor_ip
 * @property string $subject_type
 * @property int $subject_id
 * @property string $event ('created' | 'updated' | 'status_changed' | 'deleted' | 'password_reset_requested')
 * @property array|null $old_values
 * @property array|null $new_values
 * @property Carbon $created_at
 */
class AuditLog extends Model
{
    /**
     * Mass assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'actor_id',
        'actor_name',
        'actor_role',
        'actor_ip',
        'subject_type',
        'subject_id',
        'event',
        'old_values',
        'new_values',
    ];

    /**
     * Attribute type casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Audit logs are immutable records: disable standard updated_at maintenance.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Model bootstrap hook to assign created_at on initial insertion.
     */
    protected static function booted(): void
    {
        static::creating(function (AuditLog $log) {
            $log->created_at = now();
        });
    }

    /**
     * Polymorphic relation to the model mutated (e.g. Activity, User).
     *
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }

    /**
     * User account associated with the mutation event.
     *
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
