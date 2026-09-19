# Platform Roles & Granular Permissions Matrix

> **Model:** `App\Enums\PlatformRole` & `App\Providers\AppServiceProvider`  
> **Rule:** Principle of Least Privilege

## Overview

Opsora decouples tenant workspace roles (`admin`, `lead`, `engineer`) from platform-level administrative roles. Platform roles are represented by the typed enum `App\Enums\PlatformRole`.

---

## Role Definitions

| Platform Role | Enum Value | Intended Operator Responsibility |
|---|---|---|
| **Super Admin** | `super_admin` | Unrestricted platform operator; manages all resources, settings, roles, and emergency policies. |
| **Platform Administrator** | `platform_admin` | Manages tenant organizations, user accounts, plans, feature flags, and diagnostics. |
| **Platform Operations** | `platform_ops` | Infrastructure and telemetry operator; monitors health, workspaces, and deployments. |
| **Security Administrator** | `security_admin` | Governs SIEM security events, performs user suspensions, inspects audit logs. |
| **Billing Administrator** | `billing_admin` | Manages commercial SaaS plans, organization subscriptions, and revenue intelligence. |
| **Support Administrator** | `support_admin` | Investigates customer inquiries, views organization workspaces and service health. |
| **Compliance Auditor** | `auditor` | Read-only compliance officer; reviews immutable audit logs and SLA compliance records. |

---

## Granular Permission Matrix

```text
Permission                       SuperAdmin  PlatformAdmin  PlatformOps  SecurityAdmin  BillingAdmin  SupportAdmin  Auditor
platform.dashboard.view              ✅           ✅            ✅             ✅             ✅            ✅         ✅
platform.users.view                  ✅           ✅            ✅             ✅             ❌            ✅         ✅
platform.users.manage                ✅           ✅            ❌             ✅             ❌            ❌         ❌
platform.organizations.view          ✅           ✅            ✅             ✅             ✅            ✅         ✅
platform.organizations.manage        ✅           ✅            ❌             ❌             ❌            ❌         ❌
platform.organizations.suspend       ✅           ✅            ❌             ✅             ❌            ❌         ❌
platform.workspaces.view             ✅           ✅            ✅             ✅             ❌            ✅         ✅
platform.workspaces.manage           ✅           ✅            ❌             ❌             ❌            ❌         ❌
platform.plans.view                  ✅           ✅            ❌             ❌             ✅            ❌         ❌
platform.plans.manage                ✅           ✅            ❌             ❌             ✅            ❌         ❌
platform.subscriptions.view          ✅           ✅            ❌             ❌             ✅            ❌         ❌
platform.subscriptions.manage        ✅           ✅            ❌             ❌             ✅            ❌         ❌
platform.features.view               ✅           ✅            ✅             ❌             ❌            ❌         ❌
platform.features.manage             ✅           ✅            ❌             ❌             ❌            ❌         ❌
platform.health.view                 ✅           ✅            ✅             ✅             ❌            ❌         ❌
platform.security.view               ✅           ✅            ❌             ✅             ❌            ❌         ❌
platform.security.manage             ✅           ❌            ❌             ✅             ❌            ❌         ❌
platform.audit.view                  ✅           ✅            ❌             ✅             ❌            ❌         ✅
platform.reports.view                ✅           ✅            ✅             ❌             ✅            ❌         ✅
platform.settings.manage             ✅           ❌            ❌             ❌             ❌            ❌         ❌
```

---

## Enforcement Architecture

Permissions are enforced at two distinct layers:
1. **Route Middleware (`EnsurePlatformPermission`)**: Validates that `$user->hasPlatformPermission($permission)` evaluates to true before the controller action is executed.
2. **Global Gate Interceptor (`AppServiceProvider`)**:
   ```php
   Gate::before(function ($user, string $ability) {
       if (str_starts_with($ability, 'platform.') && method_exists($user, 'hasPlatformPermission')) {
           return $user->hasPlatformPermission($ability);
       }
   });
   ```
