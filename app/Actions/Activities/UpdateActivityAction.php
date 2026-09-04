<?php

declare(strict_types=1);

namespace App\Actions\Activities;

use App\Models\Activity;
use App\Services\AuditService;

/**
 * UpdateActivityAction — Domain Action to Modify Operational Check Definitions
 *
 * RESPONSIBILITIES:
 * 1. Captures pre-mutation attribute values for differential auditing.
 * 2. Updates the Activity model with validated inputs.
 * 3. Records an immutable AuditLog entry with before/after value diffs for compliance.
 */
final class UpdateActivityAction
{
    /**
     * Inject AuditService compliance dependency.
     *
     * @param  AuditService  $auditService  Service managing write-only audit trail
     */
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Execute the update pipeline on an activity definition.
     *
     * @param  Activity  $activity  Existing Activity model instance to update
     * @param  array  $validated  Validated update attributes
     * @return Activity Updated Activity model instance
     */
    public function execute(Activity $activity, array $validated): Activity
    {
        // Snapshot old values for only the keys being modified to create a precise diff
        $oldValues = $activity->only(array_keys($validated));

        $activity->update($validated);

        // Record compliance audit log with before/after differential
        $this->auditService->log(
            subject: $activity,
            event: 'updated',
            oldValues: $oldValues,
            newValues: $validated,
        );

        return $activity;
    }
}
