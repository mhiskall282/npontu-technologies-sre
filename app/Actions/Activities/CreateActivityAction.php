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
            'assigned_to' => $activity->assigned_to,
        ]);

        // Customized operational email notification on task assignment
        if (! empty($activity->assigned_to)) {
            $assignee = User::find($activity->assigned_to);
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

        // Customized email notification on urgent SRE incident
        if (! empty($validated['is_incident']) || $activity->priority === 'critical') {
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
