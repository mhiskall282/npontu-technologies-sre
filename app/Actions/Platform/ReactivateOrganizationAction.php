<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\SecurityEvent;
use App\Models\User;

final class ReactivateOrganizationAction
{
    /**
     * Administratively reactivate a suspended organization tenant.
     */
    public function execute(Organization $organization, User $admin): Organization
    {
        $oldStatus = $organization->status;
        $organization->status = 'active';
        $organization->save();

        // Immutable Audit Trail
        AuditLog::create([
            'actor_id' => $admin->id,
            'actor_name' => $admin->name,
            'subject_type' => Organization::class,
            'subject_id' => $organization->id,
            'event' => 'organization_reactivated',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'active'],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        // SIEM Security Event
        SecurityEvent::record(
            eventType: 'org_reactivated',
            severity: 'info',
            actor: $admin,
            details: [
                'organization_id' => $organization->id,
                'organization_name' => $organization->name,
            ]
        );

        return $organization;
    }
}
