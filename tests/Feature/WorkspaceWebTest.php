<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\OrganizationApplication;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use App\Services\TenantContext;

beforeEach(function () {
    TenantContext::clear();
});

afterEach(function () {
    TenantContext::clear();
});

it('redirects unauthenticated visitors attempting to access workspaces hub to login', function () {
    $this->get(route('workspaces.index'))
        ->assertRedirect(route('login'));
});

it('renders the workspaces hub for authenticated users with active workspace and memberships', function () {
    $user = User::factory()->create(['role' => 'engineer']);

    $org = Organization::create([
        'name' => 'Stark Industries Cloud',
        'slug' => 'stark-industries',
        'company_code' => 'STARK-777',
    ]);

    $workspace = Workspace::create([
        'organization_id' => $org->id,
        'name' => 'Stark Operations Core',
        'slug' => 'stark-ops',
        'status' => 'active',
    ]);

    WorkspaceMembership::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => 'operator',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['opsora_workspace_id' => $workspace->id])
        ->get(route('workspaces.index'));

    $response->assertOk()
        ->assertSee('Operational Workspaces')
        ->assertSee('Stark Operations Core')
        ->assertSee('Join by Company Code');
});

it('allows an authenticated user to provision a new workspace via web form', function () {
    $user = User::factory()->create(['role' => 'lead']);

    $response = $this->actingAs($user)->post(route('workspaces.store'), [
        'name' => 'Helicarrier Flight Control',
        'subdomain' => 'helicarrier-control',
        'retention_days' => 90,
    ]);

    $response->assertRedirect(route('activities.daily'));
    $response->assertSessionHas('status');

    $this->assertDatabaseHas('workspaces', [
        'name' => 'Helicarrier Flight Control',
        'subdomain' => 'helicarrier-control',
    ]);

    $workspace = Workspace::where('name', 'Helicarrier Flight Control')->first();
    expect($workspace)->not->toBeNull();
    expect($workspace->hasUser($user))->toBeTrue();
    expect(session('opsora_workspace_id'))->toBe($workspace->id);
});

it('allows switching the active workspace in session', function () {
    $user = User::factory()->create(['role' => 'engineer']);

    $org = Organization::create([
        'name' => 'Pym Technologies',
        'slug' => 'pym-tech',
        'company_code' => 'PYM-444',
    ]);

    $ws1 = Workspace::create([
        'organization_id' => $org->id,
        'name' => 'Pym Lab Alpha',
        'slug' => 'pym-alpha',
        'status' => 'active',
    ]);

    $ws2 = Workspace::create([
        'organization_id' => $org->id,
        'name' => 'Pym Lab Beta',
        'slug' => 'pym-beta',
        'status' => 'active',
    ]);

    WorkspaceMembership::create(['workspace_id' => $ws1->id, 'user_id' => $user->id, 'role' => 'operator']);
    WorkspaceMembership::create(['workspace_id' => $ws2->id, 'user_id' => $user->id, 'role' => 'operator']);

    $response = $this->actingAs($user)
        ->withSession(['opsora_workspace_id' => $ws1->id])
        ->post(route('workspaces.switch'), [
            'workspace_id' => $ws2->id,
        ]);

    $response->assertRedirect(route('activities.daily'));
    $response->assertSessionHas('status', "Active workspace switched to 'Pym Lab Beta'.");
    expect(session('opsora_workspace_id'))->toBe($ws2->id);
});

it('forbids switching to a workspace that the user is not a member of', function () {
    $user = User::factory()->create(['role' => 'engineer']);

    $org = Organization::create([
        'name' => 'Wakanda Design Group',
        'slug' => 'wakanda-design',
        'company_code' => 'WAK-999',
    ]);

    $restrictedWorkspace = Workspace::create([
        'organization_id' => $org->id,
        'name' => 'Vibranium Vault Operations',
        'slug' => 'vibranium-ops',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('workspaces.switch'), [
        'workspace_id' => $restrictedWorkspace->id,
    ]);

    $response->assertForbidden();
});

it('allows joining an organization workspace via valid company code', function () {
    $user = User::factory()->create(['role' => 'engineer']);

    $org = Organization::create([
        'name' => 'Oscorp Industries',
        'slug' => 'oscorp-industries',
        'company_code' => 'OSC-1234',
        'status' => 'active',
    ]);

    $primaryWs = Workspace::create([
        'organization_id' => $org->id,
        'name' => 'Oscorp Primary Ops',
        'slug' => 'oscorp-primary',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('workspaces.join'), [
        'company_code' => 'OSC-1234',
    ]);

    $response->assertRedirect(route('activities.daily'));
    $response->assertSessionHas('status', 'Joined Oscorp Industries successfully!');

    expect($primaryWs->hasUser($user))->toBeTrue();
    expect($org->hasUser($user))->toBeTrue();
    expect(session('opsora_workspace_id'))->toBe($primaryWs->id);
});

it('renders the self-service organization application form', function () {
    $user = User::factory()->create(['role' => 'lead']);

    $response = $this->actingAs($user)->get(route('organizations.apply'));

    $response->assertOk()
        ->assertSee('Register New Organization')
        ->assertSee('Organization / Company Name')
        ->assertSee('Deployment Topology');
});

it('processes self-service organization registration application', function () {
    $user = User::factory()->create(['role' => 'lead', 'email' => 'lead@shield-defense.org']);

    $response = $this->actingAs($user)->post(route('organizations.apply.store'), [
        'organization_name' => 'Strategic Homeland Division',
        'organization_slug' => 'shield-ops',
        'contact_email' => 'lead@shield-defense.org',
        'deployment_model' => 'dedicated_managed',
        'preferred_region' => 'us-east',
        'tier' => 'enterprise',
        'notes' => 'Critical government operations monitoring setup.',
    ]);

    $this->assertDatabaseHas('organization_applications', [
        'organization_name' => 'Strategic Homeland Division',
        'contact_email' => 'lead@shield-defense.org',
        'tier' => 'enterprise',
    ]);
});

it('restricts admin organization applications queue to platform administrators', function () {
    $engineer = User::factory()->create(['role' => 'engineer']);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($engineer)
        ->get(route('admin.organizations.applications'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.organizations.applications'))
        ->assertOk()
        ->assertSee('Organization Registration Queue');
});

it('allows platform administrator to approve an organization application and auto-provision organization and workspace', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $applicant = User::factory()->create(['role' => 'lead', 'email' => 'founder@quantum-ai.io']);

    $application = OrganizationApplication::create([
        'applicant_user_id' => $applicant->id,
        'organization_name' => 'Quantum AI Systems',
        'organization_slug' => 'quantum-ai',
        'contact_email' => 'founder@quantum-ai.io',
        'deployment_model' => 'shared_saas',
        'preferred_region' => 'eu-west',
        'tier' => 'team',
        'status' => 'pending_review',
        'risk_score' => 10,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.organizations.applications.review', $application), [
        'decision' => 'approved',
        'review_notes' => 'Verified corporate registry and domain ownership.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status');

    $application->refresh();
    expect($application->status)->toBe('approved');
    expect($application->reviewed_by)->toBe($admin->id);

    $this->assertDatabaseHas('organizations', [
        'name' => 'Quantum AI Systems',
        'slug' => 'quantum-ai',
    ]);

    $newOrg = Organization::where('slug', 'quantum-ai')->first();
    expect($newOrg)->not->toBeNull();
    expect($newOrg->workspaces()->count())->toBe(1);
    expect($newOrg->hasUser($applicant))->toBeTrue();
});
