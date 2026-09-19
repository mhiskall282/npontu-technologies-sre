# Platform Administrator Authentication & Login Guide

> **Audience:** DevOps Engineers, Platform Administrators, Security Auditors  
> **Environment:** Local Development & Staging

## Overview

Platform administrators authenticate using the standard Opsora secure authentication pipeline. Upon successful credential verification, the system inspects the user's `platform_role` column. If the user possesses an authorized platform role, they are granted access to `/admin/platform` and `/api/v1/platform/*`.

---

## Seeded Administrative Accounts (Local / Testing Only)

The following safe demo credentials are seeded in development environments via `php artisan db:seed --class=PlatformControlPlaneSeeder`:

| Role | Email | Password | Access Scope |
|---|---|---|---|
| **Root Super Admin** | `opsora_superadmin@opsora.internal` | `OpsoraPlatformAdmin2026!` | Global unrestricted control plane access |
| **Primary SRE Lead** | `hello@johnokyere.xyz` | `password` | Super Admin control plane + tenant operations |
| **System Administrator** | `admin@npontu.local` | `password` | Platform Admin control plane + tenant admin |
| **Billing Admin** | `opsora_billing@opsora.internal` | `OpsoraBillingAdmin2026!` | Subscriptions, plans, revenue reports |
| **Security Admin** | `opsora_security@opsora.internal` | `OpsoraSecurityAdmin2026!` | SIEM events, user suspensions, audit logs |
| **Compliance Auditor** | `opsora_auditor@opsora.internal` | `OpsoraAuditor2026!` | Read-only audit logs and compliance reports |

> [!CAUTION]
> These credentials are exclusively for local automated testing and development. Production systems must never seed default passwords and must enforce external identity providers or hardware MFA.

---

## Login Flow

1. Navigate to `/login` or `/admin`.
2. Entering `/admin` as an unauthenticated visitor redirects to `/login`.
3. If an authenticated user without a `platform_role` navigates to `/admin`, the application aborts with an HTTP `403 Forbidden` response and logs a `security_events` warning record.
4. If an authorized administrator logs in, navigating to `/admin` automatically redirects to `/admin/platform`.

---

## Password Reset & Emergency Recovery

In development environments, an administrator's password can be reset via Artisan:

```bash
php artisan tinker --execute="App\Models\User::where('email', 'opsora_superadmin@opsora.internal')->first()->update(['password' => Hash::make('NewSecurePassword2026!')]);"
```
