<?php

declare(strict_types=1);

namespace App\Actions\Activities;

use App\Models\Activity;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;

/**
 * CreateActivityAction — Domain Action to Provision a New Operational Check Definition
 *
 * RESPONSIBILITIES:
 * 1. Persists a new Activity record with the authenticated user ID as created_by.
 * 2. Emits an immutable security AuditLog entry capturing the initial configuration payload.
 * 3. Emits a structured telemetry log to the 'state_changes' channel for SIEM ingestion.
 */
final class CreateActivityAction
{
    /**
     * Inject AuditService compliance dependency.
     *
     * @param  AuditService  $auditService  Service managing write-only audit trail
     */
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Execute the activity creation pipeline.
     *
     * @param  array  $validated  Validated activity payload (title, description, category, recurrence, is_active)
     * @return Activity Persisted Activity model instance
     */
    public function execute(array $validated): Activity
    {
        $activity = Activity::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        // Write immutable audit log with full initial attributes
        $this->auditService->log(
            subject: $activity,
            event: 'created',
            newValues: $validated,
        );

        // Structured state-change telemetry
        logger()->channel('state_changes')->info('activity.created', [
            'activity_id' => $activity->id,
            'title' => $activity->title,
            'actor_id' => Auth::id(),
        ]);

        return $activity;
    }
}
