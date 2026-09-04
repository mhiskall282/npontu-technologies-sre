<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Activity — Operational Check Definition Model
 *
 * Represents a defined recurring support check (e.g., "Daily SMS count vs logs", "Database backup verification").
 * Activities act as operational templates; individual daily status transitions are recorded as append-only
 * events in the related ActivityLog model.
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $category
 * @property string $recurrence
 * @property bool $is_active
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Activity extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'category',
        'recurrence',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    // ──────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────

    /**
     * User who created this operational check definition.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Historical append-only status log events for this check.
     *
     * @return HasMany<ActivityLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Security audit logs capturing definition changes for this activity.
     *
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'subject_id')
            ->where('subject_type', self::class);
    }

    // ──────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────

    /**
     * Scope query to only include active checks.
     *
     * @param  Builder<Activity>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope query to only include daily recurring checks.
     *
     * @param  Builder<Activity>  $query
     */
    public function scopeDaily(Builder $query): void
    {
        $query->where('recurrence', 'daily');
    }

    // ──────────────────────────────────────────
    // Derived State Helpers
    // ──────────────────────────────────────────

    /**
     * Get the latest status event log entry for a given calendar date.
     *
     * Uses indexed equality check `where('date', $date)` to leverage composite index on (date, activity_id).
     *
     * @param  string  $date  Calendar date (Y-m-d)
     * @return ActivityLog|null The most recent log row or null if none recorded yet
     */
    public function latestLogForDate(string $date): ?ActivityLog
    {
        return $this->logs()
            ->where('date', $date)
            ->latest('id')
            ->first();
    }

    /**
     * Derive the effective operational status ('pending' or 'done') for a target date.
     *
     * @param  string  $date  Calendar date (Y-m-d)
     * @return string Current status ('pending' by default if no log exists)
     */
    public function currentStatusForDate(string $date): string
    {
        return $this->latestLogForDate($date)?->status ?? 'pending';
    }
}
