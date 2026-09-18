# Opsora Platform — Delegated Administration & Platform Roles

> **Status:** IMPLEMENTED  
> **Last Verified:** 2026-09-18

Opsora enforces the principle of least privilege using granular role hierarchies across platform, organization, and workspace levels.

---

## 1. Platform-Level Clearance Matrix

| Role | Target Clearance | Key Capabilities | Accessible Interfaces |
|---|---|---|---|
| **Platform Administrator** | Root Clearance | Review & decide on organization applications, suspend/reactivate tenants, inspect forensic logs across all workspaces | `/admin/organizations/applications`, global metrics |
| **Organization Owner** | Enterprise Entity | Manage organization memberships, company code distribution, billing tier, workspace provisioning | `/workspaces`, organization settings |
| **Workspace Admin** | Operational Environment | Create & delegate activities, manage checklists, view audit logs | Daily Board, Activity CRUD |
| **Shift Lead** | Incident Commander | Execute 2-way shift handovers, sign off briefings, flag escalations | Handover view, Daily Board |
| **Support Engineer** | NOC Operator | Resolve assigned checklist tasks, log status remarks, participate in channels | Daily Board, Chat |
