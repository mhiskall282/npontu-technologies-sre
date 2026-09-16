<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns date-range activity reports and aggregated charts to authorized users', function () {
    $user = User::factory()->create([
        'role' => 'lead',
        'privileges' => ['export_reports'],
    ]);
    Sanctum::actingAs($user);

    $activity = Activity::factory()->create();
    ActivityLog::create([
        'activity_id' => $activity->id,
        'date' => today()->toDateString(),
        'status' => 'done',
        'remark' => 'Completed backup check',
        'actor_name' => $user->name,
    ]);

    $response = $this->getJson('/api/v1/reports?from='.now()->subDays(7)->toDateString().'&to='.today()->toDateString());

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'data',
            'meta' => [
                'from',
                'to',
                'charts' => [
                    'status' => ['labels', 'values'],
                    'timeline' => ['labels', 'values'],
                ],
            ],
        ]);
});

it('forbids unprivileged operators from accessing reporting queries', function () {
    $agent = User::factory()->create([
        'role' => 'agent',
        'privileges' => [], // no export_reports
    ]);
    Sanctum::actingAs($agent);

    $response = $this->getJson('/api/v1/reports');

    $response->assertStatus(403);
});
