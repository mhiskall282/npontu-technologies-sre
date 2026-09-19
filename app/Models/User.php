<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlatformRole;
use App\Notifications\QueuedResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

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
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

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
        // Operations
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
        'execute_runbooks' => [
            'label' => 'Execute SRE Runbooks',
            'description' => 'Trigger automated remediation runbooks, failovers, and recovery jobs',
            'category' => 'Operations',
        ],

        // Shift Management
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

        // Incident Response
        'escalate_incidents' => [
            'label' => 'Flag Incidents & Escalations',
            'description' => 'Escalate operational checks and attach incident tracking tickets',
            'category' => 'Incident Response',
        ],
        'resolve_incidents' => [
            'label' => 'Resolve Incidents & Post-Mortem',
            'description' => 'Formally declare incidents resolved and publish post-mortem Root Cause Analyses',
            'category' => 'Incident Response',
        ],

        // Multi-Tenancy & Workspaces
        'manage_workspaces' => [
            'label' => 'Manage Workspaces',
            'description' => 'Provision, configure, switch, and archive operational team workspaces',
            'category' => 'Multi-Tenancy',
        ],

        // Commercial Billing & Subscriptions
        'manage_billing' => [
            'label' => 'Subscription & Billing Access',
            'description' => 'View commercial invoices, plan tiers, payment receipts, and subscription quotas',
            'category' => 'Commercial',
        ],

        // Security & SIEM Telemetry
        'view_audit_logs' => [
            'label' => 'View Security Audit Trails',
            'description' => 'Inspect immutable security audit logs and state mutation diffs',
            'category' => 'Security & Compliance',
        ],
        'manage_security' => [
            'label' => 'SIEM & Security Telemetry',
            'description' => 'Inspect security events, analyze threat telemetry, and revoke compromised tokens',
            'category' => 'Security & Compliance',
        ],

        // Platform & Entitlements
        'manage_feature_flags' => [
            'label' => 'Feature Flags & Entitlements',
            'description' => 'Inspect and toggle tenant-level dynamic feature flags and release toggles',
            'category' => 'Platform Governance',
        ],
        'manage_users' => [
            'label' => 'User Administration',
            'description' => 'Provision accounts, configure granular privileges, and trigger password resets',
            'category' => 'Platform Governance',
        ],

        // Integrations & Webhooks
        'manage_integrations' => [
            'label' => 'Webhooks & API Integrations',
            'description' => 'Configure outbound SIEM webhooks, third-party relays, and API access tokens',
            'category' => 'Integrations',
        ],

        // Communications
        'create_channels' => [
            'label' => 'Create Chat Channels',
            'description' => 'Create group communication channels and incident response chat rooms',
            'category' => 'Communications',
        ],
        'broadcast_announcements' => [
            'label' => 'Broadcast Emergency Announcements',
            'description' => 'Send high-priority operational broadcasts to all active SRE engineers',
            'category' => 'Communications',
        ],

        // Compliance & Governance
        'purge_audit_records' => [
            'label' => 'Compliance Archival & Data Purge',
            'description' => 'Request or execute compliance-driven archival and soft-deleted record purges',
            'category' => 'Compliance',
        ],

        // Reporting
        'export_reports' => [
            'label' => 'Reporting & Data Export',
            'description' => 'Access system reporting screens and export operational CSV/print reports',
            'category' => 'Reporting',
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
        'platform_role',
        'grade',
        'department',
        'privileges',
        'designation',
        'phone',
        'suspended_at',
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
            'suspended_at' => 'datetime',
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
     * Check if user account is administratively suspended.
     */
    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    /**
     * Check if user possesses any platform-level administrative role.
     */
    public function isPlatformAdmin(): bool
    {
        return ! empty($this->platform_role) && ! $this->isSuspended();
    }

    /**
     * Check if user possesses root Super Administrator privileges.
     */
    public function isSuperAdmin(): bool
    {
        return $this->platform_role === PlatformRole::SuperAdmin->value && ! $this->isSuspended();
    }

    /**
     * Resolve the user's platform role enum instance.
     */
    public function platformRoleEnum(): ?PlatformRole
    {
        if (empty($this->platform_role)) {
            return null;
        }

        return PlatformRole::tryFrom($this->platform_role);
    }

    /**
     * Check whether user holds a specific platform-level permission.
     */
    public function hasPlatformPermission(string $permission): bool
    {
        if ($this->isSuspended()) {
            return false;
        }

        $enum = $this->platformRoleEnum();
        if ($enum === null) {
            return false;
        }

        return $enum->hasPermission($permission);
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
                'execute_runbooks',
                'sign_handovers',
                'accept_handovers',
                'escalate_incidents',
                'resolve_incidents',
                'export_reports',
                'view_audit_logs',
                'create_channels',
                'broadcast_announcements',
                'manage_workspaces',
            ], true);
        }

        // Standard Support Operator (Agent) default baseline
        return in_array($privilege, [
            'escalate_incidents',
            'create_channels',
            'execute_runbooks',
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
     * Check if user has permission to execute automated SRE runbooks.
     */
    public function canExecuteRunbooks(): bool
    {
        return $this->hasPrivilege('execute_runbooks');
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

    /**
     * Check if user has permission to flag operational incidents.
     */
    public function canEscalateIncidents(): bool
    {
        return $this->hasPrivilege('escalate_incidents');
    }

    /**
     * Check if user has permission to formally resolve incidents and publish post-mortems.
     */
    public function canResolveIncidents(): bool
    {
        return $this->hasPrivilege('resolve_incidents');
    }

    /**
     * Check if user has permission to manage workspaces and tenant boundaries.
     */
    public function canManageWorkspaces(): bool
    {
        return $this->hasPrivilege('manage_workspaces');
    }

    /**
     * Check if user has permission to manage commercial billing and subscriptions.
     */
    public function canManageBilling(): bool
    {
        return $this->hasPrivilege('manage_billing');
    }

    /**
     * Check if user has permission to inspect security events and audit logs.
     */
    public function canViewAuditLogs(): bool
    {
        return $this->hasPrivilege('view_audit_logs');
    }

    /**
     * Check if user has permission to manage SIEM security telemetry.
     */
    public function canManageSecurity(): bool
    {
        return $this->hasPrivilege('manage_security');
    }

    /**
     * Check if user has permission to manage dynamic feature flags.
     */
    public function canManageFeatureFlags(): bool
    {
        return $this->hasPrivilege('manage_feature_flags');
    }

    /**
     * Check if user has permission to administer user accounts.
     */
    public function canManageUsers(): bool
    {
        return $this->hasPrivilege('manage_users');
    }

    /**
     * Check if user has permission to manage external webhooks and integrations.
     */
    public function canManageIntegrations(): bool
    {
        return $this->hasPrivilege('manage_integrations');
    }

    /**
     * Check if user has permission to create chat channels and war rooms.
     */
    public function canCreateChannels(): bool
    {
        return $this->hasPrivilege('create_channels');
    }

    /**
     * Check if user has permission to broadcast urgent announcements.
     */
    public function canBroadcastAnnouncements(): bool
    {
        return $this->hasPrivilege('broadcast_announcements');
    }

    /**
     * Check if user has permission to request compliance data archival and record purges.
     */
    public function canPurgeRecords(): bool
    {
        return $this->hasPrivilege('purge_audit_records');
    }

    /**
     * Check if user has permission to export operational reports and metrics.
     */
    public function canExportReports(): bool
    {
        return $this->hasPrivilege('export_reports');
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
     * SIEM security telemetry events associated with this operator.
     *
     * @return HasMany<SecurityEvent, $this>
     */
    public function securityEvents(): HasMany
    {
        return $this->hasMany(SecurityEvent::class, 'actor_id');
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
     * In-app operational notifications for this user.
     *
     * @return HasMany<OperationalNotification, $this>
     */
    public function operationalNotifications(): HasMany
    {
        return $this->hasMany(OperationalNotification::class);
    }

    /**
     * Compute total unread operational notifications for this user.
     */
    public function unreadNotificationsCount(): int
    {
        return $this->operationalNotifications()->whereNull('read_at')->count();
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

    // ──────────────────────────────────────────
    // Multi-Tenant SaaS Relationships
    // ──────────────────────────────────────────

    /**
     * Organization memberships held by this user.
     *
     * @return HasMany<OrganizationMembership, $this>
     */
    public function organizationMemberships(): HasMany
    {
        return $this->hasMany(OrganizationMembership::class);
    }

    /**
     * Organizations the user belongs to.
     *
     * @return BelongsToMany<Organization, $this>
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_memberships')
            ->withPivot(['role', 'department', 'grade', 'privileges', 'status'])
            ->withTimestamps();
    }

    /**
     * Workspace memberships held by this user.
     *
     * @return HasMany<WorkspaceMembership, $this>
     */
    public function workspaceMemberships(): HasMany
    {
        return $this->hasMany(WorkspaceMembership::class);
    }

    /**
     * Workspaces the user has access to.
     *
     * @return BelongsToMany<Workspace, $this>
     */
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_memberships')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    /**
     * Get the user's personal workspace, if one exists.
     */
    public function personalWorkspace(): ?Workspace
    {
        return Workspace::where('owner_user_id', $this->id)
            ->where('is_personal', true)
            ->first();
    }

    /**
     * Resolve the current or default active workspace for this user.
     */
    public function currentWorkspace(): ?Workspace
    {
        if (session()->has('opsora_workspace_id')) {
            $ws = $this->workspaces()->where('workspaces.id', session('opsora_workspace_id'))->first();
            if ($ws) {
                return $ws;
            }
        }

        return $this->workspaces()->where('workspaces.status', 'active')->first();
    }
}
