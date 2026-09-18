# Opsora Redesign Planning & Compatibility Constraints

> **Status:** AUTHORITATIVE  
> **Last Verified:** 2026-09-18

Before attempting any UI or architectural redesign of Opsora, review these non-negotiable boundaries:

---

## 1. What Must Remain Unchanged
1. **Brand Colors**: Primary `#1B6B3A`, Accent `#F5C518`, Alert `#E63946`, Background `#08120B`.
2. **Tenant Scoping Engine**: `TenantScope` and `BelongsToWorkspace` must continue wrapping all operational queries.
3. **Immutable Audit Custody**: The `audit_logs` table must never be altered to support update or delete mutations.
4. **Two-Way Handover Contract**: The 4-phase handover state machine must preserve the oncoming lead signature verification step.
5. **API Backward Compatibility**: Mobile endpoints under `/api/v1/` must maintain consistent JSON response formats.

---

## 2. Permitted Redesign Areas
- Enhanced real-time charts in the reporting dashboard.
- Subdomain DNS wildcard automation for customer workspaces.
- Self-service billing integration with Stripe / Paystack adapters.
- Enterprise SSO / SAML integration for large organizations.
