# Opsora Resource Ownership Matrix

> **Status:** IMPLEMENTED  
> **Last Verified:** 2026-09-18

This document defines explicit ownership, foreign key constraints, and cascading deletion behavior for every model in the Opsora platform.

---

## 1. Ownership & Deletion Semantics

| Resource Entity | Owning Entity | Foreign Key Column | DB Cascade Behavior | Authorization Policy |
|---|---|---|---|---|
| `organizations` | Platform | None | `softDeletes` | `OrganizationPolicy` |
| `organization_applications` | Applicant User | `applicant_user_id` | `cascadeOnDelete` | Platform Admin only |
| `organization_memberships` | Organization & User | `organization_id`, `user_id` | `cascadeOnDelete` | Org Owner / Admin |
| `workspaces` | Organization / User | `organization_id`, `owner_user_id` | `cascadeOnDelete` / `nullOnDelete` | Workspace Admin |
| `workspace_memberships` | Workspace & User | `workspace_id`, `user_id` | `cascadeOnDelete` | Workspace Admin |
| `activities` | Workspace | `workspace_id` | `nullOnDelete` | `ActivityPolicy` |
| `activity_logs` | Workspace & Activity | `workspace_id`, `activity_id` | `cascadeOnDelete` | Read-only / Immutable |
| `shift_handovers` | Workspace | `workspace_id` | `nullOnDelete` | Shift Lead / Supervisor |
| `conversations` | Workspace | `workspace_id` | `nullOnDelete` | Channel Member |
| `operational_notifications` | Workspace & User | `workspace_id`, `user_id` | `cascadeOnDelete` | Recipient User |
| `audit_logs` | Workspace & Actor | `workspace_id`, `actor_id` | `nullOnDelete` | Immutable Append-Only |
