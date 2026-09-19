<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Models\AuditLog;
use App\Models\FeatureFlag;
use App\Models\User;

final class ToggleFeatureFlagAction
{
    /**
     * Administratively toggle or reconfigure a platform feature flag.
     *
     * @param  list<string>|null  $targetTiers
     * @param  list<int>|null  $targetOrgIds
     */
    public function execute(
        FeatureFlag $flag,
        bool $enabled,
        User $admin,
        ?array $targetTiers = null,
        ?array $targetOrgIds = null
    ): FeatureFlag {
        $oldValues = [
            'is_enabled' => $flag->is_enabled,
            'target_tiers' => $flag->target_tiers,
            'target_org_ids' => $flag->target_org_ids,
        ];

        $flag->is_enabled = $enabled;
        if ($targetTiers !== null) {
            $flag->target_tiers = $targetTiers;
        }
        if ($targetOrgIds !== null) {
            $flag->target_org_ids = $targetOrgIds;
        }
        $flag->save();

        AuditLog::create([
            'actor_id' => $admin->id,
            'actor_name' => $admin->name,
            'subject_type' => FeatureFlag::class,
            'subject_id' => $flag->id,
            'event' => 'feature_flag_toggled',
            'old_values' => $oldValues,
            'new_values' => [
                'is_enabled' => $flag->is_enabled,
                'target_tiers' => $flag->target_tiers,
                'target_org_ids' => $flag->target_org_ids,
                'key' => $flag->key,
            ],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return $flag;
    }
}
