<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PlatformRole;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PlatformUserPrivilegesAndIpAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $workspaceAdmin;

    private User $targetOperator;

    private Workspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        $org = Organization::create([
            'name' => 'Acme Cloud NOC',
            'slug' => 'acme-cloud-noc',
            'company_code' => 'ACM-999',
        ]);

        $this->workspace = Workspace::create([
            'organization_id' => $org->id,
            'name' => 'Primary SRE Workspace',
            'slug' => 'primary-sre-workspace',
            'status' => 'active',
        ]);

        $this->superAdmin = User::factory()->create([
            'name' => 'Root Super Admin',
            'email' => 'superadmin@opsora.io',
            'role' => 'admin',
            'platform_role' => PlatformRole::SuperAdmin->value,
        ]);

        $this->workspaceAdmin = User::factory()->create([
            'name' => 'Tenant Lead Admin',
            'email' => 'tenantadmin@acme.com',
            'role' => 'admin',
            'platform_role' => null,
        ]);

        $this->targetOperator = User::factory()->create([
            'name' => 'Kofi Operator',
            'email' => 'kofi@acme.com',
            'role' => 'agent',
            'grade' => 'L2',
            'privileges' => ['escalate_incidents'],
        ]);

        WorkspaceMembership::create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->workspaceAdmin->id,
            'role' => 'admin',
            'status' => 'active',
        ]);
        WorkspaceMembership::create([
            'workspace_id' => $this->workspace->id,
            'user_id' => $this->targetOperator->id,
            'role' => 'agent',
            'status' => 'active',
        ]);
    }

    public function test_audit_logs_record_and_display_ip_address(): void
    {
        // Create an audit log record with actor_ip
        $log = AuditLog::create([
            'actor_id' => $this->superAdmin->id,
            'actor_name' => $this->superAdmin->name,
            'actor_role' => 'admin',
            'actor_ip' => '192.168.1.100',
            'subject_type' => User::class,
            'subject_id' => $this->targetOperator->id,
            'event' => 'user_privileges_updated',
            'old_values' => ['privileges' => ['escalate_incidents']],
            'new_values' => ['privileges' => ['escalate_incidents', 'manage_activities']],
            'created_at' => now(),
        ]);

        // Model attribute and accessor checks
        $this->assertEquals('192.168.1.100', $log->actor_ip);
        $this->assertEquals('192.168.1.100', $log->ip_address);

        // Verify web audit trail page renders the IP address
        $response = $this->actingAs($this->superAdmin)->get(route('admin.platform.audit.index'));
        $response->assertOk();
        $response->assertSee('192.168.1.100');
        $response->assertSee('Root Super Admin');
        $response->assertSee('user_privileges_updated');
    }

    public function test_get_privileges_catalog_api_returns_all_18_privileges_with_categories(): void
    {
        $response = $this->actingAs($this->targetOperator, 'sanctum')
            ->getJson(route('api.v1.privileges.index'));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.total', 18);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'catalog',
                'categories',
                'total',
            ],
        ]);

        // Check newly added enterprise privileges are present
        $response->assertJsonPath('data.catalog.manage_workspaces.label', 'Manage Workspaces');
        $response->assertJsonPath('data.catalog.manage_billing.label', 'Subscription & Billing Access');
        $response->assertJsonPath('data.catalog.manage_security.label', 'SIEM & Security Telemetry');
        $response->assertJsonPath('data.catalog.manage_feature_flags.label', 'Feature Flags & Entitlements');
        $response->assertJsonPath('data.catalog.manage_integrations.label', 'Webhooks & API Integrations');
        $response->assertJsonPath('data.catalog.broadcast_announcements.label', 'Broadcast Emergency Announcements');
        $response->assertJsonPath('data.catalog.resolve_incidents.label', 'Resolve Incidents & Post-Mortem');
        $response->assertJsonPath('data.catalog.purge_audit_records.label', 'Compliance Archival & Data Purge');
        $response->assertJsonPath('data.catalog.execute_runbooks.label', 'Execute SRE Runbooks');
    }

    public function test_platform_admin_can_update_user_privileges_via_api(): void
    {
        $newPrivileges = [
            'manage_activities',
            'execute_runbooks',
            'manage_workspaces',
            'manage_security',
            'broadcast_announcements',
        ];

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->putJson(route('api.v1.users.privileges.update', $this->targetOperator->id), [
                'privileges' => $newPrivileges,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.privileges', $newPrivileges);

        $this->targetOperator->refresh();
        $this->assertTrue($this->targetOperator->canManageActivities());
        $this->assertTrue($this->targetOperator->canExecuteRunbooks());
        $this->assertTrue($this->targetOperator->canManageWorkspaces());
        $this->assertTrue($this->targetOperator->canManageSecurity());
        $this->assertTrue($this->targetOperator->canBroadcastAnnouncements());
        $this->assertFalse($this->targetOperator->canManageBilling());

        // Verify audit log captured the update
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => User::class,
            'subject_id' => $this->targetOperator->id,
            'event' => 'user_privileges_updated',
        ]);
    }

    public function test_unauthorized_user_cannot_update_privileges_via_api(): void
    {
        $attacker = User::factory()->create([
            'role' => 'agent',
            'platform_role' => null,
        ]);

        $response = $this->actingAs($attacker, 'sanctum')
            ->putJson(route('api.v1.users.privileges.update', $this->targetOperator->id), [
                'privileges' => ['manage_users', 'manage_security'],
            ]);

        $response->assertForbidden();
    }

    public function test_platform_super_admin_can_update_privileges_via_web_form(): void
    {
        $selectedPrivileges = [
            'manage_activities',
            'sign_handovers',
            'resolve_incidents',
            'manage_billing',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->patch(route('admin.platform.users.update-privileges', $this->targetOperator->id), [
                'privileges' => $selectedPrivileges,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->targetOperator->refresh();
        $this->assertEquals($selectedPrivileges, $this->targetOperator->privileges);
        $this->assertTrue($this->targetOperator->canResolveIncidents());
        $this->assertTrue($this->targetOperator->canManageBilling());

        // Verify show page displays the user with privileges
        $showResponse = $this->actingAs($this->superAdmin)
            ->get(route('admin.platform.users.show', $this->targetOperator->id));
        $showResponse->assertOk();
        $showResponse->assertSee('Granular User Privileges');
        $showResponse->assertSee('18 Available');
        $showResponse->assertSee('Resolve Incidents');
    }
}
