<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\SystemHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('health check endpoint returns json for api probes and automated monitors', function () {
    $response = $this->getJson(route('health'));

    $response->assertOk();
    $response->assertJsonStructure([
        'status',
        'timestamp',
        'db',
        'db_latency_ms',
        'storage',
        'cache',
        'uptime_sla',
        'environment',
        'version',
    ]);
    expect($response->json('status'))->toBe('ok');
    expect($response->json('db'))->toBe('ok');
});

test('health check endpoint returns json when requested with format query parameter', function () {
    $response = $this->get(route('health', ['format' => 'json']));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/json');
    expect($response->json('status'))->toBe('ok');
    expect($response->json('db'))->toBe('ok');
});

test('telemetry endpoint returns live performance metrics stream', function () {
    $response = $this->getJson(route('health.telemetry'));

    $response->assertOk();
    $response->assertJsonStructure([
        'timestamp',
        'timestamp_iso',
        'db_latency_ms',
        'cache_latency_ms',
        'memory_used_mb',
        'memory_peak_mb',
        'memory_limit',
        'done_today',
        'pending_today',
        'total_logs_today',
        'status',
        'database_driver',
    ]);
    expect($response->json('status'))->toBe('operational');
    expect($response->json('db_latency_ms'))->toBeGreaterThanOrEqual(0);
});

test('health check endpoint renders interactive system health dashboard for browser requests', function () {
    $response = $this->get(route('health'));

    $response->assertOk();
    $response->assertSee('System Health');
    $response->assertSee('ALL 8 CORE SERVICES OPERATIONAL');
    $response->assertSee('Real-Time Performance Telemetry HUD');
    $response->assertSee('24-Hour Availability Heartbeat Timeline');
    $response->assertSee('7-Day Latency Trend');
    $response->assertSee('Email & Notification Gateway');
    $response->assertSee('Subsystem Diagnostics & Component Probes');
    $response->assertSee('JSON API');
});

test('authenticated operator can view system health dashboard with user layout navigation', function () {
    $user = User::factory()->create(['name' => 'Kofi SRE Lead', 'role' => 'lead']);

    $response = $this->actingAs($user)->get(route('health'));

    $response->assertOk();
    $response->assertSee('System Health');
    $response->assertSee('Kofi SRE Lead');
    $response->assertSee("Today's Board");
});

test('system health service generates telemetry probes and timeline structures', function () {
    $service = app(SystemHealthService::class);
    $metrics = $service->getFullHealthMetrics();

    expect($metrics['isOperational'])->toBeTrue();
    expect($metrics['dbProbe']['status'])->toBe('operational');
    expect($metrics['storageProbe']['status'])->toBe('operational');
    expect($metrics['cacheProbe']['status'])->toBe('operational');
    expect($metrics['mailProbe']['status'])->toBe('operational');
    expect($metrics['heartbeatTimeline'])->toHaveCount(24);
    expect($metrics['subsystems'])->toHaveCount(8);
    expect($metrics['sevenDayTrend']['labels'])->toHaveCount(7);
});
