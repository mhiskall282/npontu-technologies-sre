# Opsora Tenant Boundary Map & Data Classification

> **Status:** IMPLEMENTED  
> **Last Verified:** 2026-09-18

This document defines the strict boundary classifications governing what data belongs to the Platform Control Plane, Organizations, Workspaces, or Individual Users.

---

## 1. Data Classification Matrix

| Boundary Layer | Entities & Data | Scope & Accessibility | Cross-Tenant Policy |
|---|---|---|---|
| **Platform Control Plane** | `users` (credentials), `organization_applications`, `organizations`, global system health metrics | Accessible only by Platform Administrators holding root clearance | Strictly isolated from tenant users; audited in SIEM |
| **Organization Layer** | `organization_memberships`, company codes, billing tier, deployment model, preferred region | Shared among authorized members of the specific organization | Accessible only to users holding active `organization_memberships` |
| **Workspace Layer** | `workspaces`, `workspace_memberships`, `activities`, `activity_logs`, `shift_handovers`, `conversations`, `operational_notifications`, `audit_logs` | Scoped strictly to the active workspace | Enforced via `TenantScope` & `BelongsToWorkspace`. Never leaked |
| **User Identity Layer** | Personal profiles, personal sandboxes (`workspaces.is_personal = true`), session records | Belongs strictly to the individual user | Unshared unless explicitly granted |

---

## 2. Boundary Leakage Prevention Rules
1. **Reporting & Exports**: CSV and email exports (`/reports`, `/reports/handovers`, `/reports/timelines`) only query records belonging to `TenantContext::id()`.
2. **Operational Chat**: Message dispatch in `OperationalChat` binds channel messages to `workspace_id`. Operators cannot send or receive messages from channels outside their active workspace.
3. **Audit Trails**: While audit records are immutable, queries are scoped to the active workspace unless an authorized platform administrator uses `TenantContext::withoutTenancy()`.
