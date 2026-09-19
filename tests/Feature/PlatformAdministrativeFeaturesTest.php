<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PlatformRole;
use App\Models\FeatureFlag;
use App\Models\Organization;
use App\Models\PlatformAnnouncement;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PlatformAdministrativeFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $tenantOperator;

    private Organization $organization;

    private Workspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear platform settings cache between tests
        Cache::forget('opsora_platform_settings');

        // Create Platform Super Admin
        $this->superAdmin = User::factory()->create([
            'name' => 'Root Administrator',
            'email' => 'root@npontu.com',
            'role' => 'admin',
            'platform_role' => PlatformRole::SuperAdmin->value,
        ]);

        // Create Tenant Organization & Workspace
        $this->organization = Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Acme Telemetry',
            'slug' => 'acme-telemetry',
            'company_code' => 'ACM-999',
            'status' => 'active',
            'tier' => 'team',
        ]);

        $this->workspace = Workspace::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $this->organization->id,
            'name' => 'Primary Production Node',
            'slug' => 'primary-production-node',
            'status' => 'active',
            'owner_id' => $this->superAdmin->id,
        ]);

        // Create Tenant Operator
        $this->tenantOperator = User::factory()->create([
            'name' => 'Kwame Mensah',
            'email' => 'kwame@acmetelemetry.com',
            'role' => 'engineer',
            'platform_role' => null,
        ]);

        $this->workspace->members()->attach($this->tenantOperator->id, ['role' => 'member']);
        $this->organization->members()->attach($this->tenantOperator->id, ['role' => 'member']);
    }

    public function test_platform_admin_can_view_and_create_operational_announcements(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/admin/platform/announcements');

        $response->assertOk();
        $response->assertSee('Operational Announcements &amp; Broadcasts', false);

        // Publish new announcement
        $postResponse = $this->actingAs($this->superAdmin)
            ->post('/admin/platform/announcements', [
                'title' => 'Scheduled Core DB Maintenance',
                'message' => 'Core database migrations scheduled for Sunday 02:00 UTC.',
                'type' => 'critical',
                'is_active' => 1,
                'dismissible' => 1,
            ]);

        $postResponse->assertRedirect('/admin/platform/announcements');
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('platform_announcements', [
            'title' => 'Scheduled Core DB Maintenance',
            'type' => 'critical',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => PlatformAnnouncement::class,
            'event' => 'platform_announcement_created',
        ]);
    }

    public function test_platform_announcement_can_be_toggled_and_deleted(): void
    {
        $announcement = PlatformAnnouncement::create([
            'title' => 'Emergency Network Advisory',
            'message' => 'Network provider maintenance in Progress.',
            'type' => 'warning',
            'is_active' => true,
            'dismissible' => true,
            'created_by' => $this->superAdmin->id,
        ]);

        // Toggle to inactive
        $toggleResponse = $this->actingAs($this->superAdmin)
            ->post("/admin/platform/announcements/{$announcement->id}/toggle");

        $toggleResponse->assertRedirect('/admin/platform/announcements');
        $this->assertFalse($announcement->fresh()->is_active);

        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => PlatformAnnouncement::class,
            'event' => 'platform_announcement_toggled',
        ]);

        // Delete announcement
        $deleteResponse = $this->actingAs($this->superAdmin)
            ->delete("/admin/platform/announcements/{$announcement->id}");

        $deleteResponse->assertRedirect('/admin/platform/announcements');
        $this->assertDatabaseMissing('platform_announcements', ['id' => $announcement->id]);

        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => PlatformAnnouncement::class,
            'event' => 'platform_announcement_deleted',
        ]);
    }

    public function test_public_and_tenant_api_can_fetch_active_announcements(): void
    {
        PlatformAnnouncement::create([
            'title' => 'Live Telemetry Active',
            'message' => 'All nodes reporting nominal health.',
            'type' => 'info',
            'is_active' => true,
            'dismissible' => true,
        ]);

        PlatformAnnouncement::create([
            'title' => 'Old Inactive Notice',
            'message' => 'Old notice.',
            'type' => 'warning',
            'is_active' => false,
            'dismissible' => true,
        ]);

        $response = $this->getJson('/api/v1/announcements/active');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.title', 'Live Telemetry Active');
    }

    public function test_system_maintenance_mode_locks_out_tenants_while_exempting_platform_admins(): void
    {
        // Engage Maintenance Mode
        Cache::forever('opsora_platform_settings', [
            'platform_name' => 'Opsora SRE',
            'maintenance_mode' => true,
            'maintenance_message' => 'Emergency cloud migration in progress.',
            'maintenance_ends_at' => '15:00 UTC',
            'maintenance_bypass_key' => 'secret-bypass-token-123',
            'support_email' => 'ops@npontu.com',
        ]);

        // 1. Standard tenant visiting /daily receives 503 maintenance page
        $tenantResponse = $this->actingAs($this->tenantOperator)
            ->get('/daily');

        $tenantResponse->assertStatus(503);
        $tenantResponse->assertSee('Scheduled Platform Maintenance');
        $tenantResponse->assertSee('Emergency cloud migration in progress.');

        // 2. API requests return 503 JSON with maintenance message
        $apiResponse = $this->actingAs($this->tenantOperator)
            ->getJson('/api/v1/me');

        $apiResponse->assertStatus(503);
        $apiResponse->assertJsonPath('error', 'Platform Maintenance Active');
        $apiResponse->assertJsonPath('message', 'Emergency cloud migration in progress.');

        // 3. Platform Super Admin is exempt and can access Control Plane
        $adminResponse = $this->actingAs($this->superAdmin)
            ->get('/admin/platform');

        $adminResponse->assertOk();

        // 4. Request with valid bypass key is permitted
        $bypassResponse = $this->actingAs($this->tenantOperator)
            ->get('/daily?bypass_key=secret-bypass-token-123');

        $bypassResponse->assertOk();
    }

    public function test_platform_support_impersonation_lifecycle(): void
    {
        // 1. Super admin impersonates tenant operator
        $impersonateResponse = $this->actingAs($this->superAdmin)
            ->post("/admin/platform/users/{$this->tenantOperator->id}/impersonate", [
                'reason' => 'Diagnosing missing shift checklist entries.',
            ]);

        $impersonateResponse->assertRedirect('/daily');
        $impersonateResponse->assertSessionHas('warning');

        // Verify active user is now the tenant operator
        $this->assertEquals($this->tenantOperator->id, auth()->id());
        $this->assertEquals($this->superAdmin->id, session('opsora_impersonator_id'));

        // Verify start audit log
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => User::class,
            'subject_id' => $this->tenantOperator->id,
            'event' => 'tenant_impersonation_started',
        ]);

        // 2. Impersonated user sees the sticky top banner
        $dailyViewResponse = $this->get('/daily');
        $dailyViewResponse->assertOk();
        $dailyViewResponse->assertSee('Support Impersonation Mode');
        $dailyViewResponse->assertSee('Kwame Mensah');

        // 3. Conclude impersonation session
        $exitResponse = $this->post('/admin/platform/impersonate/exit');

        $exitResponse->assertRedirect('/admin/platform/organizations');
        $exitResponse->assertSessionHas('success');

        // Verify context restored to super admin
        $this->assertEquals($this->superAdmin->id, auth()->id());
        $this->assertFalse(session()->has('opsora_impersonator_id'));

        // Verify end audit log
        $this->assertDatabaseHas('audit_logs', [
            'subject_type' => User::class,
            'event' => 'tenant_impersonation_ended',
        ]);
    }

    public function test_platform_operations_hub_triggers_maintenance_utilities(): void
    {
        // 1. Cache purge
        $cacheResponse = $this->actingAs($this->superAdmin)
            ->post('/admin/platform/health/clear-cache');

        $cacheResponse->assertRedirect();
        $cacheResponse->assertSessionHas('success');
        $this->assertDatabaseHas('audit_logs', ['event' => 'platform_cache_cleared']);

        // 2. Token pruning
        $tokenResponse = $this->actingAs($this->superAdmin)
            ->post('/admin/platform/health/prune-tokens');

        $tokenResponse->assertRedirect();
        $tokenResponse->assertSessionHas('success');
        $this->assertDatabaseHas('audit_logs', ['event' => 'platform_tokens_pruned']);

        // 3. Queue retry
        $queueResponse = $this->actingAs($this->superAdmin)
            ->post('/admin/platform/health/retry-jobs');

        $queueResponse->assertRedirect();
        $queueResponse->assertSessionHas('success');
        $this->assertDatabaseHas('audit_logs', ['event' => 'queue_jobs_retried']);

        // 4. Diagnostics probe
        $diagResponse = $this->actingAs($this->superAdmin)
            ->post('/admin/platform/health/diagnostics');

        $diagResponse->assertRedirect();
        $diagResponse->assertSessionHas('success');
        $this->assertDatabaseHas('audit_logs', ['event' => 'platform_diagnostics_probed']);
    }

    public function test_feature_flag_full_lifecycle_management(): void
    {
        // Create flag
        $createResponse = $this->actingAs($this->superAdmin)
            ->post('/admin/platform/features', [
                'name' => 'AI Automated War Rooms',
                'key' => 'ai_automated_war_rooms',
                'description' => 'Generates AI war room recommendations',
                'is_enabled' => 0,
            ]);

        $createResponse->assertRedirect('/admin/platform/features');
        $flag = FeatureFlag::where('key', 'ai_automated_war_rooms')->firstOrFail();

        // Update flag metadata & target tiers
        $updateResponse = $this->actingAs($this->superAdmin)
            ->put("/admin/platform/features/{$flag->id}", [
                'name' => 'AI Automated Incident War Rooms (v2)',
                'description' => 'Updated capability description',
                'target_tiers' => ['enterprise'],
            ]);

        $updateResponse->assertRedirect('/admin/platform/features');
        $this->assertEquals('AI Automated Incident War Rooms (v2)', $flag->fresh()->name);
        $this->assertEquals(['enterprise'], $flag->fresh()->target_tiers);
        $this->assertDatabaseHas('audit_logs', ['event' => 'feature_flag_updated']);

        // Delete flag
        $deleteResponse = $this->actingAs($this->superAdmin)
            ->delete("/admin/platform/features/{$flag->id}");

        $deleteResponse->assertRedirect('/admin/platform/features');
        $this->assertDatabaseMissing('feature_flags', ['id' => $flag->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'feature_flag_deleted']);
    }
}
