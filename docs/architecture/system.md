# Opsora System Architecture & Component Mapping

> **Status:** IMPLEMENTED  
> **Last Verified:** 2026-09-18

This document details the data and control flow across the Opsora system from client requests down to the database and background workers.

---

## 1. Request Lifecycle & Tenant Context Binding

```mermaid
sequenceDiagram
    autonumber
    actor User as Operator / Mobile App
    participant Gateway as Nginx / Web Router
    participant Auth as Authenticate Middleware
    participant Tenant as ResolveTenantContext Middleware
    participant Controller as Controller / Livewire
    participant Scope as TenantScope
    participant DB as MySQL Database

    User->>Gateway: Request with Cookie or Bearer Token & X-Workspace-Id
    Gateway->>Auth: Authenticate Credentials
    Auth-->>Gateway: Authenticated User Model
    Gateway->>Tenant: Resolve Active Workspace
    Tenant->>Tenant: Verify User Workspace Membership & Status
    Tenant->>Scope: Bind TenantContext::setWorkspace()
    Gateway->>Controller: Dispatch to Action / Livewire
    Controller->>Scope: Eloquent Model Query (e.g. Activity::all())
    Scope->>DB: Append WHERE workspace_id = ?
    DB-->>Controller: Tenant-Isolated Result Set
    Controller-->>User: HTTP 200 / Rendered Blade View
```

---

## 2. Component Responsibility Matrix

| Subsystem | Primary Namespace | Responsibilities |
|---|---|---|
| **Identity & Access** | `App\Models\User`, `App\Models\Organization` | Account management, global personas, organization ownership |
| **Tenancy Resolution** | `App\Services\TenantContext`, `App\Http\Middleware\ResolveTenantContext` | Session/header workspace extraction, membership enforcement |
| **Operational Board** | `App\Livewire\DailyActivityBoard`, `App\Actions\Activity` | Checklist CRUD, bulk delegation, inline status updates |
| **Shift Custody** | `App\Models\ShiftHandover`, `App\Actions\Handover` | 2-phase shift sign-off, briefing notes, forensic locking |
| **Communications** | `App\Livewire\OperationalChat`, `App\Models\Conversation` | Channel dispatch, direct messages, attachments |
| **Audit Engine** | `App\Services\AuditService`, `App\Models\AuditLog` | Forensic immutable logging with actor snapshots |
| **Health Telemetry** | `App\Http\Controllers\HealthController`, `App\Services\SystemHealthService` | Real-time probes, DB latency metrics, public health endpoint |
