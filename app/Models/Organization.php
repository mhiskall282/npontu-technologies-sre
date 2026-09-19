<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Organization — Top-Level Customer/Enterprise Tenant Entity.
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $slug
 * @property string $company_code
 * @property string $status ('active' | 'pending' | 'suspended' | 'rejected')
 * @property string $tier ('free' | 'team' | 'enterprise' | 'customer_hosted')
 * @property string $deployment_model ('shared_saas' | 'dedicated_managed' | 'customer_funded' | 'customer_hosted')
 * @property string $preferred_region ('af-south' | 'us-east' | 'eu-west')
 * @property array|null $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Organization extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'company_code',
        'status',
        'tier',
        'deployment_model',
        'preferred_region',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted(): void
    {
        static::creating(function (Organization $organization) {
            if (empty($organization->uuid)) {
                $organization->uuid = (string) Str::uuid();
            }

            if (empty($organization->company_code)) {
                $organization->company_code = 'OPS-'.strtoupper(Str::random(6));
            }
        });
    }

    /**
     * Workspaces belonging to this organization.
     */
    public function workspaces(): HasMany
    {
        return $this->hasMany(Workspace::class);
    }

    /**
     * Memberships in this organization.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(OrganizationMembership::class);
    }

    /**
     * Users who are members of this organization.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_memberships')
            ->withPivot(['role', 'department', 'grade', 'privileges', 'status'])
            ->withTimestamps();
    }

    /**
     * Users alias for members relationship.
     */
    public function users(): BelongsToMany
    {
        return $this->members();
    }

    /**
     * Applications associated with this organization.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(OrganizationApplication::class, 'organization_slug', 'slug');
    }

    /**
     * Subscriptions for this organization.
     *
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Active commercial subscription.
     *
     * @return HasOne<Subscription, $this>
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    /**
     * Resolve the active plan for this organization.
     */
    public function activePlan(): ?Plan
    {
        return $this->subscription?->plan;
    }

    /**
     * Check if organization is in active operational status.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if organization is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if a specific user has active membership in this organization.
     */
    public function hasUser(User $user): bool
    {
        return $this->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }
}
