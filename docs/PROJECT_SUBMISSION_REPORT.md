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
| `/login` | `GET / POST` | Guest | Branded login page with forgot password token link |
| `/daily` | `GET` | Authenticated | Real-time shift board checklist with Livewire reactivity |
| `/reports` | `GET` | Lead / Admin | Query engine, Chart.js visualisations, CSV & PDF export |
| `/monitoring` | `GET` | Lead / Admin | Live SRE audit log stream, stale check alerts & charts |
| `/settings` | `GET / PUT` | Authenticated | Profile updates, password changes, and privilege access card |
| `/admin/users` | `GET / POST / DELETE` | Admin | Team member CRUD, welcome emails & password reset dispatch |
| `/admin/activities` | `GET / POST / PUT / DELETE` | Lead / Admin | Activity template checklist management |
| `/health` | `GET` | Public | JSON health check API (`status: ok`, DB check, system time) |

---

## 5. Quality Assurance & Verification

- **Automated Tests**: 14 Pest feature & unit tests passing (39 assertions covering Auth, CRUD, Status flows, and Date-range Reporting).
- **Code Standards**: PSR-12 strictly formatted using Laravel Pint.
- **Security**: Force HTTPS scheme enabled, trusted proxy headers (`X-Forwarded-Proto`), CSRF token protection, and bcrypt password hashing.

---

*Report generated automatically for Npontu Technologies Project Review.*
