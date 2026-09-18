<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Organization;
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

it('automatically attaches the active workspace_id upon activity creation', function () {
    $org = Organization::create([
        'name' => 'Acme Cloud Corp',
        'slug' => 'acme-cloud',
        'company_code' => 'ACM-001',
    ]);

    $workspace = Workspace::create([
        'organization_id' => $org->id,
        'name' => 'Acme Production SRE',
        'slug' => 'acme-production',
        'status' => 'active',
    ]);

    TenantContext::setWorkspace($workspace);

    $user = User::factory()->create(['role' => 'lead']);

    $activity = Activity::create([
        'title' => 'Core DB Replication Health',
        'recurrence' => 'daily',
        'priority' => 'critical',
        'created_by' => $user->id,
    ]);

    expect($activity->workspace_id)->toBe($workspace->id);
});

it('strictly restricts queries to the active workspace via TenantScope', function () {
    $orgA = Organization::create(['name' => 'Org A', 'slug' => 'org-a', 'company_code' => 'ORG-A']);
    $workspaceA = Workspace::create(['organization_id' => $orgA->id, 'name' => 'Workspace A', 'slug' => 'ws-a']);

    $orgB = Organization::create(['name' => 'Org B', 'slug' => 'org-b', 'company_code' => 'ORG-B']);
    $workspaceB = Workspace::create(['organization_id' => $orgB->id, 'name' => 'Workspace B', 'slug' => 'ws-b']);

    // Create activity in Workspace A
    TenantContext::setWorkspace($workspaceA);
    $activityA = Activity::factory()->create([
        'title' => 'Workspace A Health Check',
        'recurrence' => 'daily',
    ]);

    // Create activity in Workspace B
    TenantContext::setWorkspace($workspaceB);
    $activityB = Activity::factory()->create([
        'title' => 'Workspace B Health Check',
        'recurrence' => 'daily',
    ]);

    // Query while active in Workspace A
    TenantContext::setWorkspace($workspaceA);
    $resultsInA = Activity::all();
    expect($resultsInA->pluck('id'))->toContain($activityA->id)
        ->and($resultsInA->pluck('id'))->not->toContain($activityB->id);

    // Query while active in Workspace B
    TenantContext::setWorkspace($workspaceB);
    $resultsInB = Activity::all();
    expect($resultsInB->pluck('id'))->toContain($activityB->id)
        ->and($resultsInB->pluck('id'))->not->toContain($activityA->id);
});

it('allows administrative cross-tenant queries when using withoutTenancy bypass', function () {
    $orgA = Organization::create(['name' => 'Org Alpha', 'slug' => 'org-alpha', 'company_code' => 'ALP-01']);
    $wsA = Workspace::create(['organization_id' => $orgA->id, 'name' => 'Alpha WS', 'slug' => 'alpha-ws']);

    $orgB = Organization::create(['name' => 'Org Beta', 'slug' => 'org-beta', 'company_code' => 'BET-01']);
    $wsB = Workspace::create(['organization_id' => $orgB->id, 'name' => 'Beta WS', 'slug' => 'beta-ws']);

    TenantContext::setWorkspace($wsA);
    $actA = Activity::factory()->create(['title' => 'Alpha Task', 'recurrence' => 'daily']);

    TenantContext::setWorkspace($wsB);
    $actB = Activity::factory()->create(['title' => 'Beta Task', 'recurrence' => 'daily']);

    TenantContext::setWorkspace($wsA);
    expect(Activity::count())->toBe(1);

    $totalUnscoped = TenantContext::withoutTenancy(function () {
        return Activity::count();
    });

    // In a test with RefreshDatabase, the migration created 8 default activities, plus our 2 new ones
    expect($totalUnscoped)->toBeGreaterThanOrEqual(2);
});

it('rejects access when user tries to access a workspace they do not belong to', function () {
    $org = Organization::create(['name' => 'Delta Tech', 'slug' => 'delta-tech', 'company_code' => 'DEL-01']);
    $workspace = Workspace::create(['organization_id' => $org->id, 'name' => 'Delta WS', 'slug' => 'delta-ws']);

    $userWithoutMembership = User::factory()->create(['role' => 'agent']);

    $response = $this->actingAs($userWithoutMembership)
        ->withHeaders(['X-Workspace-Id' => (string) $workspace->id])
        ->getJson('/api/v1/activities');

    $response->assertStatus(403)
        ->assertJsonPath('error', 'Unauthorized Workspace Access');
});

it('allows access when user holds active membership in the requested workspace', function () {
    $org = Organization::create(['name' => 'Epsilon Ops', 'slug' => 'epsilon-ops', 'company_code' => 'EPS-01']);
    $workspace = Workspace::create(['organization_id' => $org->id, 'name' => 'Epsilon WS', 'slug' => 'epsilon-ws']);

    $user = User::factory()->create(['role' => 'agent']);

    WorkspaceMembership::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => 'agent',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->withHeaders(['X-Workspace-Id' => (string) $workspace->id])
        ->getJson('/api/v1/activities');

    $response->assertOk();
});

it('rejects access when accessing a suspended workspace', function () {
    $org = Organization::create(['name' => 'Zeta SRE', 'slug' => 'zeta-sre', 'company_code' => 'ZET-01']);
    $suspendedWorkspace = Workspace::create([
        'organization_id' => $org->id,
        'name' => 'Suspended WS',
        'slug' => 'suspended-ws',
        'status' => 'suspended',
    ]);

    $user = User::factory()->create(['role' => 'agent']);
    WorkspaceMembership::create([
        'workspace_id' => $suspendedWorkspace->id,
        'user_id' => $user->id,
        'role' => 'agent',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->withHeaders(['X-Workspace-Id' => (string) $suspendedWorkspace->id])
        ->getJson('/api/v1/activities');

    $response->assertStatus(403)
        ->assertJsonPath('error', 'Workspace Suspended');
});

it('rejects access when parent organization is suspended', function () {
    $suspendedOrg = Organization::create([
        'name' => 'Suspended Org',
        'slug' => 'suspended-org',
        'company_code' => 'SUS-01',
        'status' => 'suspended',
    ]);

    $workspace = Workspace::create([
        'organization_id' => $suspendedOrg->id,
        'name' => 'Child WS',
        'slug' => 'child-ws',
        'status' => 'active',
    ]);

    $user = User::factory()->create(['role' => 'agent']);
    WorkspaceMembership::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'role' => 'agent',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)
        ->withHeaders(['X-Workspace-Id' => (string) $workspace->id])
        ->getJson('/api/v1/activities');

    $response->assertStatus(403)
        ->assertJsonPath('error', 'Organization Suspended');
});

it('supports personal workspaces owned by individual users', function () {
    $user = User::factory()->create(['role' => 'agent']);

    $personalWorkspace = Workspace::create([
        'organization_id' => null,
        'owner_user_id' => $user->id,
        'name' => "{$user->name}'s Personal Workspace",
        'slug' => "personal-{$user->id}",
        'is_personal' => true,
        'status' => 'active',
    ]);

    WorkspaceMembership::create([
        'workspace_id' => $personalWorkspace->id,
        'user_id' => $user->id,
        'role' => 'admin',
        'status' => 'active',
    ]);

    expect($user->personalWorkspace()->id)->toBe($personalWorkspace->id);
    expect($personalWorkspace->isPersonal())->toBeTrue();
    expect($personalWorkspace->hasUser($user))->toBeTrue();
});
