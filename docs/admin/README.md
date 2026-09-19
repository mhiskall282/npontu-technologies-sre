# Opsora Platform Control Plane — Administration Hub

> **Status:** IMPLEMENTED & OPERATIONAL  
> **Classification:** Confidential & Restricted (Platform Operators Only)  
> **Route:** `/admin/platform` & `/api/v1/platform/*`

The **Platform Control Plane** is the global administrative governance center of the Opsora multi-tenant SaaS platform. It governs tenant organizations, commercial plans, subscriptions, user privileges, SIEM security telemetry, feature flags, and sovereign regional compliance.

---

## 🏛️ Control Plane vs. Tenant Plane Separation

Opsora strictly enforces the separation between the **Platform Control Plane** and the **Tenant/Application Plane**:

```text
                           OPSORA SRE PLATFORM
                                    |
                    +---------------+---------------+
                    |                               |
          CONTROL PLANE (Global)          TENANT PLANE (Local)
                    |                               |
       Super / Platform Admins               Organizations
                    |                               |
           Platform Operations                 Workspaces
                    |                               |
          Commercial / Billing                  SRE Teams
                    |                               |
          Platform SIEM Security                 Services
                    |                               |
           Compliance / Audit                   Incidents
```

- **Control Plane (`/admin/platform`, `/api/v1/platform/*`)**: Unrestricted cross-tenant oversight executed exclusively via `TenantContext::withoutTenancy()` and authorized by `EnsurePlatformAdmin` middleware and `platform.*` permissions.
- **Tenant Plane (`/workspaces`, `/daily`, `/activities`)**: Strictly scoped by `TenantScope` and `TenantContext::workspaceId()`. Tenant users cannot access control plane routes or cross-tenant data.

---

## 📚 Documentation Index

| Guide | Description |
|---|---|
| [Architecture](architecture.md) | Control Plane architecture, data flows, cross-tenant scoping, and boundary rules |
| [Login Guide](login-guide.md) | Administrator authentication, seed credentials, MFA extension points, and emergency recovery |
| [Roles & Permissions](roles-and-permissions.md) | Granular `PlatformRole` enum cases and `platform.*` permission matrix |
| [Cockpit Dashboard](dashboard.md) | Real-time cockpit widgets, telemetry metrics, and platform health status |
| [Metrics Reference](metrics.md) | Exact mathematical definitions of MRR, ARR, compliance SLA, and active telemetry |
| [Organizations Management](organizations.md) | Lifecycle: approval queue, suspension, reactivation, tier changes, and member audits |
| [Users Management](users.md) | User administration, role escalation, suspension, and token revocation |
| [Workspaces Oversight](workspaces.md) | Multi-tenant team workspaces and personal sandboxes |
| [Subscriptions & Billing](subscriptions.md) | Commercial subscriptions, billing intervals, and payment statuses |
| [Plans & Pricing](plans.md) | Developer Sandbox (Free), Team Operations, and Enterprise Sovereign plan tiers |
| [Entitlements & Features](entitlements.md) | Centralized entitlement resolution pipeline across Web, API, and Mobile |
| [Feature Flags](feature-flags.md) | Dynamic feature gating by plan tier and tenant organization |
| [Security Center & SIEM](security.md) | Real-time threat detection, authentication audits, and privilege escalation traps |
| [Audit Trail](audit-logs.md) | Immutable audit log records, before/after diffs, and compliance verification |
| [Platform Health](platform-health.md) | Live diagnostics: Database latency, Cache, Queue workers, and Storage |
| [Enterprise Governance](enterprise.md) | Dedicated VPC deployments, customer-hosted models, and SOC2 compliance |
| [Data Residency](data-residency.md) | Sovereign regional isolation rules (Africa South, Europe West, US East) |
| [White-Labeling](white-label.md) | Tenant-specific brand customization and portal options |
| [Support Access](support-access.md) | Controlled, audited, and time-bounded customer support access patterns |
| [REST API Reference](api.md) | Platform REST endpoints (`/api/v1/platform/*`) for administrative clients |
| [Troubleshooting](troubleshooting.md) | Common operator diagnostics, database resets, and permission reconciliation |
