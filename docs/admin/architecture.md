# Platform Control Plane Architecture

> **Authoritative Specification:** Opsora Platform Control Plane  
> **Status:** Implemented

## Overview

The Opsora Platform Control Plane is architected as an administrative layer directly on top of the Laravel modular monolith. Rather than running a disconnected administrative microservice, the control plane shares the core database, Eloquent models, domain actions, and audit systems while enforcing rigorous physical and logical boundaries.

---

## Architectural Data Flow

```text
Super Administrator / Platform Operator
                 │
                 ▼
       Web UI / Platform REST API
                 │
                 ▼
        [EnsurePlatformAdmin] ──► Checks user->isPlatformAdmin()
        [EnsurePlatformPermission] ──► Checks user->hasPlatformPermission('platform.*')
                 │
                 ▼
     Platform Controllers / API Handlers
                 │
                 ▼
        TenantContext::withoutTenancy()
                 │  (Bypasses TenantScope temporarily for cross-tenant visibility)
                 ▼
    Domain Actions & Application Services
        ├── SuspendOrganizationAction
        ├── ReactivateOrganizationAction
        ├── SuspendUserAction
        ├── ReactivateUserAction
        ├── UpdateSubscriptionAction
        └── PlatformMetricsService
                 │
                 ├──► Emits AuditLog (Write-only compliance record)
                 ├──► Emits SecurityEvent (SIEM threat telemetry)
                 │
                 ▼
      Primary Database & Cache Subsystems
```

---

## Key Boundary Principles

1. **Strict Tenant Isolation on Application Plane**: Normal tenant users only ever access resources where `workspace_id = TenantContext::workspaceId()`. The global query scope `TenantScope` prevents cross-tenant leakage.
2. **Controlled Elevation via `withoutTenancy()`**: Platform controllers execute data queries inside `TenantContext::withoutTenancy(fn() => ...)`. This guarantees cross-tenant visibility only occurs within audited administrative methods.
3. **No `is_admin = true` Antipattern**: Authorization is governed by the typed PHP 8.2+ enum `App\Enums\PlatformRole`, separating tenant workspace roles (`admin`, `lead`, `engineer`) from platform roles (`SuperAdmin`, `PlatformAdmin`, `BillingAdmin`, `SecurityAdmin`, etc.).
4. **Immutable Security Logging**: Privileged mutations automatically record both an `AuditLog` entry (with actor snapshot and attribute diffs) and a `SecurityEvent` entry for SIEM ingestion.
