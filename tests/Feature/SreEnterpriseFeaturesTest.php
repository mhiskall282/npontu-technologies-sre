<?php

declare(strict_types=1);

use App\Livewire\ActivityStatusUpdater;
use App\Livewire\DailyActivityBoard;
use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\ShiftHandover;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('supervisors can create activity with priority, SLA, and pinned status', function () {
    $lead = User::factory()->create(['role' => 'lead']);

    $response = $this->actingAs($lead)->post(route('activities.store'), [
        'title' => 'Core Gateway Heartbeat Verification',
        'description' => 'Verify PaySwitch and MTN MoMo connectivity endpoints.',
        'category' => 'Infrastructure',
        'recurrence' => 'daily',
        'priority' => 'critical',
        'sla_time' => '08:30 GMT',
        'is_pinned' => true,
        'is_active' => true,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('activities', [
        'title' => 'Core Gateway Heartbeat Verification',
        'priority' => 'critical',
        'sla_time' => '08:30 GMT',
        'is_pinned' => true,
    ]);

    $activity = Activity::where('title', 'Core Gateway Heartbeat Verification')->firstOrFail();

    // Verify audit log captured new attributes
    $this->assertDatabaseHas('audit_logs', [
        'subject_id' => $activity->id,
        'subject_type' => Activity::class,
        'event' => 'created',
    ]);
});

test('operators can flag active incident escalation and ticket reference on status update', function () {
    $operator = User::factory()->create([
        'role' => 'agent',
        'name' => 'Kofi Mensah',
        'designation' => 'NOC Operator',
    ]);
    $activity = Activity::factory()->create(['title' => 'SMS Delivery Queue Depth Check']);
    $today = today()->format('Y-m-d');

    Livewire::actingAs($operator)
        ->test(ActivityStatusUpdater::class, [
            'activity' => $activity,
            'date' => $today,
            'currentStatus' => 'pending',
        ])
        ->set('status', 'pending')
        ->set('remark', 'SMS queue latency spike detected on Telecel gateway.')
        ->set('isEscalated', true)
        ->set('incidentTicket', 'INC-7821')
        ->call('save')
        ->assertDispatched('status-updated');

    // Verify immutable ActivityLog has escalation data
    $this->assertDatabaseHas('activity_logs', [
        'activity_id' => $activity->id,
        'date' => $today,
        'status' => 'pending',
        'incident_ticket' => 'INC-7821',
        'is_escalated' => true,
        'actor_name' => 'Kofi Mensah',
        'actor_designation' => 'NOC Operator',
    ]);

    // Verify AuditLog captured incident reference
    $latestAudit = AuditLog::where('subject_id', $activity->id)->latest('id')->first();
    expect($latestAudit)->not->toBeNull();
    expect($latestAudit->new_values['incident_ticket'])->toBe('INC-7821');
    expect($latestAudit->new_values['is_escalated'])->toBeTrue();

    // Verify board renders escalation alert
    Livewire::actingAs($operator)
        ->test(DailyActivityBoard::class)
        ->assertSee('ACTIVE ESCALATION:')
        ->assertSee('Ticket #INC-7821');
});

test('supervisors can bulk delegate multiple operational checks in one click', function () {
    $lead = User::factory()->create(['role' => 'lead']);
    $engineer = User::factory()->create(['role' => 'agent', 'name' => 'Abena Serwaa']);

    $check1 = Activity::factory()->create(['title' => 'Bulk Task Alpha', 'assigned_to' => null]);
    $check2 = Activity::factory()->create(['title' => 'Bulk Task Beta', 'assigned_to' => null]);
    $check3 = Activity::factory()->create(['title' => 'Bulk Task Gamma', 'assigned_to' => null]);

    Livewire::actingAs($lead)
        ->test(DailyActivityBoard::class)
        ->set('selectedActivities', [$check1->id, $check2->id, $check3->id])
        ->call('bulkAssign', $engineer->id)
        ->assertSee('Bulk delegation complete: 3 check(s) delegated to Abena Serwaa.');

    expect($check1->fresh()->assigned_to)->toBe($engineer->id);
    expect($check2->fresh()->assigned_to)->toBe($engineer->id);
    expect($check3->fresh()->assigned_to)->toBe($engineer->id);

    // Verify individual audit trail records created for all 3 mutations
    expect(AuditLog::where('event', 'updated')->whereIn('subject_id', [$check1->id, $check2->id, $check3->id])->count())
        ->toBe(3);
});

test('standard operators cannot bulk delegate activities', function () {
    $operator = User::factory()->create(['role' => 'agent']);
    $check1 = Activity::factory()->create();

    Livewire::actingAs($operator)
        ->test(DailyActivityBoard::class)
        ->set('selectedActivities', [$check1->id])
        ->call('bulkAssign', $operator->id)
        ->assertForbidden();
});

test('supervisors can sign off and record formal SRE shift handover report', function () {
    $outgoingLead = User::factory()->create(['role' => 'lead', 'name' => 'Kwame Osei']);
    $incomingLead = User::factory()->create(['role' => 'lead', 'name' => 'Ama Serwaa']);

    Activity::factory()->count(3)->create();
    $today = today()->format('Y-m-d');

    Livewire::actingAs($outgoingLead)
        ->test(DailyActivityBoard::class)
        ->call('openHandoverModal')
        ->set('handoverShift', 'morning')
        ->set('handoverIncomingLeadId', $incomingLead->id)
        ->set('handoverSummary', 'Morning shift operational briefing: all payment switches cleared without packet drop.')
        ->set('handoverIncidents', 'Monitored transient timeout on MTN USSD between 10:15 and 10:20 GMT. Self-healed.')
        ->call('saveHandover')
        ->assertSee('Shift handover report signed and logged successfully.');

    $this->assertDatabaseHas('shift_handovers', [
        'date' => $today,
        'shift' => 'morning',
        'outgoing_lead_id' => $outgoingLead->id,
        'incoming_lead_id' => $incomingLead->id,
        'pending_tasks_count' => 3,
        'completed_tasks_count' => 0,
    ]);

    // Verify audit log generated for shift handover
    $handover = ShiftHandover::firstOrFail();
    $this->assertDatabaseHas('audit_logs', [
        'subject_id' => $handover->id,
        'subject_type' => ShiftHandover::class,
        'event' => 'created',
    ]);

    // Verify handover briefing banner appears on the board
    Livewire::actingAs($incomingLead)
        ->test(DailyActivityBoard::class)
        ->assertSee('Operational Shift Handover Briefing')
        ->assertSee('Morning Shift')
        ->assertSee('Kwame Osei')
        ->assertSee('Morning shift operational briefing: all payment switches cleared without packet drop.');
});
