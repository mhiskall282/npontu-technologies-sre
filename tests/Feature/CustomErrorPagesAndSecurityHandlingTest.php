<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('non-existent route returns 404 with custom branded SRE error page', function () {
    $response = $this->get('/non-existent-sre-route-'.uniqid());

    $response->assertNotFound();
    $response->assertSee('Operational Resource Not Found');
    $response->assertSee("Today's Board", false);
    $response->assertSee('Support Tracker');
    $response->assertSee('NPONTU TECHNOLOGIES');
});

test('custom 419 error page renders session timeout explanation and re-auth CTA', function () {
    $view = view('errors.419')->render();

    expect($view)->toContain('419')
        ->toContain('SESSION TIMEOUT')
        ->toContain('Operational Session Expired')
        ->toContain('Sign In to Resume Shift')
        ->toContain('Refresh Page')
        ->toContain('SRE_CSRF_EXPIRATION');
});

test('custom 403 error page renders privilege restricted messaging', function () {
    $view = view('errors.403')->render();

    expect($view)->toContain('403')
        ->toContain('ACCESS FORBIDDEN')
        ->toContain('Privileged Action Restricted')
        ->toContain("Return to Today's Board")
        ->toContain('admin@npontu.local');
});

test('custom 500 error page renders SRE incident reference and recovery buttons', function () {
    $view = view('errors.500')->render();

    expect($view)->toContain('500')
        ->toContain('SRE RUNTIME EXCEPTION')
        ->toContain('Internal Operational Exception')
        ->toContain('Retry Operation')
        ->toContain('Inspect Health HUD');
});

test('custom 503 error page renders scheduled maintenance notice', function () {
    $view = view('errors.503')->render();

    expect($view)->toContain('503')
        ->toContain('MAINTENANCE WINDOW')
        ->toContain('Scheduled SRE Maintenance')
        ->toContain('Check Platform Status');
});

test('login page displays session expired alert banner when expired param is present', function () {
    $response = $this->get(route('login', ['expired' => '1']));

    $response->assertOk();
    $response->assertSee('Session Expired');
    $response->assertSee('Your shift session timed out due to inactivity');
});

test('login page includes quick operator test credentials and system health link', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('Operator Sign-In');
    $response->assertSee('Quick Operator Access (Test Accounts)');
    $response->assertSee('admin@npontu.local');
    $response->assertSee('lead@npontu.local');
    $response->assertSee('agent@npontu.local');
    $response->assertSee('System Health & Diagnostics', false);
});

test('login page displays authentication error banner on invalid credentials', function () {
    $response = $this->post(route('login'), [
        'email' => 'invalid@npontu.local',
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors('email');
    $response->assertRedirect();

    $viewResponse = $this->withViewErrors(['email' => 'These credentials do not match our records.'])->get(route('login'));
    $viewResponse->assertSee('Authentication Failed');
    $viewResponse->assertSee('These credentials do not match our records.');
});
