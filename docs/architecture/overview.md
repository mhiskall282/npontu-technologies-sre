# Opsora Architecture — High-Level Overview

> **Status:** IMPLEMENTED  
> **Last Verified:** 2026-09-18

Opsora is designed as a **modular monolith** optimized for Site Reliability Engineering and operational workflow management. It separates control plane concerns (identity, organizations, workspace registry) from execution plane concerns (daily checklists, shift handovers, incident escalations, and war rooms).

---

## 1. High-Level Architectural Layers

```mermaid
graph TD
    ClientWeb[Web Clients: Blade + Livewire 3] --> API[HTTP / Livewire Gateway]
    ClientMobile[Mobile Clients: Flutter iOS & Android] --> API
    
    API --> Middleware[ResolveTenantContext & Auth Guards]
    
    subgraph ControlPlane [Opsora Control Plane]
        Identity[User & Organization Registry]
        Workspaces[Workspace & Membership Registry]
        ApprovalQueue[Self-Service Org Approval Queue]
    end
    
    subgraph ExecutionPlane [Tenant Execution Plane]
        TenantScope[Global TenantScope Isolation]
        DailyBoard[Daily Shift Activities]
        Handovers[Two-Way Custody Handover Engine]
        Chat[Operational War Rooms & Channels]
        AuditTrail[Immutable SIEM Audit Engine]
    end
    
    Middleware --> ControlPlane
    Middleware --> ExecutionPlane
    ExecutionPlane --> DB[(MySQL 8.0+ InnoDB)]
```

---

## 2. Core Architectural Decisions
1. **Modular Monolith**: Single codebase maintaining Laravel backend and Flutter mobile client, avoiding premature microservice complexity while enforcing strict domain boundaries.
2. **Multi-Tenancy via Workspace Context**: Tenant scoping is rooted in `workspaces`. Users belong to Organizations, and Organizations own one or more Workspaces. Users can also have individual personal workspaces.
3. **Database-Level Isolation**: All operational tables (`activities`, `activity_logs`, `shift_handovers`, `conversations`, `operational_notifications`, `audit_logs`) carry a `workspace_id` foreign key with compound indexes.
4. **Append-Only Audit Trail**: State mutations generate immutable audit records that cannot be edited or deleted by any user or administrator.
