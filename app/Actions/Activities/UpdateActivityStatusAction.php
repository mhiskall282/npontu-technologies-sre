<?php

declare(strict_types=1);

namespace App\Actions\Activities;

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * UpdateActivityStatusAction — Operational Status Transition & Append-Only Event Logger
 *
 * CRITICAL ARCHITECTURAL PATTERNS:
 * 1. Append-Only Event Store (FR-3 & FR-4):
 *    Status changes NEVER execute an SQL `UPDATE` on existing records. Every transition is an
 *    immutable event row appended to `activity_logs`. The current daily status is derived as
 *    the latest log for that calendar date. This preserves full handover history across shifts.
 *
 * 2. Server-Side Bio Capture & Non-Repudiation:
 *    Actor attributes (name, role, designation, and IP address) are derived strictly from
 *    the authenticated session and server request (`Request::ip()`). They are NEVER accepted
 *    from user input payloads, preventing bio-spoofing.
 *
 * 3. Separation of Concerns:
 *    - `activity_logs`: Domain state store used for shift handover views and daily reporting.
 *    - `audit_logs`: Security and compliance audit trail tracking model mutations with before/after diffs.
 *    - `state_changes` channel: Structured logging for SIEM and centralized observability.
 */
final class UpdateActivityStatusAction
{
    /**
     * Inject AuditService compliance dependency.
     *
     * @param  AuditService  $auditService  Service managing write-only audit trail
     */
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Persist an operational status transition event.
     *
     * @param  Activity  $activity  Operational check definition
     * @param  string  $status  New status ('pending' or 'done')
     * @param  string|null  $remark  Optional operator handover note or discrepancy remark
     * @param  string  $date  Shift calendar date (Y-m-d)
     * @param  string|null  $incidentTicket  Optional related incident ticket reference (e.g., 'INC-1042')
     * @param  bool  $isEscalated  Flag indicating this check is an active operational escalation
     * @return ActivityLog Newly created immutable event record
     */
    public function execute(
        Activity $activity,
        string $status,
        ?string $remark,
        string $date,
        ?string $incidentTicket = null,
        bool $isEscalated = false,
    ): ActivityLog {
        /** @var User $actor */
        $actor = Auth::user();

        // Retrieve existing derived status prior to appending the new event
        $oldStatus = $activity->currentStatusForDate($date);

        // ── 1. Append New Domain Event Row (Never UPDATE existing row) ─────
        $log = ActivityLog::create([
            'activity_id' => $activity->id,
            'date' => $date,
            'status' => $status,
            'remark' => $remark,
            'incident_ticket' => $incidentTicket,
            'is_escalated' => $isEscalated,
            'updated_by' => $actor->id,
            'actor_name' => $actor->name,               // Snapshot derived server-side
            'actor_role' => $actor->role,               // Snapshot derived server-side
            'actor_designation' => $actor->designation, // Snapshot derived server-side
            'actor_ip' => Request::ip(),                // Captured server-side
        ]);

        // ── 2. Record Security Compliance Audit Trail ─────────────────────
        $this->auditService->log(
            subject: $activity,
            event: 'status_changed',
            oldValues: ['status' => $oldStatus, 'date' => $date],
            newValues: [
                'status' => $status,
                'remark' => $remark,
                'incident_ticket' => $incidentTicket,
                'is_escalated' => $isEscalated,
                'date' => $date,
            ],
        );

        // ── 3. Structured Application Log for SIEM / Telemetry ────────────
        logger()->channel('state_changes')->info('activity.status_changed', [
            'activity_id' => $activity->id,
            'date' => $date,
            'old_status' => $oldStatus,
            'new_status' => $status,
            'incident_ticket' => $incidentTicket,
            'is_escalated' => $isEscalated,
            'actor_id' => $actor->id,
            'actor_ip' => Request::ip(),
        ]);

        return $log;
    }
}
