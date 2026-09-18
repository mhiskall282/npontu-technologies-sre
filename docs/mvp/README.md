# Opsora SRE Platform — MVP Testing Package

Welcome to the **Opsora SRE Platform MVP Pilot Testing Suite**. This directory contains the complete operational, verification, and testing documentation designed for internal QA engineers, site reliability engineers, operations leads, and pilot enterprise evaluators.

---

## 📂 Package Contents

| Document | Description | Target Audience |
| :--- | :--- | :--- |
| [**`test-plan.md`**](./test-plan.md) | High-level testing strategy, test phases, scope, risk analysis, and execution schedule | QA Leads, SRE Architects, Evaluators |
| [**`test-cases.md`**](./test-cases.md) | Detailed step-by-step test cases covering Web, API, and Mobile workflows with expected results | QA Testers, Pilot Engineers |
| [**`acceptance-criteria.md`**](./acceptance-criteria.md) | Formal MVP exit criteria, business rules, and non-negotiable quality gates | Product Managers, Engineering Leads |
| [**`test-environment.md`**](./test-environment.md) | Environment setup, test personas, seeded organizations, workspaces, and credentials | All Testers, Pilot Onboarding Engineers |
| [**`bug-reporting.md`**](./bug-reporting.md) | Standardized bug submission template, severity matrix, triage protocol, and SLA | Testers, Developers, Support |
| [**`pilot-onboarding.md`**](./pilot-onboarding.md) | Step-by-step pilot customer onboarding guide from signup to shift handover execution | Pilot Customers, Org Admins |
| [**`release-checklist.md`**](./release-checklist.md) | Final release sign-off checklist for Production Readiness and MVP launch | Release Engineers, DevSecOps |

---

## 🎯 MVP Scope & Focus Areas

The Minimum Viable Product (MVP) covers the end-to-end operational lifecycle for SRE shift teams:

1. **Identity & Tenant Isolation**: Multi-tenant organizations (`Organization`), isolated workspaces (`Workspace`), company codes, role-based access control (`Admin`, `Lead`, `Engineer`), and session/Sanctum authentication.
2. **Shift Operations & Checklists**: Activity logging, categorized operational checks, priority tagging, status state machines, and mandatory resolution remarks.
3. **Shift Handovers**: Outgoing engineer sign-off, outgoing shift notes, uncompleted item roll-over, and incoming engineer digital counter-signature.
4. **Diagnostic Health Telemetry**: Live ping checks, API health probes, MySQL database metrics, and server load monitoring.
5. **Real-time Incident Escalation**: Incident creation, severity assignment (`P1` to `P4`), responder assignments, and operational notes.
6. **Immutable Audit Trail**: Strict logging of every mutating action with actor snapshot, event name, old/new diffs, and client IP.
7. **Cross-Platform Mobile App (`npontu_sre_mobile`)**: Native Flutter mobile app providing on-the-go shift management, push alerts, biometric login, and offline-resilient cache.

---

## 🧪 Test Personas at a Glance

All seeded pilot accounts use the default password: **`password`** in development and testing sandbox environments:

| Persona | Email | System Role | Organization | Primary Workspace |
| :--- | :--- | :--- | :--- | :--- |
| **John Okyere** | `john.okyere@npontu.com` | `admin` (System Administrator) | Opsora SRE (Legacy Npontu) | Primary SRE Operations |
| **Abena Owusu** | `abena.owusu@npontu.com` | `lead` (Operations Lead) | Opsora SRE (Legacy Npontu) | Primary SRE Operations |
| **Kofi Asante** | `kofi.asante@npontu.com` | `engineer` (L1 SRE Engineer) | Opsora SRE (Legacy Npontu) | Primary SRE Operations |
| **Alice (Tenant B)** | `alice@tenantb.test` | `admin` (Tenant B Admin) | Tenant B Operations | Tenant B Core Workspace |
| **Bob (Tenant B)** | `bob@tenantb.test` | `engineer` (Tenant B Engineer) | Tenant B Operations | Tenant B Core Workspace |

*(See [`test-environment.md`](./test-environment.md) for full credential and isolation instructions).*
