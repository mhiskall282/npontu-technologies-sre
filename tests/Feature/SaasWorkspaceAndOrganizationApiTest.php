<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\OrganizationApplication;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;

it('lists all workspaces accessible to the authenticated user', function () {
    $user = User::factory()->create(['role' => 'agent']);

    $org = Organization::create([
        'name' => 'Acme Cloud',
        'slug' => 'acme-cloud',
        'company_code' => 'ACM-123',
    ]);

    $ws = Workspace::create([
        'organization_id' => $org->id,
        'name' => 'Acme NOC',
        'slug' => 'acme-noc',
        'status' => 'active',
    ]);

    WorkspaceMembership::create([
        'workspace_id' => $ws->id,
        'user_id' => $user->id,
        'role' => 'agent',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/v1/workspaces');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.name', 'Acme NOC')
        ->assertJsonPath('data.0.organization.name', 'Acme Cloud');
});

it('allows user to provision a new personal workspace', function () {
    $user = User::factory()->create(['role' => 'agent']);

    $response = $this->actingAs($user)
        ->postJson('/api/v1/workspaces', [
            'name' => 'My Personal Lab',
            'slug' => 'my-personal-lab',
            'is_personal' => true,
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'My Personal Lab')
        ->assertJsonPath('data.is_personal', true);

    $this->assertDatabaseHas('workspaces', [
        'name' => 'My Personal Lab',
        'is_personal' => true,
        'owner_user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('workspace_memberships', [
        'user_id' => $user->id,
        'role' => 'admin',
    ]);
});

it('allows user to switch active workspace context', function () {
    $user = User::factory()->create(['role' => 'agent']);

    $ws = Workspace::create([
        'name' => 'Production Edge',
        'slug' => 'production-edge',
        'status' => 'active',
    ]);

    WorkspaceMembership::create([
        'workspace_id' => $ws->id,
        'user_id' => $user->id,
        'role' => 'agent',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/v1/workspaces/switch', [
            'workspace_id' => $ws->id,
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $ws->id)
        ->assertJsonPath('data.name', 'Production Edge');
});

it('allows user to join an organization using its company code', function () {
    $user = User::factory()->create(['role' => 'agent']);

    $org = Organization::create([
        'name' => 'Fintech Hub',
        'slug' => 'fintech-hub',
        'company_code' => 'FNT-999',
        'status' => 'active',
    ]);

    $ws = Workspace::create([
        'organization_id' => $org->id,
        'name' => 'Fintech Primary SRE',
        'slug' => 'fintech-primary-sre',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/v1/organizations/join-by-code', [
            'company_code' => 'FNT-999',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.organization.name', 'Fintech Hub')
        ->assertJsonPath('data.primary_workspace.name', 'Fintech Primary SRE');

    $this->assertDatabaseHas('organization_memberships', [
        'organization_id' => $org->id,
        'user_id' => $user->id,
        'role' => 'member',
    ]);

    $this->assertDatabaseHas('workspace_memberships', [
        'workspace_id' => $ws->id,
        'user_id' => $user->id,
        'role' => 'agent',
    ]);
});

it('rejects invalid company code on join', function () {
    $user = User::factory()->create(['role' => 'agent']);

    $response = $this->actingAs($user)
        ->postJson('/api/v1/organizations/join-by-code', [
            'company_code' => 'INVALID-CODE',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['company_code']);
});

it('auto-approves low-risk standard organization applications', function () {
    $user = User::factory()->create(['role' => 'agent']);

    $response = $this->actingAs($user)
        ->postJson('/api/v1/organizations/apply', [
            'organization_name' => 'Green Energy Corp',
            'organization_slug' => 'green-energy',
            'contact_email' => 'ops@greenenergy.com',
            'tier' => 'team',
            'deployment_model' => 'shared_saas',
            'preferred_region' => 'eu-west',
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.auto_approved', true)
        ->assertJsonPath('data.status', 'approved');

    $this->assertDatabaseHas('organizations', [
        'slug' => 'green-energy',
        'status' => 'active',
    ]);
});

it('places customer-hosted or enterprise applications into manual review queue', function () {
    $user = User::factory()->create(['role' => 'agent']);

    $response = $this->actingAs($user)
        ->postJson('/api/v1/organizations/apply', [
            'organization_name' => 'National Bank Systems',
            'organization_slug' => 'national-bank',
            'contact_email' => 'secops@nationalbank.gov',
            'tier' => 'enterprise',
            'deployment_model' => 'customer_hosted',
            'preferred_region' => 'af-south',
            'notes' => 'Requires on-premise hardware deployment.',
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.auto_approved', false)
        ->assertJsonPath('data.status', 'pending_review');

    $this->assertDatabaseHas('organization_applications', [
        'organization_slug' => 'national-bank',
        'status' => 'pending_review',
    ]);
});

it('allows platform administrators to review and approve applications', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $applicant = User::factory()->create(['role' => 'agent']);

    $application = OrganizationApplication::create([
        'organization_name' => 'Global Logistics Inc',
        'organization_slug' => 'global-logistics',
        'applicant_user_id' => $applicant->id,
        'contact_email' => 'sre@globallogistics.com',
        'status' => 'pending_review',
        'tier' => 'enterprise',
        'deployment_model' => 'dedicated_managed',
        'risk_score' => 40,
    ]);

    $response = $this->actingAs($admin)
        ->postJson("/api/v1/organizations/applications/{$application->id}/review", [
            'decision' => 'approved',
            'review_notes' => 'Contract verified. Deployment approved for dedicated environment.',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'approved');

    $this->assertDatabaseHas('organizations', [
        'slug' => 'global-logistics',
        'status' => 'active',
    ]);

    $this->assertDatabaseHas('organization_memberships', [
        'user_id' => $applicant->id,
        'role' => 'owner',
    ]);
});

it('allows platform administrators to reject applications with justification', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $applicant = User::factory()->create(['role' => 'agent']);

    $application = OrganizationApplication::create([
        'organization_name' => 'Spam Ops LLC',
        'organization_slug' => 'spam-ops',
        'applicant_user_id' => $applicant->id,
        'contact_email' => 'bot@mailinator.com',
        'status' => 'pending_review',
        'risk_score' => 90,
    ]);

    $response = $this->actingAs($admin)
        ->postJson("/api/v1/organizations/applications/{$application->id}/review", [
            'decision' => 'rejected',
            'rejection_reason' => 'Disposable email domain violates acceptable use policy.',
            'review_notes' => 'Automated risk flag confirmed by human review.',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'rejected');

    $this->assertDatabaseHas('organization_applications', [
        'id' => $application->id,
        'status' => 'rejected',
        'rejection_reason' => 'Disposable email domain violates acceptable use policy.',
    ]);
});
