# Opsora Database — MySQL 8.0 Schema & Migration Index

> **Status:** IMPLEMENTED  
> **Database Engine:** MySQL 8.0+ InnoDB  
> **Charset:** `utf8mb4_unicode_ci`

---

## 1. Table Dictionary

| Table Name | Primary Purpose | Foreign Keys | Tenant Scoped |
|---|---|---|---|
| `users` | Global platform user credentials & roles | None | Identity Plane |
| `organizations` | Customer legal entities & subscription tier | None | Control Plane |
| `organization_applications` | Self-service registration requests & review notes | `applicant_user_id`, `reviewed_by` | Control Plane |
| `organization_memberships` | User-to-Organization relationship | `organization_id`, `user_id` | Control Plane |
| `workspaces` | Operational containers & environments | `organization_id`, `owner_user_id` | Control Plane |
| `workspace_memberships` | User-to-Workspace access permissions | `workspace_id`, `user_id` | Control Plane |
| `activities` | Operational recurring checks & tasks | `workspace_id`, `created_by`, `assigned_to` | Yes (`workspace_id`) |
| `activity_logs` | Checklist status modification audit history | `workspace_id`, `activity_id`, `user_id` | Yes (`workspace_id`) |
| `shift_handovers` | Shift briefing reports & signed custody records | `workspace_id`, `user_id`, `accepted_by_id` | Yes (`workspace_id`) |
| `conversations` | Ops chat channels & direct messaging | `workspace_id`, `user_id` | Yes (`workspace_id`) |
| `operational_notifications` | User alerts & system communications | `workspace_id`, `user_id` | Yes (`workspace_id`) |
| `audit_logs` | Immutable SIEM audit trail | `workspace_id`, `actor_id` | Yes (`workspace_id`) |
| `personal_access_tokens` | Laravel Sanctum API authentication tokens | `tokenable_id` | Identity Plane |

---

## 2. Reversible Migrations List
All 11 migrations in `database/migrations/` strictly implement reversible `down()` methods, ensuring that rollback operations (`php artisan migrate:rollback`) can be executed safely at any time.
