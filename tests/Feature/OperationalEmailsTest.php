<?php

declare(strict_types=1);

use App\Actions\Activities\CreateActivityAction;
use App\Actions\Handovers\CreateShiftHandoverAction;
use App\Mail\ActivityAssignedMail;
use App\Mail\ActivityIncidentEscalatedMail;
use App\Mail\ShiftHandoverReadyMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('dispatches ActivityAssignedMail when a task is assigned to an engineer', function () {
    Mail::fake();

    $creator = User::factory()->create();
    $assignee = User::factory()->create();

    $this->actingAs($creator);

    $action = app(CreateActivityAction::class);
    $activity = $action->execute([
        'title' => 'TLS Ingress Verification',
        'description' => 'Verify Kubernetes ingress TLS certificates',
        'status' => 'in_progress',
        'activity_date' => now()->format('Y-m-d'),
        'assigned_to' => $assignee->id,
    ]);

    Mail::assertQueued(ActivityAssignedMail::class, function ($mail) use ($assignee, $activity) {
        return $mail->recipient->id === $assignee->id && $mail->activity->id === $activity->id;
    });
});

it('dispatches ActivityIncidentEscalatedMail when an activity is flagged as an incident', function () {
    Mail::fake();

    $creator = User::factory()->create();
    $assignee = User::factory()->create();

    $this->actingAs($creator);

    $action = app(CreateActivityAction::class);
    $activity = $action->execute([
        'title' => 'Major Outage Incident',
        'description' => 'Major database connection pool exhaustion outage',
        'status' => 'in_progress',
        'activity_date' => now()->format('Y-m-d'),
        'assigned_to' => $assignee->id,
        'is_incident' => true,
    ]);

    Mail::assertQueued(ActivityIncidentEscalatedMail::class, function ($mail) use ($assignee, $activity) {
        return $mail->recipient->id === $assignee->id && $mail->activity->id === $activity->id;
    });
});

it('dispatches ShiftHandoverReadyMail to the incoming lead on shift sign-off', function () {
    Mail::fake();

    $outgoingLead = User::factory()->create();
    $incomingLead = User::factory()->create();

    $this->actingAs($outgoingLead);

    $action = app(CreateShiftHandoverAction::class);
    $handover = $action->execute([
        'date' => now()->format('Y-m-d'),
        'shift' => 'night',
        'incoming_lead_id' => $incomingLead->id,
        'summary' => 'Night shift completed. Zero critical alerts.',
        'incidents' => null,
        'pending_tasks_count' => 0,
        'completed_tasks_count' => 12,
    ]);

    Mail::assertQueued(ShiftHandoverReadyMail::class, function ($mail) use ($incomingLead, $handover) {
        return $mail->recipient->id === $incomingLead->id && $mail->handover->id === $handover->id;
    });
});
