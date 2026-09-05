<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the public landing page with 200 ok for unauthenticated visitors', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Support Tracker');
    $response->assertSee('NPONTU TECHNOLOGIES');
    $response->assertSee('Making you free to achieve');
    $response->assertSee('zero-blindspot');
    $response->assertSee('Launch Shift Console');
    $response->assertSee('Inspect Telemetry HUD');
    $response->assertSee('Reactive Daily Shift Board');
    $response->assertSee('Two-Way Shift Handover Handshake');
    $response->assertSee('Ops Comms & Incident War Rooms', false);
    $response->assertSee('Forensic Compliance Audit Trail');
    $response->assertSee('Granular Privileges & SRE Grades', false);
    $response->assertSee('Real-Time Telemetry & Probes', false);
    $response->assertSee('The 4-Step Handover Lifecycle');
    $response->assertSee('Pre-Seeded Operational Roles');
    $response->assertSee('Kwame Mensah');
    $response->assertSee('Abena Owusu');
    $response->assertSee('Kofi Asante');
});

it('shows enter sre cockpit button when user is authenticated on the landing page', function () {
    $user = User::factory()->create([
        'name' => 'Kwame Mensah',
    ]);

    $response = $this->actingAs($user)->get('/');

    $response->assertOk();
    $response->assertSee('Enter SRE Cockpit');
    $response->assertSee("Go to Today's Shift Board", false);
});

it('maintains strict authentication guards on protected operational routes', function () {
    $protectedRoutes = [
        '/daily',
        '/messages',
        '/reports',
        '/monitoring',
        '/dashboard',
    ];

    foreach ($protectedRoutes as $route) {
        $response = $this->get($route);
        $response->assertRedirect('/login');
    }
});
