# Opsora SaaS Transformation — Tenant Security Model

> **Status**: Approved Security Mandate (Stage 4)  
> **Standard**: Zero-Trust Tenant Isolation & Defense-in-Depth

---

## 1. Core Tenant Security Principles

1. **Explicit Tenancy**: Every operational entity must know its workspace and organization owner.
2. **Fail-Closed Isolation**: If workspace context cannot be positively established and verified against user membership, access is unconditionally denied (HTTP 403 / 404).
3. **Immutability of Audit Trails**: Tenant-level audit logs must be append-only. Neither organization admins nor workspace owners can alter or prune security audit events.
4. **Least Privilege Platform Administration**: Platform administrators can manage tenants and review applications, but cannot read customer operational data (checklists, handovers, chat messages) without explicit, auditable elevated session escalation.

---

## 2. Automated Isolation Test Plan

To prove tenant isolation mathematically and empirically, the following automated Pest tests are established:

| Test Case | Scenario | Expected Behavior |
|---|---|---|
| `test_cross_tenant_activity_read` | User A (Workspace 1) requests Activity belonging to Workspace 2 | HTTP 404 Not Found |
| `test_cross_tenant_activity_mutation` | User A attempts `PUT /api/v1/activities/{id}` on Workspace 2 activity | HTTP 404 Not Found |
| `test_cross_tenant_status_checkoff` | User A attempts checkoff on Workspace 2 activity | HTTP 404 Not Found |
| `test_cross_tenant_handover_acceptance` | Lead A attempts to sign on to Workspace 2 shift handover | HTTP 404 Not Found |
| `test_cross_tenant_chat_participation` | User A attempts to subscribe or post to Workspace 2 war room | HTTP 403 Forbidden |
| `test_cross_tenant_report_export` | User A generates CSV report | Report contains 0 records from Workspace 2 |
| `test_suspended_tenant_lockout` | User attempts access while Workspace or Org status is `suspended` | HTTP 403 Forbidden with `TENANT_SUSPENDED` code |
| `test_unauthorized_workspace_switching` | User attempts to switch to a workspace where they lack membership | HTTP 403 Forbidden |
| `test_background_job_tenant_isolation` | Job dispatched with Workspace 1 context executes | Modifies only Workspace 1 entities |

---

## 3. Threat Matrix & Countermeasures

### 3.1 Tenant Data Leakage (OWASP Multi-Tenancy Top 1)
- **Attack**: Malicious user guesses or iterates entity IDs (`/activities/1042`).
- **Defense**: Global `TenantScope` executes at Eloquent query construction time. Entities belonging to other workspaces do not exist in the query result set.

### 3.2 Workspace Impersonation & Spoofing
- **Attack**: Malicious user sends spoofed headers (`X-Workspace-ID: 2`).
- **Defense**: Custom headers are rejected. Workspace is derived strictly from verified route subdomain/slug and authenticated session membership.

### 3.3 Privileged Platform Administrator Abuse
- **Attack**: Internal platform support staff reads confidential enterprise operational war rooms.
- **Defense**: `customer_data.access` permission required. Access triggers mandatory `admin_accessed_customer_data` audit event recording IP, justification note, and exact records viewed.
