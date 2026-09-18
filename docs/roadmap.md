# Opsora Platform Roadmap

> **Status:** AUTHORITATIVE  
> **Last Updated:** 2026-09-18

This document categorizes all features of the Opsora platform into four explicit statuses: **Implemented**, **Partially Implemented**, **Planned**, and **Future**.

---

## 1. Implemented (In Production & Tested)

### Multi-Tenancy & Organizations
- [x] Multi-tenant organization model with slug and auto-generated `company_code`.
- [x] Multi-workspace model supporting both Organization workspaces and Personal user sandboxes.
- [x] `TenantScope` and `BelongsToWorkspace` trait strictly isolating queries across all operational tables.
- [x] Dynamic context resolution middleware (`ResolveTenantContext`) supporting session and `X-Workspace-Id` header.
- [x] Self-service organization registration form (`/organizations/apply`) with automatic risk score evaluation.
- [x] Platform administrator review queue (`/admin/organizations/applications`) with approve/reject workflow.
- [x] Fast onboarding via company code (`/workspaces/join` and `POST /api/v1/workspaces/join`).
- [x] Workspace switching with strict membership authorization and suspended status guards.
- [x] Legacy environment data migration command (`php artisan opsora:migrate-legacy`) with `--dry-run` and `--rollback`.

### Operational SRE Workflows
- [x] Daily Shift Activity checklist board with recurrence, priority, and assigned engineer filters.
- [x] Two-Way Shift Handover Protocol with Outgoing Sign-Off, Incoming Acceptance, and Forensic Seal.
- [x] Incident escalation flagging with ticket reference links and resolution remarks.
- [x] Operational team messaging channels and 1-on-1 direct messaging with image and PDF attachments.
- [x] 1-Click email reply link bridge and inbound webhook processor.
- [x] Compliance reporting engine with date-range queries and streaming CSV exports.
- [x] Public diagnostic health endpoint (`/health`) and telemetry metrics stream (`/health/telemetry`).

### Mobile Companion (Flutter)
- [x] Multi-workspace discovery and 1-tap switching bottom sheet.
- [x] Company code join support with automatic tenant activation.
- [x] `X-Workspace-Id` HTTP client interceptor with persistent secure token storage.
- [x] Daily shift activities feed and task status updates.
- [x] 25 unit and widget tests passing with zero failures.

---

## 2. Partially Implemented (Architecture Approved — Active Development)

- [ ] **Subdomain-based routing**: Data model supports `subdomain` column on `workspaces`; DNS wildcard routing in progress.
- [ ] **Custom domains**: Data model supports `custom_domain`; automated TLS certificate issuance planned for Phase 2.
- [ ] **Data Residency zones**: Nominations captured (`af-south`, `eu-west`, `us-east`); multi-region DB replication in deployment staging.

---

## 3. Planned (Architecture Specified)

- [ ] **Subscription & Billing Automation**: Stripe / Paystack payment gateway adapters (domain model documented in `docs/billing/billing-domain-design.md`).
- [ ] **Offline License Keys**: Cryptographically signed license file generator for on-premise deployments.
- [ ] **Push Notification Bridge**: Firebase Cloud Messaging (FCM) integration for mobile push alerts.
- [ ] **Enterprise SSO / SAML / OIDC**: WorkOS or native SAML 2.0 identity provider connectors.

---

## 4. Future (Research & Ideation)

- [ ] **Automated Remediation Webhooks**: Auto-trigger Ansible or Kubernetes runbooks from incident alerts.
- [ ] **SLO & Error Budget Burn Rates**: Historical reliability analytics calculated from synthetic probes.
- [ ] **Enterprise White-Label Mobile App Bundles**: Automated CI flavor generation for branded client apps.
