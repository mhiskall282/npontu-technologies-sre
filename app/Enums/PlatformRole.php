<?php

declare(strict_types=1);

namespace App\Enums;

enum PlatformRole: string
{
    case SuperAdmin = 'super_admin';
    case PlatformAdmin = 'platform_admin';
    case PlatformOps = 'platform_ops';
    case SecurityAdmin = 'security_admin';
    case BillingAdmin = 'billing_admin';
    case SupportAdmin = 'support_admin';
    case Auditor = 'auditor';

    /**
     * Human-readable display label for the platform role.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrator (Root)',
            self::PlatformAdmin => 'Platform Administrator',
            self::PlatformOps => 'Platform Operations & SRE',
            self::SecurityAdmin => 'Security Administrator',
            self::BillingAdmin => 'Billing & Commercial Administrator',
            self::SupportAdmin => 'Customer Support Lead',
            self::Auditor => 'Compliance Auditor (Read-Only)',
        };
    }

    /**
     * Granular permissions granted to this platform role.
     *
     * @return list<string>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdmin => [
                'platform.dashboard.view',
                'platform.organizations.view',
                'platform.organizations.manage',
                'platform.organizations.suspend',
                'platform.workspaces.view',
                'platform.workspaces.manage',
                'platform.users.view',
                'platform.users.manage',
                'platform.users.suspend',
                'platform.plans.view',
                'platform.plans.manage',
                'platform.subscriptions.view',
                'platform.subscriptions.manage',
                'platform.features.view',
                'platform.features.manage',
                'platform.health.view',
                'platform.system.manage',
                'platform.security.view',
                'platform.security.manage',
                'platform.audit.view',
                'platform.reports.view',
                'platform.settings.view',
                'platform.settings.manage',
            ],
            self::PlatformAdmin => [
                'platform.dashboard.view',
                'platform.organizations.view',
                'platform.organizations.manage',
                'platform.organizations.suspend',
                'platform.workspaces.view',
                'platform.workspaces.manage',
                'platform.users.view',
                'platform.users.manage',
                'platform.users.suspend',
                'platform.features.view',
                'platform.features.manage',
                'platform.health.view',
                'platform.audit.view',
                'platform.reports.view',
                'platform.settings.view',
            ],
            self::PlatformOps => [
                'platform.dashboard.view',
                'platform.health.view',
                'platform.system.manage',
                'platform.workspaces.view',
                'platform.reports.view',
                'platform.audit.view',
            ],
            self::SecurityAdmin => [
                'platform.dashboard.view',
                'platform.security.view',
                'platform.security.manage',
                'platform.audit.view',
                'platform.users.view',
                'platform.users.suspend',
                'platform.reports.view',
            ],
            self::BillingAdmin => [
                'platform.dashboard.view',
                'platform.plans.view',
                'platform.plans.manage',
                'platform.subscriptions.view',
                'platform.subscriptions.manage',
                'platform.organizations.view',
                'platform.reports.view',
                'platform.audit.view',
            ],
            self::SupportAdmin => [
                'platform.dashboard.view',
                'platform.organizations.view',
                'platform.workspaces.view',
                'platform.users.view',
                'platform.reports.view',
                'platform.audit.view',
            ],
            self::Auditor => [
                'platform.dashboard.view',
                'platform.audit.view',
                'platform.security.view',
                'platform.reports.view',
                'platform.organizations.view',
                'platform.workspaces.view',
                'platform.users.view',
                'platform.plans.view',
                'platform.subscriptions.view',
                'platform.health.view',
            ],
        };
    }

    /**
     * Check whether this platform role has a specific capability.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this === self::SuperAdmin) {
            return true;
        }

        return in_array($permission, $this->permissions(), true);
    }
}
