<?php

declare(strict_types=1);

use App\Models\AuditLog;
use App\Models\ShiftHandover;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows outgoing lead to create a shift handover briefing', function () {
    $outgoingLead = User::factory()->create([
        'name' => 'Lead Kwesi',
        'role' => 'lead',
        'privileges' => ['sign_handovers'],
    ]);
    $incomingLead = User::factory()->create([
        'name' => 'Lead Abena',
        'role' => 'lead',
    ]);

    Sanctum::actingAs($outgoingLead);

    $response = $this->postJson('/api/v1/handovers', [
        'date' => today()->toDateString(),
        'shift' => 'morning',
        'incoming_lead_id' => $incomingLead->id,
        'summary' => 'All morning payment gateway heartbeat checks passed. Zero downtime.',
        'incidents' => 'Minor SMS provider latency between 07:15 and 07:30 resolved.',
        'pending_tasks_count' => 0,
        'completed_tasks_count' => 12,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'shift' => 'morning',
                'outgoing_lead_id' => $outgoingLead->id,
                'is_accepted' => false,
            ],
        ]);

    expect(ShiftHandover::where('summary', 'like', '%All morning payment gateway%')->exists())->toBeTrue();
    expect(AuditLog::where('event', 'created')->exists())->toBeTrue();
});

it('allows incoming lead to acknowledge and accept shift handover responsibility', function () {
    $incomingLead = User::factory()->create([
        'name' => 'Lead Abena',
        'role' => 'lead',
        'privileges' => ['accept_handovers'],
    ]);

    $handover = ShiftHandover::factory()->create([
        'incoming_lead_id' => $incomingLead->id,
        'accepted_at' => null,
    ]);

    Sanctum::actingAs($incomingLead);

    $response = $this->postJson("/api/v1/handovers/{$handover->id}/accept", [
        'remarks' => 'Acknowledged. Took custody of afternoon shift checks and active alarms.',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $handover->id,
                'is_accepted' => true,
                'acceptance_remarks' => 'Acknowledged. Took custody of afternoon shift checks and active alarms.',
            ],
        ]);

    expect($handover->fresh()->isAccepted())->toBeTrue();
    expect(AuditLog::where('event', 'handover_accepted')->exists())->toBeTrue();
});
