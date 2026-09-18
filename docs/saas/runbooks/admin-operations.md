# Opsora SaaS — Platform Administrator Operations Runbook

## Overview
This runbook provides administrative procedures for managing cross-tenant operations, reviewing organization onboarding requests, managing suspension/reactivation, and auditing system events.

---

## 1. Organization Application Review Queue

### Accessing the Queue
1. Log in with an account holding `role = 'admin'` or `role = 'owner'`.
2. Navigate to `/admin/organizations/applications` (or click **Org Applications** in the desktop sidebar).
3. The queue displays all pending organization registrations, sorted by risk score.

### Evaluating Applications
- **Risk Score Assessment**:
  - `0 - 20`: Standard low-risk application (company email matches domain, standard shared SaaS requested).
  - `21 - 50`: Intermediate risk (generic email provider, high initial user count).
  - `> 50`: High risk (flagged domain, dedicated VPC requested, conflicting entity names).
- **Decision Actions**:
  - **Approve**: Automatically creates the `organizations` record, provisions its primary `workspaces` entry, enrolls the applicant as owner, and generates an immutable audit record.
  - **Reject**: Marks status as `rejected` and records mandatory `rejection_reason` for compliance tracking.

---

## 2. Suspending and Reactivating Workspaces

### Suspending a Workspace (e.g. Non-Payment or Security Incident)
```sql
UPDATE workspaces 
SET status = 'suspended' 
WHERE id = :workspace_id;
```
When suspended:
- `ResolveTenantContext` middleware blocks all member requests with `403 Forbidden: Active workspace is suspended`.
- Members cannot perform CRUD actions, export data, or access real-time chat.
- Control plane platform administrators can still access records via `TenantContext::withoutTenancy(callable)`.

### Reactivating a Workspace:
```sql
UPDATE workspaces 
SET status = 'active' 
WHERE id = :workspace_id;
```

---

## 3. Auditing Cross-Tenant Administrative Actions

All platform-level administrative interventions must be logged in `audit_logs`:
```bash
# Query recent administrative actions across all workspaces
php artisan tinker --execute="
    \App\Services\TenantContext::withoutTenancy(function () {
        return \App\Models\AuditLog::latest()->take(10)->get(['id', 'actor_name', 'event', 'subject_type', 'created_at']);
    });
"
```
