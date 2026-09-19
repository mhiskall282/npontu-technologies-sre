<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

final class UpdateSubscriptionAction
{
    /**
     * Administratively update or attach a commercial plan subscription to an organization.
     */
    public function execute(Organization $organization, Plan $plan, string $status, User $admin): Subscription
    {
        $oldTier = $organization->tier;
        $organization->tier = $plan->tier;
        $organization->save();

        $subscription = $organization->subscriptions()->latest()->first();

        if ($subscription) {
            $oldPlanId = $subscription->plan_id;
            $oldStatus = $subscription->status;

            $subscription->update([
                'plan_id' => $plan->id,
                'status' => $status,
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);
        } else {
            $oldPlanId = null;
            $oldStatus = null;

            $subscription = Subscription::create([
                'organization_id' => $organization->id,
                'plan_id' => $plan->id,
                'status' => $status,
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
                'billing_provider' => 'ready',
            ]);
        }

        AuditLog::create([
            'actor_id' => $admin->id,
            'actor_name' => $admin->name,
            'subject_type' => Subscription::class,
            'subject_id' => $subscription->id,
            'event' => 'subscription_updated',
            'old_values' => [
                'tier' => $oldTier,
                'plan_id' => $oldPlanId,
                'status' => $oldStatus,
            ],
            'new_values' => [
                'tier' => $plan->tier,
                'plan_id' => $plan->id,
                'status' => $status,
                'plan_name' => $plan->name,
            ],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return $subscription;
    }
}
