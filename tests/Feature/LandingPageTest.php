<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the public storytelling landing page with 200 ok for unauthenticated visitors', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Support Tracker');
    $response->assertSee('NPONTU TECHNOLOGIES');
    $response->assertSee('When critical infrastructure runs 24/7');
    $response->assertSee('Launch Shift Console');
    $response->assertSee('Inspect Telemetry HUD');
    $response->assertSee('The Reactive Daily Shift Board');
    $response->assertSee('Verifiable Custody Transfer');
    $response->assertSee('Zero-Friction Incident War Rooms');
    $response->assertSee('100% Immutable Forensic Audit Shield');
    $response->assertSee('The Anatomy of a Flawless Handover');
    $response->assertSee('Live Verification');
    $response->assertSee('Outgoing Sign-Off');
    $response->assertSee('Incoming Sign-On');
    $response->assertSee('Compliance Archival');
    $response->assertSee('8 Core Subsystems');

    // Asserts that raw test credentials and demo checklists are NOT on public homepage
    $response->assertDontSee('Password: password');
    $response->assertDontSee('Pre-Seeded Operational Roles');
    $response->assertDontSee('sre-cockpit.npontu.local');
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

it('hides quick operator access helper on mobile in the login interface', function () {
    $response = $this->get('/login');

    $response->assertOk();
    // Verify that quick access test accounts container is explicitly hidden on mobile (<sm)
    $response->assertSee('hidden sm:block mt-6 pt-5 border-t border-gray-100', false);
    $response->assertSee('Quick Operator Access (Test Accounts):');
    $response->assertSee('admin@npontu.local');
});

it('provides collapsible mobile navigation drawer on public pages', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('id="landing-mobile-menu"', false);
    $response->assertSee('id="mobile-nav-toggle-btn"', false);
    $response->assertSee('toggleLandingNav');
});
