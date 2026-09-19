<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\SecurityEvent;
use App\Models\User;

final class SuspendOrganizationAction
{
    /**
     * Administratively suspend an organization tenant.
     */
    public function execute(Organization $organization, string $reason, User $admin): Organization
    {
        $oldStatus = $organization->status;
        $organization->status = 'suspended';
        $organization->save();

        // Immutable Audit Trail
        AuditLog::create([
            'actor_id' => $admin->id,
            'actor_name' => $admin->name,
            'subject_type' => Organization::class,
            'subject_id' => $organization->id,
            'event' => 'organization_suspended',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'suspended', 'reason' => $reason],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        // SIEM Security Event
        SecurityEvent::record(
            eventType: 'org_suspended',
            severity: 'warning',
            actor: $admin,
            details: [
                'organization_id' => $organization->id,
                'organization_name' => $organization->name,
                'reason' => $reason,
            ]
        );

        return $organization;
    }
}
