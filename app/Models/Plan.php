<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Plan — Commercial SaaS Tier & Entitlements Specification.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $tier ('free' | 'team' | 'enterprise' | 'customer_hosted')
 * @property int $price_cents
 * @property string $billing_interval ('monthly' | 'annual' | 'perpetual')
 * @property int $trial_days
 * @property array|null $features
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Plan extends Model
{
    use HasFactory;

    protected $table = 'saas_plans';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'tier',
        'price_cents',
        'billing_interval',
        'trial_days',
        'features',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'trial_days' => 'integer',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Subscriptions attached to this commercial plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    /**
     * Check if this plan includes a given feature entitlement.
     */
    public function hasFeature(string $feature): bool
    {
        return ! empty($this->features[$feature]);
    }

    /**
     * Retrieve a quantitative entitlement limit (e.g. max_workspaces, max_users).
     */
    public function getLimit(string $limitKey, int $default = 0): int
    {
        if (isset($this->features[$limitKey])) {
            return (int) $this->features[$limitKey];
        }

        return $default;
    }

    /**
     * Human-friendly formatted price string.
     */
    public function priceFormatted(): string
    {
        if ($this->price_cents === 0) {
            return 'Free';
        }

        $dollars = number_format($this->price_cents / 100, 2);

        return "\${$dollars}/{$this->billing_interval}";
    }
}
