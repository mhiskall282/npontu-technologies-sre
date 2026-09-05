# Project Submission Report — Npontu Support Activity Tracker

**Application Name**: Support Activity Tracker — Npontu Technologies  
**Developer**: John Okyere (`hello@johnokyere.xyz`)  
**Live Custom Domain**: [https://npontu-tracker.johnokyere.xyz](https://npontu-tracker.johnokyere.xyz)  
**Render Endpoint**: [https://npontu-support-tracker.onrender.com](https://npontu-support-tracker.onrender.com)  
**GitHub Repository**: [https://github.com/mhiskall282/npontu-technologies-sre](https://github.com/mhiskall282/npontu-technologies-sre)  
**Date of Submission**: 28th July 2026  

---

## 1. Project Overview & Business Value

The **Support Activity Tracker** is a production-grade operations management system designed specifically for System Reliability Engineers (SRE) and Support Operations Teams at Npontu Technologies. 

It solves the operational challenge of fragmented shift handovers by offering:
- **Real-Time Daily Shift Board**: A reactive checklist where operators update operational check statuses (`Done` / `Pending`) with required remarks.
- **Immutable Security Audit Trail**: Every user-facing mutation logs actor details, denormalized snapshots, server-captured IP addresses, and JSON state diffs.
- **SRE Monitoring Console**: Live audit log stream, stale check alert banners, category progress bars, and 7-day completion trend charts.
- **Multi-Format Reporting Suite**: Date-range query engine with interactive Chart.js visualizations, CSV export, standalone A4-landscape PDF print layout, and email report dispatch.
- **User Management & Self-Service Settings**: Role-based access control (Admin, Lead, Agent), password reset via tokenized emails, and account settings.

---

## 2. Default Access Credentials

For testing and evaluation, the production database is seeded with the following default role accounts:

| Role | Name | Email | Password | Access Privileges |
|---|---|---|---|---|
| **Administrator** | Kwame Mensah | `admin@npontu.local` | `password` | Full system access, user management, activity creation, audit monitoring |
| **Team Lead** | Abena Owusu | `lead@npontu.local` | `password` | Activity management, shift board oversight, reports & SRE monitoring |
| **Support Agent** | Kofi Asante | `agent@npontu.local` | `password` | Shift checklist status updates, remark logging, personal settings |

---

## 3. Architecture & Technical Design

The application is engineered using an enterprise **4-Tier Architecture**:

1. **Tier 1: Presentation (UI)**: Responsive Blade views, Livewire 3 real-time polling, Tailwind CSS v4 design system, and custom animated splash screen.
2. **Tier 2: Application / HTTP**: Middleware guard stack (`auth`, `EnsureRole`, `VerifyCsrfToken`), Form Request validation, and Controllers.
3. **Tier 3: Business Logic & Services**: Single-responsibility Action classes, `AuditService`, `ReportingService`, and Notifications (`WelcomeNotification`, `AdminPasswordResetNotification`).
4. **Tier 4: Data Persistence**: Eloquent ORM Models with soft-deletes, polymorphic audit morphs, and Render Free PostgreSQL database.

---

## 4. Key Endpoints & Functionality Summary

| Endpoint | HTTP Method | Protected Role | Key Features |
|---|---|---|---|
| `/` | `GET` | Public | High-impact SRE landing page: brand hero, 6 capability pillars, handover lifecycle, live telemetry, and test accounts |
| `/login` | `GET / POST` | Guest | Branded login page with session expired banner, error alerts, and 1-click test credentials helper |
| `/daily` | `GET` | Authenticated | Real-time shift board checklist with Livewire reactivity, task delegation, and handover sign-off/sign-on |
| `/messages` | `GET` | Authenticated | SRE team comms console: 1-on-1 direct messaging, team shift channels, incident war rooms, and @mention email alerts |
| `/reports` | `GET` | Lead / Admin | Query engine, Chart.js visualisations, CSV & PDF export |
| `/reports/handovers` | `GET` | Lead / Admin | Shift handover audit report with acceptance rate KPIs and CSV export |
| `/reports/timelines` | `GET` | Lead / Admin | Operator active duty hours & shift timeline analytics with CSV export |
| `/monitoring` | `GET` | Lead / Admin | Live SRE audit log stream, stale check alerts, & completion trend charts |
| `/health` | `GET` | Public | Interactive SRE System Health dashboard & JSON status API for monitors |
| `/health/telemetry` | `GET` | Public | Real-time performance telemetry JSON stream (DB latency, cache, memory) |
| `/settings` | `GET / PUT` | Authenticated | Profile updates, password changes, and privilege access card |
| `/admin/users` | `GET / POST / DELETE` | Admin | Team member CRUD with 9 granular privileges checkboxes and L1-L5 SRE technical grades |
| `/admin/activities` | `GET / POST / PUT / DELETE` | Lead / Admin | Activity template checklist management with priority, SLA, and pinned status |

---

## 5. Quality Assurance & Verification

- **Automated Tests**: **55 Pest feature & unit tests passing** (280 assertions covering Public SRE Landing Page, Authentication & Session Expiry, Custom Branded SRE Error Pages, Activity CRUD, Status Flows, Operational Comms, Shift Handover Handshake, SRE Enterprise Features, Task Assignment, Reporting, and System Health Diagnostics).
- **Code Standards**: PSR-12 strictly formatted using Laravel Pint (0 issues).
- **UI & Layout**: Responsive Left Sidebar Navigation layout (Npontu brand tokens `#1B6B3A`, `#F5C518`, `#0F1A14`) with desktop sticky sidebar, mobile drawer, live UTC clock, and targeted loading state synchronization.
- **Security & Error Resilience**: Branded error pages (419, 404, 403, 500, 503), Livewire 419 session expiration interceptor with smooth redirect to `/login?expired=1`, operator sign-in error handling with 1-click test credentials, Force HTTPS scheme, trusted proxy headers (`X-Forwarded-Proto`), CSRF token protection, polymorphic audit logs with JSON diffs, and bcrypt password hashing.

---

*Report generated automatically for Npontu Technologies Project Review.*
