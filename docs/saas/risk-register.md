# Opsora SaaS Transformation — Risk Register & Mitigation Strategy

> **Stage**: Stage 0 (Safety Baseline & Risk Assessment)  
> **Target Platform**: Opsora Multi-Tenant SaaS Engine  
> **Status**: Active & Managed

---

## Risk Matrix Summary

| ID | Risk Description | Severity | Likelihood | Impact Area | Mitigation Strategy | Status |
|---|---|---|---|---|---|---|
| **RSK-01** | Cross-tenant data exposure via unscoped Eloquent queries | Critical | High | Data Privacy / Compliance | Introduce global `TenantScope` and mandatory `workspace_id` foreign keys on all domain entities. Enforce server-side authorization checks. | Mitigated by Design |
| **RSK-02** | Breaking existing deployed Npontu environment during migration | High | Medium | Production Availability | Isolate legacy data in dedicated `npontu` organization/workspace with immutable backwards compatibility. Work on isolated feature branch `feat/opsora-saas`. | Active Control |
| **RSK-03** | Existing mobile app (`npontu_sre_mobile`) session invalidation | High | Medium | Mobile Operations | Implement workspace resolution fallback where legacy single-tenant users default to their primary organization workspace seamlessly. | Planned |
| **RSK-04** | IDOR vulnerability on multi-tenant API endpoints | High | High | API Security | Enforce workspace membership verification in middleware (`ValidateWorkspaceContext`) before controller dispatch. Never accept tenant ID from client payload. | Mitigated by Design |
| **RSK-05** | Real-time chat & notification cross-tenant channel broadcast leakage | High | Medium | Comms Security | Prefix all private Livewire / WebSocket broadcast channels with hashed workspace identifier (e.g. `workspace.{id}.conversation.{id}`). | Planned |
| **RSK-06** | Export data leakage across organizational boundaries in CSV/PDF reports | High | Low | Reporting | Hardcode workspace filter inside `ReportingService::export()` so no query can execute without explicit workspace binding. | Planned |
| **RSK-07** | Performance degradation with multi-tenant index bloat | Medium | Medium | Database | Composite indexing strategy: `INDEX (workspace_id, date, status)` on high-volume tables (`activities`, `activity_logs`). | Planned |
| **RSK-08** | Incomplete migration rollback leading to database inconsistency | High | Low | Migrations | Enforce strict `down()` methods on every new SaaS migration, audited via automated rollback testing in CI. | Enforced |
| **RSK-09** | Unauthorized organization registration abuse / spam | Medium | High | Trust & Safety | Configurable approval pipeline: automatic approval for verified corporate domains; manual review queue for unverified or high-risk entities. | Planned |
| **RSK-10** | Mobile layout regressions on multi-tenant workspace switcher | Low | Medium | Mobile UX | Implement clean modal sheet / drawer for workspace switching adhering to responsive 360px viewport discipline. | Planned |

---

## Detailed Risk Mitigations

### RSK-01: Cross-Tenant Data Exposure
- **Mechanism**: If a controller queries `Activity::where('date', $date)->get()` without scoping, it returns activities across all tenants.
- **Enforcement**:
  1. All tenant-owned models implement `BelongsToWorkspace` trait.
  2. The trait registers an automatic `TenantScope` applying `where('workspace_id', TenantContext::id())`.
  3. Form Requests validate that route parameter entities belong to current tenant.
  4. Automated Pest tests assert that Tenant A receives HTTP 404/403 when requesting Tenant B's UUID or ID.

### RSK-02: Preserving Legacy Npontu Environment
- **Mechanism**: The initial deployment running on Render must not suffer database corruption or authentication failures.
- **Enforcement**:
  1. A dedicated baseline seeder provisions Organization `#1` as "Npontu Technologies (Internal SRE)".
  2. All pre-existing database records are backfilled to Organization `#1` and Workspace `#1`.
  3. Pre-existing test credentials (`admin@npontu.local`, etc.) are pre-attached to Workspace `#1`.
  4. All existing tests run and pass against Workspace `#1`.
