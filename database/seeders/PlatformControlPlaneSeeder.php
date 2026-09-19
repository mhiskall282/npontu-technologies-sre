<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlatformRole;
use App\Models\FeatureFlag;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class PlatformControlPlaneSeeder extends Seeder
{
    /**
     * Seed platform plans, feature flags, and administrative control plane users.
     */
    public function run(): void
    {
        // 1. Commercial SaaS Plans
        $freePlan = Plan::updateOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Developer Sandbox',
                'description' => 'Essential SRE activity logging for small teams and solo engineers.',
                'tier' => 'free',
                'price_cents' => 0,
                'billing_interval' => 'monthly',
                'trial_days' => 14,
                'features' => [
                    'currency' => 'USD',
                    'max_users' => 3,
                    'max_workspaces' => 1,
                    'max_services' => 5,
                    'daily_activity_board' => true,
                    'basic_audit_log' => true,
                    'export_reports' => false,
                    'incident_war_rooms' => false,
                    'dual_signoff_handovers' => false,
                    'white_label' => false,
                ],
                'is_active' => true,
            ]
        );

        $teamPlan = Plan::updateOrCreate(
            ['slug' => 'team'],
            [
                'name' => 'Team Operations',
                'description' => 'Advanced SRE shift management with dual-signoff handovers and integrations.',
                'tier' => 'team',
                'price_cents' => 4900,
                'billing_interval' => 'monthly',
                'trial_days' => 14,
                'features' => [
                    'currency' => 'USD',
                    'max_users' => 15,
                    'max_workspaces' => 5,
                    'max_services' => 25,
                    'daily_activity_board' => true,
                    'basic_audit_log' => true,
                    'export_reports' => true,
                    'incident_war_rooms' => true,
                    'dual_signoff_handovers' => true,
                    'white_label' => false,
                ],
                'is_active' => true,
            ]
        );

        $enterprisePlan = Plan::updateOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise Sovereign',
                'description' => 'Dedicated sovereign data residency, white-label branding, and unlimited scale.',
                'tier' => 'enterprise',
                'price_cents' => 29900,
                'billing_interval' => 'monthly',
                'trial_days' => 30,
                'features' => [
                    'currency' => 'USD',
                    'max_users' => null,
                    'max_workspaces' => null,
                    'max_services' => null,
                    'daily_activity_board' => true,
                    'basic_audit_log' => true,
                    'export_reports' => true,
                    'incident_war_rooms' => true,
                    'dual_signoff_handovers' => true,
                    'white_label' => true,
                    'sovereign_data_residency' => true,
                    'custom_retention_policy' => true,
                ],
                'is_active' => true,
            ]
        );

        // 2. Default Feature Flags
        FeatureFlag::updateOrCreate(
            ['key' => 'sre_dual_handover_enforcement'],
            [
                'name' => 'SRE Dual-Handover SLA Enforcement',
                'description' => 'Requires both incoming and outgoing leads to digitally sign shift handovers.',
                'is_enabled' => true,
                'target_tiers' => ['free', 'team', 'enterprise'],
            ]
        );

        FeatureFlag::updateOrCreate(
            ['key' => 'sovereign_data_residency'],
            [
                'name' => 'Sovereign Regional Data Isolation',
                'description' => 'Restricts tenant DB read/write operations to designated cloud territory.',
                'is_enabled' => true,
                'target_tiers' => ['enterprise'],
            ]
        );

        FeatureFlag::updateOrCreate(
            ['key' => 'white_label_branding'],
            [
                'name' => 'Enterprise White-Label Customization',
                'description' => 'Allows tenant-specific logo, email styling, and portal theming.',
                'is_enabled' => true,
                'target_tiers' => ['enterprise'],
            ]
        );

        // 3. Platform Administration Users
        User::updateOrCreate(
            ['email' => 'opsora_superadmin@opsora.internal'],
            [
                'name' => 'Root Super Administrator',
                'password' => Hash::make('OpsoraPlatformAdmin2026!'),
                'role' => 'admin',
                'platform_role' => PlatformRole::SuperAdmin->value,
                'department' => 'Global Platform Operations',
                'designation' => 'Principal Control Plane Operator',
            ]
        );

        User::updateOrCreate(
            ['email' => 'opsora_billing@opsora.internal'],
            [
                'name' => 'Finance & Billing Admin',
                'password' => Hash::make('OpsoraBillingAdmin2026!'),
                'role' => 'admin',
                'platform_role' => PlatformRole::BillingAdmin->value,
                'department' => 'Commercial Billing',
                'designation' => 'Billing Administrator',
            ]
        );

        User::updateOrCreate(
            ['email' => 'opsora_security@opsora.internal'],
            [
                'name' => 'SIEM Security Admin',
                'password' => Hash::make('OpsoraSecurityAdmin2026!'),
                'role' => 'admin',
                'platform_role' => PlatformRole::SecurityAdmin->value,
                'department' => 'Platform Security & Compliance',
                'designation' => 'Security Administrator',
            ]
        );

        User::updateOrCreate(
            ['email' => 'opsora_auditor@opsora.internal'],
            [
                'name' => 'Independent Compliance Auditor',
                'password' => Hash::make('OpsoraAuditor2026!'),
                'role' => 'agent',
                'platform_role' => PlatformRole::Auditor->value,
                'department' => 'Internal Audit & Governance',
                'designation' => 'Lead Auditor',
            ]
        );

        // Elevate existing development accounts to Platform Administrators
        User::where('email', 'hello@johnokyere.xyz')->update([
            'platform_role' => PlatformRole::SuperAdmin->value,
        ]);

        User::where('email', 'admin@npontu.local')->update([
            'platform_role' => PlatformRole::PlatformAdmin->value,
        ]);

        // 4. Provision default active subscription for existing organizations
        $organizations = Organization::all();
        foreach ($organizations as $org) {
            if (! $org->subscription()) {
                Subscription::create([
                    'organization_id' => $org->id,
                    'plan_id' => $org->tier === 'enterprise' ? $enterprisePlan->id : ($org->tier === 'team' ? $teamPlan->id : $freePlan->id),
                    'status' => 'active',
                    'current_period_start' => now(),
                    'current_period_end' => now()->addMonth(),
                    'billing_provider' => 'ready',
                ]);
            }
        }
    }
}
