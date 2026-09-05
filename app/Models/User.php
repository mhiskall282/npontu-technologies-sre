<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\QueuedResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * User — Support Engineer & Operations Personnel Model
 *
 * Implements authentication, granular RBAC permissions, SRE engineering grade,
 * and operational communications tracking.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role ('admin' | 'lead' | 'agent')
 * @property string $grade ('L1' | 'L2' | 'L3' | 'L4' | 'L5')
 * @property string $department
 * @property array|null $privileges
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
     * SRE Engineering Grades / Tiers.
     */
    public const GRADES = [
        'L1' => 'L1 — Associate Support Operator',
        'L2' => 'L2 — Support Engineer (SRE)',
        'L3' => 'L3 — Senior SRE Specialist',
        'L4' => 'L4 — Team Lead & Shift Supervisor',
        'L5' => 'L5 — Principal Architect & Enterprise Lead',
    ];

    /**
     * Operational Departments / Categories.
     */
    public const DEPARTMENTS = [
        'Core Operations (NOC)',
        'Infrastructure & Cloud',
        'Database & Storage',
        'Payment & SMS Gateways',
        'Security & Compliance',
    ];

    /**
     * Complete Catalog of Granular Permissions / Privileges.
     */
    public const ALL_PRIVILEGES = [
        'manage_activities' => [
            'label' => 'Manage Activities',
            'description' => 'Create, edit, and configure operational activity checks and recurrences',
            'category' => 'Operations',
        ],
        'assign_tasks' => [
            'label' => 'Delegate & Reassign Tasks',
            'description' => 'Delegate checks to team members individually or in bulk batches',
            'category' => 'Operations',
        ],
        'sign_handovers' => [
            'label' => 'Sign Shift Handovers',
            'description' => 'Draft and digitally sign off SRE shift handover briefings',
            'category' => 'Shift Management',
        ],
        'accept_handovers' => [
            'label' => 'Accept & Sign-On Handovers',
            'description' => 'Formally acknowledge and accept shift handovers as incoming lead',
            'category' => 'Shift Management',
        ],
        'escalate_incidents' => [
            'label' => 'Flag Incidents & Escalations',
            'description' => 'Escalate operational checks and attach incident tracking tickets',
            'category' => 'Incident Response',
        ],
        'export_reports' => [
            'label' => 'Reporting & Data Export',
            'description' => 'Access system reporting screens and export operational CSV/print reports',
            'category' => 'Reporting',
        ],
        'manage_users' => [
            'label' => 'User Administration',
            'description' => 'Provision accounts, configure granular privileges, and trigger password resets',
            'category' => 'Administration',
        ],
        'view_audit_logs' => [
            'label' => 'View Security Audit Trails',
            'description' => 'Inspect immutable security audit logs and state mutation diffs',
            'category' => 'Administration',
        ],
        'create_channels' => [
            'label' => 'Create Chat Channels',
            'description' => 'Create group communication channels and incident response chat rooms',
            'category' => 'Communications',
        ],
    ];

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
        'grade',
        'department',
        'privileges',
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
            'privileges' => 'array',
        ];
    }

    // ──────────────────────────────────────────
    // Role & Granular Privilege Helpers
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
     * Check whether user possesses a specific granular privilege.
     *
     * Evaluation Logic:
     *   1. Administrator possesses all privileges unconditionally.
     *   2. If explicit privileges array is defined, returns whether privilege is present.
     *   3. If privileges array is null, falls back to role default baseline.
     *
     * @param  string  $privilege  Privilege key (e.g. 'manage_activities', 'assign_tasks')
     * @return bool True if authorized
     */
    public function hasPrivilege(string $privilege): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->privileges !== null && is_array($this->privileges)) {
            return in_array($privilege, $this->privileges, true);
        }

        // Role default baseline when privileges array is not explicitly configured
        if ($this->isLead()) {
            return in_array($privilege, [
                'manage_activities',
                'assign_tasks',
                'sign_handovers',
                'accept_handovers',
                'escalate_incidents',
                'export_reports',
                'view_audit_logs',
                'create_channels',
            ], true);
        }

        // Standard Support Operator (Agent) default baseline
        return in_array($privilege, [
            'escalate_incidents',
            'create_channels',
        ], true);
    }

    /**
     * Check if user has permission to create, edit, or manage activities.
     */
    public function canManageActivities(): bool
    {
        return $this->hasPrivilege('manage_activities');
    }

    /**
     * Check if user has permission to delegate/reassign tasks.
     */
    public function canAssignTasks(): bool
    {
        return $this->hasPrivilege('assign_tasks');
    }

    /**
     * Check if user has permission to sign off shift handovers.
     */
    public function canSignHandovers(): bool
    {
        return $this->hasPrivilege('sign_handovers');
    }

    /**
     * Check if user has permission to accept incoming shift handovers.
     */
    public function canAcceptHandovers(): bool
    {
        return $this->hasPrivilege('accept_handovers');
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

    /**
     * Operational checks assigned to this user/engineer.
     *
     * @return HasMany<Activity, $this>
     */
    public function assignedActivities(): HasMany
    {
        return $this->hasMany(Activity::class, 'assigned_to');
    }

    /**
     * Operational chat conversations the user is participating in.
     *
     * @return BelongsToMany<Conversation, $this>
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    /**
     * Messages authored by this user.
     *
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Compute total unread operational messages for this user.
     */
    public function unreadMessagesCount(): int
    {
        $participants = ConversationParticipant::where('user_id', $this->id)->get();
        if ($participants->isEmpty()) {
            return 0;
        }

        $total = 0;
        foreach ($participants as $participant) {
            $query = Message::where('conversation_id', $participant->conversation_id)
                ->where('sender_id', '!=', $this->id);

            if ($participant->last_read_at) {
                $query->where('created_at', '>', $participant->last_read_at);
            }

            $total += $query->count();
        }

        return $total;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new QueuedResetPasswordNotification($token));
    }
}
