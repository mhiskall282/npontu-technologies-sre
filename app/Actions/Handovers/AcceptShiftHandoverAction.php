<?php

declare(strict_types=1);

namespace App\Actions\Handovers;

use App\Models\ShiftHandover;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;

/**
 * AcceptShiftHandoverAction — SRE Shift Handover Acceptance & Sign-On Action
 *
 * Implements the incoming shift lead's acknowledgment and acceptance of operational
 * responsibility, completing the formal two-way handover handshake.
 */
final class AcceptShiftHandoverAction
{
    /**
     * Inject AuditService compliance dependency.
     */
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Execute the shift handover acceptance and sign-on.
     *
     * @param  ShiftHandover  $handover  Handover record to accept
     * @param  string|null  $remarks  Optional sign-on remarks or acknowledgement notes
     * @return ShiftHandover Updated ShiftHandover instance
     */
    public function execute(ShiftHandover $handover, ?string $remarks = null): ShiftHandover
    {
        $actorId = (int) Auth::id();
        $acceptedAt = now();

        $handover->update([
            'accepted_at' => $acceptedAt,
            'accepted_by_id' => $actorId,
            'acceptance_remarks' => $remarks,
        ]);

        // Compliance audit trail for shift responsibility transfer
        $this->auditService->log(
            subject: $handover,
            event: 'handover_accepted',
            newValues: [
                'accepted_at' => $acceptedAt->toIso8601String(),
                'accepted_by_id' => $actorId,
                'acceptance_remarks' => $remarks,
            ],
        );

        // State changes telemetry for SIEM
        logger()->channel('state_changes')->info('shift_handover.accepted', [
            'handover_id' => $handover->id,
            'date' => $handover->date->format('Y-m-d'),
            'shift' => $handover->shift,
            'accepted_by_id' => $actorId,
        ]);

        return $handover;
    }
}
