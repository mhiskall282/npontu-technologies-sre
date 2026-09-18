<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Workspace — Operational Isolation Boundary Entity.
 *
 * @property int $id
 * @property string $uuid
 * @property int|null $organization_id
 * @property int|null $owner_user_id
 * @property string $name
 * @property string $slug
 * @property string|null $subdomain
 * @property string|null $custom_domain
 * @property bool $is_personal
 * @property string $status ('active' | 'suspended' | 'archived')
 * @property int $retention_days
 * @property array|null $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Workspace extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'organization_id',
        'owner_user_id',
        'name',
        'slug',
        'subdomain',
        'custom_domain',
        'is_personal',
        'status',
        'retention_days',
        'settings',
    ];

    /**
     * Attribute type casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_personal' => 'boolean',
            'retention_days' => 'integer',
            'settings' => 'array',
        ];
    }

    /**
     * Bootstrap the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Workspace $workspace) {
            if (empty($workspace->uuid)) {
                $workspace->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * The parent organization (if an organization workspace).
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The owner user (especially for personal workspaces).
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Memberships in this workspace.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(WorkspaceMembership::class);
    }

    /**
     * Users who are members of this workspace.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_memberships')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    /**
     * Operational activities scoped to this workspace.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Operational activity logs scoped to this workspace.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Operational shift handovers scoped to this workspace.
     */
    public function shiftHandovers(): HasMany
    {
        return $this->hasMany(ShiftHandover::class);
    }

    /**
     * Operational conversations and war rooms scoped to this workspace.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Operational notifications scoped to this workspace.
     */
    public function operationalNotifications(): HasMany
    {
        return $this->hasMany(OperationalNotification::class);
    }

    /**
     * Security audit logs scoped to this workspace.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check if workspace is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if workspace is a personal workspace.
     */
    public function isPersonal(): bool
    {
        return $this->is_personal;
    }

    /**
     * Check if a specific user has active membership in this workspace.
     */
    public function hasUser(User $user): bool
    {
        return $this->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }
}
