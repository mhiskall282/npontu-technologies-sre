<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Subscription — Active Billing & Plan Association for an Organization Tenant.
 *
 * @property int $id
 * @property int $organization_id
 * @property int $plan_id
 * @property string $status ('active' | 'trialing' | 'past_due' | 'canceled' | 'paused')
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $current_period_start
 * @property Carbon|null $current_period_end
 * @property Carbon|null $canceled_at
 * @property string $billing_provider ('ready' | 'stripe' | 'invoice' | 'manual')
 * @property string|null $billing_account_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Subscription extends Model
{
    use HasFactory;

    protected $table = 'saas_subscriptions';

    protected $fillable = [
        'organization_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'canceled_at',
        'billing_provider',
        'billing_account_id',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    /**
     * The tenant organization owning this subscription.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * The commercial plan associated with this subscription.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * Determine if subscription is currently valid and active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing'], true) &&
            ($this->canceled_at === null || $this->current_period_end?->isFuture());
    }

    /**
     * Determine if subscription is in an active trial period.
     */
    public function onTrial(): bool
    {
        return $this->status === 'trialing' &&
            $this->trial_ends_at !== null &&
            $this->trial_ends_at->isFuture();
    }

    /**
     * Scope query to only active subscriptions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'trialing']);
    }

    /**
     * Scope query to trialing subscriptions.
     */
    public function scopeTrialing(Builder $query): Builder
    {
        return $query->where('status', 'trialing')
            ->where('trial_ends_at', '>', now());
    }
}
