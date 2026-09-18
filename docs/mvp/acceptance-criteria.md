# Opsora SRE Platform — MVP Acceptance Criteria

This document defines the definitive **Acceptance Criteria (AC)** and Quality Gates required for the **Opsora SRE SaaS Platform MVP** release sign-off.

---

## Gate 1: Multi-Tenancy & Data Isolation

- [x] **AC-TENANT-01**: Every operational entity (`Activity`, `ShiftHandover`, `Incident`, `HealthCheck`, `AuditLog`) MUST be foreign-keyed to an `organization_id` and `workspace_id`.
- [x] **AC-TENANT-02**: All Eloquent queries executed within tenant contexts MUST be scoped through global query scopes or explicit tenant bounds; no record may be accessed without membership verification.
- [x] **AC-TENANT-03**: Cross-tenant direct object references (IDOR) MUST produce `403 Forbidden` or `404 Not Found` without revealing whether the record exists in another tenant.
- [x] **AC-TENANT-04**: The REST API MUST require and validate the `X-Workspace-Id` header against the authenticated user's organization memberships.

---

## Gate 2: Authentication & Authorization (RBAC)

- [x] **AC-AUTH-01**: Web authentication uses secure HTTP-only session cookies with CSRF protection; mobile and REST API clients authenticate via Laravel Sanctum Bearer tokens.
- [x] **AC-AUTH-02**: Three standard roles are supported and enforced via Laravel Policies:
  - **`admin`**: User management, workspace settings, role modifications, and full audit log inspection.
  - **`lead`**: Shift handover counter-signatures, incident triage, and team reporting.
  - **`engineer`**: Personal checklist execution, status updates, and outgoing handover drafting.
- [x] **AC-AUTH-03**: Password storage MUST use Bcrypt with a work factor of 12; failed logins MUST trigger IP and account throttling.

---

## Gate 3: SRE Shift Operations & Activities

- [x] **AC-ACT-01**: Shift activities MUST record title, description, category, priority (`P1` to `P4`), status, assignee, and workspace.
- [x] **AC-ACT-02**: Changing an activity's status to `Done` or `Blocked` MUST strictly require a non-empty resolution remark.
- [x] **AC-ACT-03**: Activities table MUST support real-time Livewire filtering by priority, category, assignee, and search query.

---

## Gate 4: Shift Handover Protocol

- [x] **AC-HAND-01**: Outgoing engineers MUST be able to bundle all uncompleted shift activities into an automated handover report.
- [x] **AC-HAND-02**: An outgoing handover MUST capture digital sign-off metadata (actor, timestamp, client IP).
- [x] **AC-HAND-03**: The incoming shift lead MUST review and counter-sign the handover before it transitions to `Completed`.
- [x] **AC-HAND-04**: Incomplete checklist items roll over into the next shift's active backlog.

---

## Gate 5: Immutable Audit Logging

- [x] **AC-AUD-01**: Every create, update, status change, and delete action MUST dispatch an immutable audit trail record.
- [x] **AC-AUD-02**: Audit records MUST capture:
  - `actor_id` and denormalized `actor_name`
  - `subject_type` and `subject_id` (polymorphic relation)
  - `event` string (`created`, `updated`, `status_changed`, `deleted`)
  - `old_values` and `new_values` JSON diffs
  - Client IP address and UTC timestamp
- [x] **AC-AUD-03**: Audit logs are read-only and restricted to users with the `admin` role or `view_audit_logs` permission.

---

## Gate 6: Mobile Client (`npontu_sre_mobile`)

- [x] **AC-MOB-01**: Startup sequence MUST transition cleanly through splash, local cache hydration, and authentication check without white screens or login flashes.
- [x] **AC-MOB-02**: Mobile client MUST support offline cached viewing of shift checklists with a visible offline indicator.
- [x] **AC-MOB-03**: Pull-to-refresh MUST synchronize remote state; network dropouts MUST present actionable retry buttons without fatal exceptions.

---

## Gate 7: Automated Testing & Code Quality

- [x] **AC-QA-01**: 100% of Pest backend automated feature and unit tests must pass.
- [x] **AC-QA-02**: 100% of Flutter mobile widget and unit tests must pass.
- [x] **AC-QA-03**: Zero lint errors reported by Laravel Pint (`vendor/bin/pint --test`).
- [x] **AC-QA-04**: Every database migration MUST include a working `down()` method.
