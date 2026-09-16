<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns public uptime JSON health probe', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'timestamp',
            'db',
            'db_latency_ms',
            'storage',
            'cache',
            'uptime_sla',
        ]);
});

it('returns streaming real-time performance telemetry', function () {
    $response = $this->getJson('/api/v1/health/telemetry');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'data' => [
                'timestamp',
                'db_latency_ms',
                'cache_latency_ms',
                'memory_used_mb',
                'done_today',
                'pending_today',
            ],
        ]);
});

it('allows lead or admin to inspect full diagnostics', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/v1/health/diagnostics');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'data' => [
                'isOperational',
                'subsystems',
                'heartbeatTimeline',
                'sevenDayTrend',
                'recordCounts',
            ],
        ]);
});
