<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * WorkspaceMembership — Relationship linking Users to Workspaces with Workspace-Level Roles.
 *
 * @property int $id
 * @property int $workspace_id
 * @property int $user_id
 * @property string $role ('admin' | 'lead' | 'agent' | 'viewer')
 * @property string $status ('active' | 'suspended')
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class WorkspaceMembership extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'workspace_id',
        'user_id',
        'role',
        'status',
    ];

    /**
     * The workspace.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * The user member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if membership possesses workspace administrator privileges.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if membership possesses workspace team lead privileges.
     */
    public function isLead(): bool
    {
        return in_array($this->role, ['admin', 'lead'], true);
    }

    /**
     * Check if membership is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
