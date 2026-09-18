# Opsora Platform — Organizations & Onboarding Lifecycle

> **Status:** IMPLEMENTED  
> **Last Verified:** 2026-09-18

An **Organization** in Opsora represents an enterprise customer, company, department, or managed service provider.

---

## 1. Registration & Approval Lifecycle

1. **Application Submission**: An authenticated user submits `POST /organizations/apply` with organization details, desired plan, and target deployment model.
2. **Automated Risk Scoring**: `ApplyOrganizationAction` evaluates applicant domain verification, plan tier, and deployment topology.
   - Low-risk standard teams (`risk_score <= 20`) are auto-approved immediately.
   - Dedicated managed or customer-hosted requests (`risk_score > 20`) enter the manual review queue.
3. **Administrator Review**: Platform administrators access `/admin/organizations/applications` to inspect risk indicators and approve or reject applications.
4. **Tenant Provisioning**: Upon approval, the organization and its primary workspace are provisioned, assigning the applicant as owner.
5. **Unique Company Code**: Every organization receives a unique code (e.g. `OPS-ACME44`) allowing employees to join instantly.
