# Opsora Multi-Tenancy Architecture & Isolation Model

> **Status:** IMPLEMENTED  
> **Last Verified:** 2026-09-18

Opsora implements a **shared-database, workspace-scoped multi-tenancy model**. This document details the technical implementation guaranteeing zero cross-tenant data leakage.

---

## 1. Domain Entities Hierarchy

```text
User (Global Identity)
 ├── Organization Memberships (role, department, grade)
 │    └── Organization (Legal Entity, Tier, Company Code)
 │         └── Workspaces (Operational Environments)
 │              ├── Workspace Memberships (role: admin, lead, agent, viewer)
 │              └── Operational Data (Activities, Handovers, War Rooms, Audit Logs)
 └── Personal Workspace (is_personal = true)
```

---

## 2. Technical Enforcement Engine

### 1. `BelongsToWorkspace` Trait
Applied to all tenant-scoped operational models (`Activity`, `ActivityLog`, `ShiftHandover`, `Conversation`, `OperationalNotification`, `AuditLog`):
```php
namespace App\Traits;

trait BelongsToWorkspace
{
    public static function bootBelongsToWorkspace(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            if (empty($model->workspace_id) && TenantContext::hasWorkspace()) {
                $model->workspace_id = TenantContext::id();
            }
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
```

### 2. `TenantScope` Global Scope
Automatically intercepts every SQL query executed on tenant models:
```php
namespace App\Scopes;

final class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (TenantContext::hasWorkspace()) {
            $builder->where($model->qualifyColumn('workspace_id'), '=', TenantContext::id());
        }
    }
}
```

### 3. `TenantContext` Scoped Service
Maintains the active workspace in memory for the duration of the request:
- `TenantContext::setWorkspace(Workspace $workspace)`
- `TenantContext::getWorkspace(): ?Workspace`
- `TenantContext::withoutTenancy(callable $callback)`: Safely executes platform administrative tasks or background queue jobs with tenant scoping bypassed and audited.

---

## 3. Cross-Tenant Security Invariants
- **Client Input Disregarded**: The backend never accepts an unchecked `workspace_id` from client payloads.
- **Server-Side Resolution**: Context is strictly derived from the authenticated user's session or verified `X-Workspace-Id` header matching an active `workspace_memberships` record.
- **Suspension Enforcement**: If either a workspace or its parent organization is marked `status = 'suspended'`, `ResolveTenantContext` halts execution with HTTP `403 Forbidden`.
