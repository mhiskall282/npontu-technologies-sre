<?php

declare(strict_types=1);

namespace App\Actions\Activities;

use App\Mail\ActivityAssignedMail;
use App\Mail\ActivityIncidentEscalatedMail;
use App\Models\Activity;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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

        // Notify if assigned_to was updated and newly set
        if (isset($validated['assigned_to']) && $validated['assigned_to'] !== ($oldValues['assigned_to'] ?? null)) {
            $assignee = User::find($validated['assigned_to']);
            if ($assignee && $assignee->email) {
                try {
                    Mail::to($assignee->email)->queue(
                        new ActivityAssignedMail($activity, $assignee, Auth::user())
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        // Notify if newly marked as incident
        if (! empty($validated['is_incident']) && empty($oldValues['is_incident'])) {
            $recipient = $activity->assignee ?? Auth::user();
            if ($recipient && $recipient->email) {
                try {
                    Mail::to($recipient->email)->queue(
                        new ActivityIncidentEscalatedMail($activity, $recipient, Auth::user())
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return $activity;
    }
}
