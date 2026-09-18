<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * OrganizationMembership — Relationship linking Users to Organizations with Organization-Level Roles.
 *
 * @property int $id
 * @property int $organization_id
 * @property int $user_id
 * @property string $role ('owner' | 'admin' | 'member' | 'billing_admin' | 'security_officer')
 * @property string|null $department
 * @property string|null $grade
 * @property array|null $privileges
 * @property string $status ('active' | 'suspended' | 'invited')
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OrganizationMembership extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'user_id',
        'role',
        'department',
        'grade',
        'privileges',
        'status',
    ];

    /**
     * Attribute type casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'privileges' => 'array',
        ];
    }

    /**
     * The organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The user member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if membership represents an organization owner.
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Check if membership has administrative control.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin'], true);
    }

    /**
     * Check if membership is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
