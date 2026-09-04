<?php

declare(strict_types=1);

namespace App\Actions\Activities;

use App\Models\Activity;
use App\Services\AuditService;

/**
 * DeleteActivityAction — Domain Action to Retire an Operational Check Definition
 *
 * RESPONSIBILITIES:
 * 1. Writes an immutable audit trail snapshot before deleting.
 * 2. Executes soft-deletion (via SoftDeletes trait) so foreign key constraints and
 *    historical shift reporting / audit records are not broken.
 */
final class DeleteActivityAction
{
    /**
     * Inject AuditService compliance dependency.
     *
     * @param  AuditService  $auditService  Service managing write-only audit trail
     */
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Execute soft-deletion and audit recording.
     *
     * @param  Activity  $activity  Activity instance to retire
     * @return void
     */
    public function execute(Activity $activity): void
    {
        // Snapshot all attributes prior to soft-deletion
        $this->auditService->log(
            subject: $activity,
            event: 'deleted',
            oldValues: $activity->toArray(),
        );

        // Soft delete: sets deleted_at timestamp, preserving historical foreign keys
        $activity->delete();
    }
}
