<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * ActivityLog — Append-Only Operational Status Event Model
 *
 * CRITICAL DESIGN DECISION:
 * Rows in this table are append-only and immutable. We NEVER perform an SQL UPDATE on this table.
 * Each row represents an individual shift checkoff event. The current status of an activity for
 * a calendar date is derived from the latest row by ID.
 *
 * DENORMALIZED ACTOR SNAPSHOT:
 * Fields (`actor_name`, `actor_role`, `actor_designation`, `actor_ip`) are denormalized snapshots
 * captured server-side at the moment of the update. This guarantees historical non-repudiation
 * even if user profiles or designations change in the future.
 *
 * @property int $id
 * @property int $activity_id
 * @property Carbon $date
 * @property string $status ('pending' | 'done')
 * @property string|null $remark
 * @property int|null $updated_by
 * @property string $actor_name
 * @property string|null $actor_role
 * @property string|null $actor_designation
 * @property string|null $actor_ip
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'activity_id',
        'date',
        'status',
        'remark',
        'updated_by',
        'actor_name',
        'actor_role',
        'actor_designation',
        'actor_ip',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
        ];
    }

    /**
     * Parent activity definition to which this status event belongs.
     *
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * User account that performed the update (nullable to allow audit retention upon user deletion).
     *
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Determine if this log entry represents a completed status.
     *
     * @return bool True if status is 'done'
     */
    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    /**
     * Determine if this log entry represents an uncompleted pending status.
     *
     * @return bool True if status is 'pending'
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
