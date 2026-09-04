<?php

declare(strict_types=1);

namespace App\Actions\Handovers;

use App\Models\ShiftHandover;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;

/**
 * CreateShiftHandoverAction — Formal SRE Shift Handover Domain Action
 *
 * Persists an immutable shift handover briefing record, calculates operational metrics
 * snapshot, and generates a compliance audit log entry for shift transfer non-repudiation.
 */
final class CreateShiftHandoverAction
{
    /**
     * Inject AuditService compliance dependency.
     */
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Execute the shift handover creation and digital sign-off pipeline.
     *
     * @param  array{
     *     date: string,
     *     shift: string,
     *     incoming_lead_id: int|null,
     *     summary: string,
     *     incidents: string|null,
     *     pending_tasks_count: int,
     *     completed_tasks_count: int,
     * } $data Validated handover payload
     * @return ShiftHandover Newly created and signed handover instance
     */
    public function execute(array $data): ShiftHandover
    {
        $actorId = (int) Auth::id();

        $handover = ShiftHandover::create([
            'date' => $data['date'],
            'shift' => $data['shift'],
            'outgoing_lead_id' => $actorId,
            'incoming_lead_id' => $data['incoming_lead_id'] ?? null,
            'summary' => $data['summary'],
            'incidents' => $data['incidents'] ?? null,
            'pending_tasks_count' => $data['pending_tasks_count'] ?? 0,
            'completed_tasks_count' => $data['completed_tasks_count'] ?? 0,
            'signed_at' => now(),
        ]);

        // Record compliance audit log
        $this->auditService->log(
            subject: $handover,
            event: 'created',
            newValues: [
                'date' => $handover->date->format('Y-m-d'),
                'shift' => $handover->shift,
                'outgoing_lead_id' => $handover->outgoing_lead_id,
                'incoming_lead_id' => $handover->incoming_lead_id,
                'pending_tasks_count' => $handover->pending_tasks_count,
                'completed_tasks_count' => $handover->completed_tasks_count,
                'signed_at' => $handover->signed_at?->toIso8601String(),
            ],
        );

        // State change telemetry for SIEM
        logger()->channel('state_changes')->info('shift_handover.signed', [
            'handover_id' => $handover->id,
            'date' => $handover->date->format('Y-m-d'),
            'shift' => $handover->shift,
            'outgoing_lead_id' => $handover->outgoing_lead_id,
            'incoming_lead_id' => $handover->incoming_lead_id,
        ]);

        return $handover;
    }
}
