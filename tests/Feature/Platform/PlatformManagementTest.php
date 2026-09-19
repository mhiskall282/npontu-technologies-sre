<?php

declare(strict_types=1);

use App\Enums\PlatformRole;
use App\Models\FeatureFlag;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'platform_role' => PlatformRole::SuperAdmin->value,
    ]);
});

it('allows Super Admin to suspend an organization', function () {
    $org = Organization::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Acme Cloud Ops',
        'slug' => 'acme-cloud-ops',
        'company_code' => 'ACM-123',
        'status' => 'active',
        'tier' => 'team',
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->post("/admin/platform/organizations/{$org->id}/suspend", [
            'reason' => 'Delinquent account payment violation',
        ]);

    $response->assertSessionHas('warning');
    expect($org->fresh()->status)->toBe('suspended');

    $this->assertDatabaseHas('audit_logs', [
        'subject_type' => Organization::class,
        'subject_id' => $org->id,
        'event' => 'organization_suspended',
    ]);
});

it('allows Super Admin to reactivate a suspended organization', function () {
    $org = Organization::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Acme Cloud Ops',
        'slug' => 'acme-cloud-ops',
        'company_code' => 'ACM-123',
        'status' => 'suspended',
        'tier' => 'team',
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->post("/admin/platform/organizations/{$org->id}/reactivate");

    $response->assertSessionHas('success');
    expect($org->fresh()->status)->toBe('active');

    $this->assertDatabaseHas('audit_logs', [
        'subject_type' => Organization::class,
        'subject_id' => $org->id,
        'event' => 'organization_reactivated',
    ]);
});

it('allows Super Admin to suspend a user and revoke tokens', function () {
    $targetUser = User::factory()->create([
        'suspended_at' => null,
    ]);
    $targetUser->createToken('test-mobile-token');

    expect($targetUser->tokens()->count())->toBe(1);

    $response = $this->actingAs($this->superAdmin)
        ->post("/admin/platform/users/{$targetUser->id}/suspend", [
            'reason' => 'Security incident compromise',
        ]);

    $response->assertSessionHas('warning');
    expect($targetUser->fresh()->isSuspended())->toBeTrue();
    expect($targetUser->fresh()->tokens()->count())->toBe(0);
});

it('prevents Super Admin from suspending themselves', function () {
    $response = $this->actingAs($this->superAdmin)
        ->post("/admin/platform/users/{$this->superAdmin->id}/suspend", [
            'reason' => 'Self test',
        ]);

    $response->assertSessionHas('error');
    expect($this->superAdmin->fresh()->isSuspended())->toBeFalse();
});

it('allows Super Admin to toggle a feature flag', function () {
    $flag = FeatureFlag::create([
        'key' => 'test_feature_flag',
        'name' => 'Test Feature Flag',
        'is_enabled' => false,
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->patch("/admin/platform/features/{$flag->id}/toggle");

    $response->assertSessionHas('success');
    expect($flag->fresh()->is_enabled)->toBeTrue();

    $this->assertDatabaseHas('audit_logs', [
        'subject_type' => FeatureFlag::class,
        'subject_id' => $flag->id,
        'event' => 'feature_flag_toggled',
    ]);
});

it('allows Super Admin to update subscription status', function () {
    $org = Organization::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Beta Ops',
        'slug' => 'beta-ops',
        'company_code' => 'BET-999',
        'status' => 'active',
    ]);

    $plan = Plan::create([
        'name' => 'Team Ops',
        'slug' => 'team-ops-test',
        'tier' => 'team',
        'price_cents' => 4900,
        'billing_interval' => 'monthly',
    ]);

    $subscription = Subscription::create([
        'organization_id' => $org->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'current_period_start' => now(),
        'current_period_end' => now()->addMonth(),
        'billing_provider' => 'ready',
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->post("/admin/platform/subscriptions/{$subscription->id}/status", [
            'status' => 'past_due',
        ]);

    $response->assertSessionHas('success');
    expect($subscription->fresh()->status)->toBe('past_due');
});

it('renders the platform reports index without database driver errors', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get('/admin/platform/reports');

    $response->assertOk();
    $response->assertSee('Platform SaaS Intelligence');
});

it('renders the platform dashboard, users, and organizations index successfully', function () {
    $this->actingAs($this->superAdmin)
        ->get('/admin/platform')
        ->assertOk()
        ->assertSee('Administrative Cockpit');

    $this->actingAs($this->superAdmin)
        ->get('/admin/platform/users')
        ->assertOk();

    $this->actingAs($this->superAdmin)
        ->get('/admin/platform/organizations')
        ->assertOk();
});
