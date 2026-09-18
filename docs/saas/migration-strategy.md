# Opsora SaaS Transformation — Legacy Migration Strategy

> **Status**: Approved Migration Runbook (Stage 15)  
> **Source Environment**: Legacy Single-Tenant Npontu SRE Cockpit  
> **Destination Environment**: Opsora Multi-Tenant SaaS Platform

---

## 1. Zero-Data-Loss Migration Pipeline

```
[ 1. Read-Only Snapshot ]
         │
         ▼
[ 2. Pre-Flight Inspection & Integrity Check ]
         │
         ▼
[ 3. Mapping Configuration ]
   ├── Create Tenant: "Npontu Technologies (Legacy Internal SRE)"
   ├── Create Workspace: "Primary Operations" (UUID mapped)
   ├── Map Legacy Users -> OrganizationMemberships + WorkspaceMemberships
   └── Preserve all IDs via explicit backfill mapping table
         │
         ▼
[ 4. Dry-Run Validation (In-Memory / Staging DB) ]
         │
         ▼
[ 5. Production Delta Backfill ]
   ├── Attach `workspace_id = 1` to all legacy `activities`
   ├── Attach `workspace_id = 1` to all legacy `activity_logs`
   ├── Attach `workspace_id = 1` to all legacy `shift_handovers`
   ├── Attach `workspace_id = 1` to all legacy `conversations`
   └── Attach `workspace_id = 1` to all legacy `audit_logs`
         │
         ▼
[ 6. Post-Migration Reconciliation & Hash Verification ]
         │
         ▼
[ 7. Switch Traffic & Keep Legacy Database Dump Frozen ]
```

---

## 2. Integrity Verification & Reconciliation Checklist

1. **Activity Count Verification**:
   - `SELECT COUNT(*) FROM activities WHERE workspace_id = 1;` must match total legacy row count exactly.
2. **Audit Trail Verification**:
   - Every historical `audit_logs` row maintains its immutable snapshot and original actor ID.
3. **Rollback Safety**:
   - If unexpected issues arise, reverting the branch or rolling back the multi-tenancy migrations drops `workspace_id` foreign keys and restores single-tenant operation without data truncation.
