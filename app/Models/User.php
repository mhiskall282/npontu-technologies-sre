<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * User — Support Engineer & Operations Personnel Model
 *
 * Implements authentication, role-based access control (RBAC), and bio metadata tracking.
 *
 * ROLES & PRIVILEGES:
 * - admin: System configuration, full user provisioning, activity definitions, monitoring, and audit logs.
 * - lead: Shift handover oversight, check creation/editing, report export, and supervisor monitoring.
 * - agent (Support Operator): Daily shift board checkoffs, inline status updates, and view history.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role ('admin' | 'lead' | 'agent')
 * @property string|null $designation
 * @property string|null $phone
 * @property Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Mass assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'designation',
        'phone',
    ];

    /**
     * Hidden attributes excluded from serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute type casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ──────────────────────────────────────────
    // Role Helpers (RBAC)
    // ──────────────────────────────────────────

    /**
     * Check if user possesses the Administrator role.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user possesses the Team Lead role.
     */
    public function isLead(): bool
    {
        return $this->role === 'lead';
    }

    /**
     * Check if user possesses the Support Operator (Agent) role.
     */
    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    /**
     * Check if user has supervisor permissions to create, edit, or delete activities.
     *
     * @return bool True if admin or lead
     */
    public function canManageActivities(): bool
    {
        return in_array($this->role, ['admin', 'lead'], strict: true);
    }

    // ──────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────

    /**
     * Operational checks created by this user.
     *
     * @return HasMany<Activity, $this>
     */
    public function createdActivities(): HasMany
    {
        return $this->hasMany(Activity::class, 'created_by');
    }

    /**
     * Shift status log entries updated by this operator.
     *
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'updated_by');
    }

    /**
     * Security compliance audit trail mutations initiated by this user.
     *
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }
}
