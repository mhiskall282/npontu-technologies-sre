<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows authenticated operators to list activities with shift status', function () {
    $user = User::factory()->create(['role' => 'agent']);
    Sanctum::actingAs($user);

    $activity = Activity::factory()->create([
        'title' => 'Core Payment Gateway Heartbeat',
        'priority' => 'critical',
        'is_pinned' => true,
    ]);

    $response = $this->getJson('/api/v1/activities');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonFragment([
            'title' => 'Core Payment Gateway Heartbeat',
            'priority' => 'critical',
            'is_pinned' => true,
            'current_status' => 'pending',
        ]);
});

it('allows authorized users to create operational checks', function () {
    $lead = User::factory()->create([
        'role' => 'lead',
        'privileges' => ['manage_activities'],
    ]);
    Sanctum::actingAs($lead);

    $response = $this->postJson('/api/v1/activities', [
        'title' => 'Daily Database Replication Lag Inspection',
        'description' => 'Verify secondary replica lag < 50ms',
        'category' => 'Database',
        'recurrence' => 'daily',
        'priority' => 'high',
        'sla_time' => '09:00 GMT',
        'is_pinned' => true,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'title' => 'Daily Database Replication Lag Inspection',
                'priority' => 'high',
            ],
        ]);

    expect(Activity::where('title', 'Daily Database Replication Lag Inspection')->exists())->toBeTrue();
    expect(AuditLog::where('event', 'created')->exists())->toBeTrue();
});

it('records an append-only status checkoff event with bio capture and audit trail', function () {
    $agent = User::factory()->create([
        'name' => 'Kofi Annan',
        'role' => 'agent',
        'designation' => 'NOC SRE Specialist',
    ]);
    Sanctum::actingAs($agent);

    $activity = Activity::factory()->create();

    $response = $this->postJson("/api/v1/activities/{$activity->id}/status", [
        'status' => 'done',
        'remark' => 'Verification logs match monitoring dashboard exactly.',
        'date' => today()->toDateString(),
        'incident_ticket' => 'INC-8821',
        'is_escalated' => false,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'log' => [
                    'status' => 'done',
                    'remark' => 'Verification logs match monitoring dashboard exactly.',
                    'actor_name' => 'Kofi Annan',
                    'incident_ticket' => 'INC-8821',
                ],
            ],
        ]);

    // Ensure append-only event row was stored
    expect(ActivityLog::where('activity_id', $activity->id)
        ->where('status', 'done')
        ->where('actor_name', 'Kofi Annan')
        ->exists()
    )->toBeTrue();

    // Ensure compliance audit log was stored
    expect(AuditLog::where('subject_id', $activity->id)
        ->where('event', 'status_changed')
        ->exists()
    )->toBeTrue();
});

it('supports bulk task delegation by authorized leads', function () {
    $lead = User::factory()->create([
        'role' => 'lead',
        'privileges' => ['assign_tasks'],
    ]);
    $engineer = User::factory()->create(['name' => 'Ama Serwaa']);
    Sanctum::actingAs($lead);

    $act1 = Activity::factory()->create();
    $act2 = Activity::factory()->create();

    $response = $this->postJson('/api/v1/activities/bulk-assign', [
        'activity_ids' => [$act1->id, $act2->id],
        'assigned_to' => $engineer->id,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'count' => 2,
                'assigned_to' => $engineer->id,
                'assignee_name' => 'Ama Serwaa',
            ],
        ]);

    expect($act1->fresh()->assigned_to)->toBe($engineer->id);
    expect($act2->fresh()->assigned_to)->toBe($engineer->id);
});
