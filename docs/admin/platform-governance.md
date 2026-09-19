# Platform Administration & Operational Governance Guide

> **Target Audience**: Platform Administrators, Site Reliability Engineers, Customer Support Leads  
> **System Scope**: Opsora SRE SaaS Control Plane (`/admin/platform/*`)  
> **Classification**: Operational Runbook & Governance Standard

---

## 1. Executive Summary

This document governs how the Opsora SRE SaaS platform is managed, operated, and maintained. Real-world cloud platforms require strict boundaries between administrative interventions and tenant operations. This runbook establishes operating protocols for:
1. **Platform Announcements & System Broadcasts**
2. **Scheduled Maintenance Mode & Emergency Lockouts**
3. **Tenant Support Impersonation ("Login as Tenant")**
4. **Platform Operations Runner (Caches, Tokens, Queue Workers)**
5. **Feature Flag Targeting & Lifecycles**

---

## 2. Platform Announcements & Broadcasts

### 2.1 Broadcast Severities
| Severity | Visual Cue | Target Audience | Usage Scenario |
|---|---|---|---|
| `info` | Emerald / Cyan Banner | All active operators | Planned feature rollouts, minor policy updates, scheduled reports. |
| `warning` | Gold / Amber Banner | All active operators | Upcoming maintenance windows, degraded third-party integrations (SMS/Mail). |
| `critical` | Crimson Red (#E63946) | All active operators | Major incident war rooms, active platform degradation, emergency lockout advisory. |

### 2.2 Operational Procedures
1. Navigate to **Control Plane &rarr; Announcements & Broadcasts** (`/admin/platform/announcements`).
2. Click **Broadcast New Announcement**.
3. Input the headline, severity, message body, and optional expiration datetime.
4. Check **Activate immediately upon broadcast**.
5. Once published, the broadcast is instantly pushed to:
   - Web App: High-visibility top banner across all authenticated pages (`resources/views/layouts/app.blade.php`).
   - Mobile App: Ingested via `GET /api/v1/announcements/active`.

---

## 3. System Maintenance Mode & Emergency Lockout

### 3.1 Lockout Behavior & Exemptions
When `maintenance_mode` is enabled in **Enterprise Policies & Settings** (`/admin/platform/settings`):
- **Tenant Web Traffic**: Intercepted by `EnsurePlatformNotUnderMaintenance` and presented with the custom Npontu branded 503 Maintenance Page (`resources/views/errors/maintenance.blade.php`).
- **Tenant API / Mobile Traffic**: Returns HTTP 503 JSON with custom maintenance message, estimated restoration ETA, and primary support contact.
- **Platform Administrators**: Automatically exempt based on `user->isPlatformAdmin()`. Unrestricted access to `/admin/platform/*` is preserved.
- **Emergency Bypass Key**: Authorized external parties can append `?bypass_key=SECRET` to bypass the lockout.

---

## 4. Tenant Support Impersonation Protocol

### 4.1 Ethical & Security Standards
Impersonating a customer account is an elevated diagnostic privilege restricted to administrators with `platform_role != null`.
- **Zero Credential Sharing**: Admins never ask customers for passwords or MFA tokens.
- **Dual Audit Logging**: Starting and concluding an impersonation session triggers immutable `AuditLog` events (`tenant_impersonation_started` and `tenant_impersonation_ended`) containing the admin's verified client IP address.
- **Omnipresent Visual Indication**: While impersonating, a persistent, non-dismissible gold banner appears across the top of every screen:
  `⚠️ Support Impersonation Mode: Currently acting as [User Name] ([email]). All actions are logged.`
- **Immediate Context Restoration**: The admin can click **Exit Support Session** at any time to immediately restore their Platform Admin credentials.

---

## 5. Platform Operations Runner

Available under **Control Plane &rarr; Operations & Health** (`/admin/platform/health`):

| Operation | Command Executed | Purpose |
|---|---|---|
| **Purge System Caches** | `cache:clear`, `view:clear`, `route:clear` | Flushes stale application cache, re-compiles Blade templates, and refreshes routing tables. |
| **Prune Stale Tokens** | Deletes expired Sanctum tokens | Cleans up database storage and invalidates outdated mobile/API credentials. |
| **Retry Failed Queue Jobs** | `queue:retry all` | Automatically re-attempts processing of failed background jobs. |
| **Dispatch SRE Reports** | `reports:send-automated` | Forces compilation and immediate email delivery of daily shift and activity compliance reports. |
| **Deep Latency Probes** | Live read/write roundtrip | Measures millisecond latency of the Database, Cache driver, and Storage mounts. |

---

## 6. Audit & Compliance

Every operational action performed in the Control Plane is written to the immutable `audit_logs` table with:
- `actor_id` and `actor_name`
- `actor_role`
- `actor_ip` (provenance IP)
- `subject_type` and `subject_id`
- `event`
- `old_values` / `new_values` (JSON state snapshot)
- `created_at` timestamp
