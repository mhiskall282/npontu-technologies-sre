# Single-Tenant to Multi-Tenant SaaS Migration & Coexistence Strategy

> **Author**: Opsora SRE Principal Platform & Release Architecture  
> **Target Version**: Opsora SaaS v2.0 (Multi-Tenant Control Plane)  
> **Status**: Active & Pilot-Ready  
> **Git Branches**: `main` / `legacy/single-tenant` (Legacy Single-Tenant) & `feat/opsora-saas` (Multi-Tenant SaaS)

---

## 1. Executive Summary & Coexistence Mandate

Opsora SRE is transitioning from a dedicated single-tenant operations tool to an enterprise-grade, multi-tenant Software-as-a-Service (SaaS) platform with a centralized **Platform Control Plane** (`/admin/platform`).

### The Core Mandate
1. **Zero Disruption to Existing Operations**: The legacy tenant installation (`Npontu Technologies`) must remain fully functional with zero downtime, zero data loss, and uninterrupted shift tracking.
2. **Branch Isolation**: The existing single-tenant codebase is preserved on `legacy/single-tenant` and `main`, while multi-tenant SaaS features are developed and tested on `feat/opsora-saas`.
3. **Dual Deployment Topology**: Both systems can be deployed simultaneously to independent target URLs, enabling parallel testing and validation without interference.
4. **Clean Migration Path**: A repeatable database migration and backfill pipeline automatically upgrades legacy databases to the multi-tenant topology.

---

## 2. Architecture & Coexistence Topology

### 2.1 Dual-Plane System Architecture

```mermaid
flowchart TB
    subgraph ClientLayer["1. Client Layer"]
        WebAdmin["Web Browser (Super Admin)"]
        WebTenant["Web Browser (Engineers)"]
        MobileFlutter["Flutter Mobile App (iOS / Android)"]
    end

    subgraph IngressRouting["2. Ingress & Domain Routing"]
        Ingress["Cloudflare / Nginx Reverse Proxy"]
    end

    subgraph ControlPlane["3. Platform Control Plane (/admin/platform)"]
        CPDashboard["Administrative Cockpit"]
        CPOrgs["Tenant Org Directory"]
        CPUsers["Global Access Control"]
        CPSubs["Commercial Subscriptions & MRR"]
        CPFlags["Feature Flags & Entitlements"]
        CPHealth["Infrastructure Probes"]
        CPSIEM["SIEM Security Telemetry"]
        CPAudit["Immutable Audit Ledger"]
    end

    subgraph TenantPlane["4. Tenant Operations Plane"]
        TenantContext["TenantContext (ResolveTenantContext Middleware)"]
        WS["Workspaces & Role Isolation"]
        Activities["Shift Activities & Checklists"]
        Handovers["Dual-Signoff Shift Handovers"]
        Comms["Ops Comms & Emergency Broadcasts"]
    end

    subgraph DataStorage["5. Data Tier (MySQL 8.0 / InnoDB)"]
        LegacyTables["Legacy SRE Data (activities, handovers, users)"]
        SaaSTables["SaaS Platform Tables (saas_plans, subscriptions, feature_flags, security_events)"]
    end

    ClientLayer --> Ingress
    Ingress -- "/admin/platform/*" --> ControlPlane
    Ingress -- "/workspaces/*, /activities/*" --> TenantPlane
    Ingress -- "/api/v1/*" --> TenantContext

    ControlPlane --> SaaSTables
    ControlPlane --> LegacyTables
    TenantPlane --> LegacyTables
```

---

## 3. URL & Deployment Routing Strategy

To ensure legacy and new deployments run side-by-side without interference:

| Environment | Branch | Target URL | Database | Purpose |
|---|---|---|---|---|
| **Legacy Single-Tenant** | `main` or `legacy/single-tenant` | `https://npontu-support-tracker.onrender.com` | `npontu_legacy_db` | Continues serving daily operational shift tracking for Npontu operators. |
| **New Multi-Tenant SaaS** | `feat/opsora-saas` | `https://opsora-saas.onrender.com` | `opsora_saas_db` | Pilot deployment for multi-tenant organizations, commercial billing, and control plane. |
| **Platform Control Plane** | `feat/opsora-saas` | `https://opsora-saas.onrender.com/admin/platform` | `opsora_saas_db` | Super Admin oversight across all tenant organizations. |
| **Mobile API Ingress** | `feat/opsora-saas` | `https://opsora-saas.onrender.com/api/v1` | `opsora_saas_db` | Supports `/me`, `/workspaces`, `/activities`, `/handovers` with SaaS tenant metadata. |

### 3.1 Domain & Subdomain Resolution Matrix

```mermaid
sequenceDiagram
    autonumber
    actor User as Operator / Admin
    participant DNS as DNS / Gateway
    participant Ingress as App Ingress
    participant Middleware as ResolveTenantContext
    participant App as Platform / Tenant Controller

    alt Platform Super Admin Route
        User->>DNS: Request /admin/platform
        DNS->>Ingress: Route to Web Cluster
        Ingress->>Middleware: EnsurePlatformAdmin
        Middleware-->>App: Direct to Platform Control Plane (Bypasses Tenant Scope)
        App-->>User: Render Administrative Cockpit
    else Tenant Subdomain Route (e.g. acme.opsora.io)
        User->>DNS: Request acme.opsora.io
        DNS->>Ingress: Resolve Subdomain "acme"
        Ingress->>Middleware: Lookup Organization by slug "acme"
        Middleware->>TenantContext: Set Tenant Organization & Workspace Scope
        Middleware-->>App: Apply Global Scope (organization_id = 42)
        App-->>User: Render Scoped Tenant Dashboard
    else Legacy Single-Tenant Route
        User->>DNS: Request /activities or /api/v1
        DNS->>Ingress: No Subdomain / Header
        Ingress->>Middleware: Fallback to Default Organization (NPT-OPS-01)
        Middleware->>TenantContext: Bind to Npontu Technologies (Org #1)
        Middleware-->>App: Query activities where organization_id = 1 or is null
        App-->>User: Uninterrupted Legacy Experience
    end
```

---

## 4. Zero-Downtime Database Migration Pipeline

### 4.1 Schema Evolution Strategy
The database schema has been designed with **strict backward compatibility**:
1. **No Destructive Drops**: Existing tables (`users`, `activities`, `activity_logs`, `shift_handovers`, `workspaces`) retain all original columns.
2. **Nullable New Columns**: Columns added to existing tables (`users.platform_role`, `users.suspended_at`, `activities.workspace_id`) are created as `nullable` so existing records remain valid without alteration.
3. **New SaaS Isolation Tables**:
   - `saas_plans`: Catalog of pricing plans and entitlements.
   - `saas_subscriptions`: Commercial subscription states (`active`, `trialing`, `past_due`, `canceled`).
   - `feature_flags`: Dynamic feature flags and targeting rules.
   - `security_events`: SIEM audit trail for logins, privilege escalations, and suspensions.

### 4.2 Automated Data Backfill Migration

When running `php artisan migrate` on an existing single-tenant database:

```mermaid
flowchart TD
    StartMigration["1. Run Migration: 2026_09_19_000001_create_platform_control_plane_tables"]
    CreateTables["2. Create SaaS Tables (plans, subscriptions, feature_flags, security_events)"]
    EnsureDefaultOrg["3. Ensure Default Anchor Org Exists (NPT-OPS-01: Npontu Technologies)"]
    EnsureDefaultWs["4. Ensure Default Workspace Exists (Primary Operations)"]
    BackfillUsers["5. Associate Legacy Users with NPT-OPS-01 via workspace_memberships"]
    BackfillActivities["6. Tag Existing Activities with Default Workspace ID"]
    SeedPlans["7. Seed Standard SaaS Plans (Free, Team, Enterprise)"]
    AttachSubscription["8. Provision Enterprise Subscription to Anchor Org"]
    MigrationComplete["9. Verification & Schema Validation Complete"]

    StartMigration --> CreateTables
    CreateTables --> EnsureDefaultOrg
    EnsureDefaultOrg --> EnsureDefaultWs
    EnsureDefaultWs --> BackfillUsers
    BackfillUsers --> BackfillActivities
    BackfillActivities --> SeedPlans
    SeedPlans --> AttachSubscription
    AttachSubscription --> MigrationComplete
```

### 4.3 Data Migration Script & Seeder
The automated seeder `PlatformControlPlaneSeeder` carries out the backfill:
```bash
# In production migration:
php artisan migrate --force
php artisan db:seed --class=PlatformControlPlaneSeeder --force
```

This guarantees that:
- Every existing user belongs to `NPT-OPS-01`.
- Every existing activity remains visible in the primary workspace.
- The default super admin (`opsora_superadmin@opsora.internal`) is provisioned for Platform Control Plane access.

---

## 5. Mobile Client Compatibility & Tenant Context

### 5.1 Dynamic Ingress Configuration
The Flutter mobile application (`npontu_sre_mobile`) contains a built-in server switcher on the login screen supporting:
1. **Production SaaS**: `https://opsora-saas.onrender.com/api/v1`
2. **Legacy Production**: `https://npontu-support-tracker.onrender.com/api/v1`
3. **Local Development**: `http://127.0.0.1:8000/api/v1`
4. **Android Emulator**: `http://10.0.2.2:8000/api/v1`

### 5.2 Mobile Payload Schema
When an operator logs in, `/api/v1/me` and `/api/v1/workspaces` return:
```json
{
  "user": {
    "id": 1,
    "name": "Kwame Mensah",
    "email": "admin@npontu.local",
    "role": "admin",
    "grade": "L5",
    "organization": {
      "name": "Npontu Technologies",
      "company_code": "NPT-OPS-01",
      "tier": "enterprise",
      "plan_name": "Enterprise Dedicated"
    },
    "current_workspace": {
      "id": 1,
      "name": "Primary Operations Cockpit"
    }
  }
}
```

This ensures the mobile application renders:
- The **SaaS Tenant & Workspace Card** in the mobile navigation drawer.
- The **Active Workspace Pill** in the AppBar with single-tap switching.
- Company Code and Plan Tier badges in the User Profile sheet.

---

## 6. Rollback & Disaster Recovery Plan

If an issue arises during staging or production pilot deployment:

| Scenario | Recovery Action | Time to Restore |
|---|---|---|
| **SaaS Deployment Fault** | Revert DNS or proxy to route all traffic to `legacy/single-tenant` URL. | `< 2 minutes` |
| **Database Migration Rollback** | Execute `php artisan migrate:rollback --step=1`. All SaaS tables are cleanly dropped via their `down()` methods without touching legacy tables. | `< 30 seconds` |
| **Mobile App Network Issue** | Tap server badge on Mobile Login screen and switch server endpoint back to Legacy URL. | Immediate (0 downtime) |

---

## 7. Verification Checklist

- [x] `main` / `legacy/single-tenant` branch preserved and tagged.
- [x] `feat/opsora-saas` contains all SaaS control plane and multi-tenant logic.
- [x] All database migrations implement complete, reversible `down()` methods.
- [x] Cross-database compatibility verified for SQLite (local/CI) and MySQL (production).
- [x] Mobile API endpoints return organization, company code, tier, and workspace metadata.
- [x] Flutter mobile drawer and dashboard render active SaaS tenant and workspace.
- [x] Automated test suite (100+ tests) passing with 100% success rate.
