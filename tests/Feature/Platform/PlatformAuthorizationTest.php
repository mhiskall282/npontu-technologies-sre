<?php

declare(strict_types=1);

use App\Enums\PlatformRole;
use App\Models\User;

it('denies unauthenticated guests from accessing the platform control plane', function () {
    $response = $this->get('/admin/platform');

    $response->assertRedirect('/login');
});

it('denies standard tenant users from accessing the platform control plane with 403', function () {
    $tenantUser = User::factory()->create([
        'platform_role' => null,
    ]);

    $response = $this->actingAs($tenantUser)->get('/admin/platform');

    $response->assertForbidden();
});

it('allows Super Admin to access the platform control plane dashboard', function () {
    $superAdmin = User::factory()->create([
        'platform_role' => PlatformRole::SuperAdmin->value,
    ]);

    $response = $this->actingAs($superAdmin)->get('/admin/platform');

    $response->assertOk()
        ->assertViewIs('admin.platform.dashboard')
        ->assertSee('Administrative Cockpit');
});

it('redirects platform admin from /admin to /admin/platform', function () {
    $superAdmin = User::factory()->create([
        'platform_role' => PlatformRole::SuperAdmin->value,
    ]);

    $response = $this->actingAs($superAdmin)->get('/admin');

    $response->assertRedirect(route('admin.platform.dashboard'));
});

it('aborts with 403 when non-platform user accesses /admin', function () {
    $tenantUser = User::factory()->create([
        'platform_role' => null,
    ]);

    $response = $this->actingAs($tenantUser)->get('/admin');

    $response->assertForbidden();
});

it('allows Billing Admin to view plans and subscriptions', function () {
    $billingAdmin = User::factory()->create([
        'platform_role' => PlatformRole::BillingAdmin->value,
    ]);

    $response = $this->actingAs($billingAdmin)->get('/admin/platform/subscriptions');

    $response->assertOk()
        ->assertViewIs('admin.platform.subscriptions.index');
});

it('prevents suspended users from logging in', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'suspended_at' => now(),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});
