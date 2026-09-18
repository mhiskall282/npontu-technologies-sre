<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use App\Services\TenantContext;

beforeEach(function () {
    TenantContext::clear();
});

afterEach(function () {
    TenantContext::clear();
});

it('previews unmapped legacy records in dry-run mode without modifying the database', function () {
    $org = Organization::create(['name' => 'Legacy Ops Corp', 'slug' => 'legacy-ops', 'company_code' => 'LEG-01']);
    $workspace = Workspace::create(['organization_id' => $org->id, 'name' => 'Legacy Primary WS', 'slug' => 'legacy-ws']);

    $user = User::factory()->create();

    // Create unmapped activity without global tenant scope
    $activity = Activity::withoutGlobalScopes()->create([
        'title' => 'Legacy Unscoped Server Check',
        'recurrence' => 'daily',
        'priority' => 'high',
        'created_by' => $user->id,
        'workspace_id' => null,
    ]);

    expect($activity->workspace_id)->toBeNull();

    $this->artisan('opsora:migrate-legacy', [
        '--dry-run' => true,
        '--target-org' => $org->id,
        '--target-workspace' => $workspace->id,
    ])
        ->expectsOutputToContain('DRY-RUN COMPLETE')
        ->assertSuccessful();

    $activity->refresh();
    expect($activity->workspace_id)->toBeNull(); // Still null because dry-run
});

it('migrates unmapped legacy records and logs audit reconciliation', function () {
    $org = Organization::create(['name' => 'Acme Cloud SRE', 'slug' => 'acme-cloud', 'company_code' => 'ACM-99']);
    $workspace = Workspace::create(['organization_id' => $org->id, 'name' => 'Acme Primary Ops', 'slug' => 'acme-primary']);

    $user = User::factory()->create();

    $activity = Activity::withoutGlobalScopes()->create([
        'title' => 'Unmapped Core Router Ping',
        'recurrence' => 'daily',
        'priority' => 'critical',
        'created_by' => $user->id,
        'workspace_id' => null,
    ]);

    $this->artisan('opsora:migrate-legacy', [
        '--target-org' => $org->id,
        '--target-workspace' => $workspace->id,
    ])
        ->expectsOutputToContain('Migration completed successfully.')
        ->assertSuccessful();

    $activity->refresh();
    expect($activity->workspace_id)->toBe($workspace->id);

    // Verify audit log entry
    $auditEntry = AuditLog::withoutGlobalScopes()
        ->where('event', 'legacy_saas_migration')
        ->latest('id')
        ->first();

    expect($auditEntry)->not->toBeNull();
    expect($auditEntry->new_values['target_workspace_id'])->toBe($workspace->id);
    expect($auditEntry->new_values['activities_migrated'])->toBeGreaterThanOrEqual(1);
});

it('rolls back workspace associations when requested', function () {
    $org = Organization::create(['name' => 'Temp Org', 'slug' => 'temp-org', 'company_code' => 'TMP-11']);
    $workspace = Workspace::create(['organization_id' => $org->id, 'name' => 'Temp WS', 'slug' => 'temp-ws']);

    $user = User::factory()->create();

    $activity = Activity::withoutGlobalScopes()->create([
        'title' => 'Rollback Candidate Check',
        'recurrence' => 'daily',
        'priority' => 'low',
        'created_by' => $user->id,
        'workspace_id' => $workspace->id,
    ]);

    $this->artisan('opsora:migrate-legacy', [
        '--rollback' => true,
        '--target-org' => $org->id,
        '--target-workspace' => $workspace->id,
    ])
        ->expectsOutputToContain("Rollback complete for Workspace #{$workspace->id}.")
        ->assertSuccessful();

    $activity->refresh();
    expect($activity->workspace_id)->toBeNull();
});
