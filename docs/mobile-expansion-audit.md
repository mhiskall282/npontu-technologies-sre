# Mobile Expansion Architecture & Security Audit
**Project**: Npontu Technologies SRE Platform — Support Activity Tracker  
**Target Clients**: Laravel 11 Web (Blade/Livewire) + Cross-Platform Mobile (Flutter Android/iOS)  
**Date**: September 2026  
**Auditor**: Senior Software Architect, Laravel Engineer, Flutter Engineer & DevSecOps Specialist  
**Status**: Approved Baseline Audit  

---

## 1. Executive Summary

The Npontu Technologies Support Activity Tracker is a mission-critical Site Reliability Engineering (SRE) operations platform built on Laravel 11 (PHP 8.4/8.2+), MySQL/SQLite, Livewire 3, Tailwind CSS v3, and Pest. It enforces 24/7 operational continuity, compliance tracking, append-only operational checkoffs, two-way shift handovers, operational team communications, and system health diagnostics.

This audit evaluates the codebase to establish secure, versioned, RESTful API boundaries (`/api/v1`) and a cross-platform mobile client (Flutter for Android and iOS) without duplicating business logic, breaking existing web functionality, or introducing parallel state engines.

---

## 2. Existing Architecture Overview

```
                           ┌──────────────────────────────────────────────┐
                           │            NPONTU SRE PLATFORM               │
                           └──────────────────────┬───────────────────────┘
                                                  │
                 ┌────────────────────────────────┴───────────────────────────────┐
                 │                                                                │
     ┌───────────────────────┐                                       ┌───────────────────────┐
     │      WEB CLIENT       │                                       │     MOBILE CLIENT     │
     │ Blade + Livewire 3    │                                       │ Flutter (Android/iOS) │
     │ Tailwind CSS Tokens   │                                       │ Material 3 + Tokens   │
     └───────────┬───────────┘                                       └───────────┬───────────┘
                 │                                                                │
                 │ Session Cookie + CSRF                                         │ Bearer Token (Sanctum)
                 ▼                                                                ▼
     ┌───────────────────────┐                                       ┌───────────────────────┐
     │   WEB ROUTES (web.php)│                                       │   API v1 (api.php)    │
     └───────────┬───────────┘                                       └───────────┬───────────┘
                 │                                                                │
                 └────────────────────────────────┬───────────────────────────────┘
                                                  │
                                                  ▼
                                 ┌─────────────────────────────────┐
                                 │       SHARED DOMAIN LOGIC       │
                                 │   - Actions/Activities          │
                                 │   - Actions/Handovers           │
                                 │   - Services (Audit, Health,    │
                                 │     Reporting, EmailToken)      │
                                 │   - Policies (Activity, User)   │
                                 └────────────────┬────────────────┘
                                                  │
                 ┌────────────────────────────────┴───────────────────────────────┐
                 │                                                                │
                 ▼                                                                ▼
   ┌──────────────────────────┐                                     ┌──────────────────────────┐
   │    PERSISTENT STORAGE    │                                     │  OPERATIONAL TELEMETRY   │
   │ MySQL 8.0+ / SQLite      │                                     │ SIEM state_changes log   │
   │ Append-only Event Logs   │                                     │ Health Probes & Cron     │
   │ Immutable Audit Logs     │                                     │ SMTP Alert Notifications │
   └──────────────────────────┘                                     └──────────────────────────┘
```

The architecture strictly adheres to:
- **Thin Controllers**: Controllers execute HTTP validation, policy checks, delegate to Actions/Services, and return structured responses.
- **Single Responsibility Domain Actions**: State mutations are centralized in `app/Actions/` (`CreateActivityAction`, `UpdateActivityStatusAction`, `CreateShiftHandoverAction`, `AcceptShiftHandoverAction`, `DeleteActivityAction`, `UpdateActivityAction`).
- **Append-Only Domain Event Store**: Checkoff events are recorded in `activity_logs` without SQL updates on historical rows.
- **Immutable Compliance Audit Trail**: Every mutating user action generates an audit log entry in `audit_logs` capturing actor metadata, timestamp, IP, and before/after diffs.

---

## 3. Database Schema & Relationships

The existing database schema comprises 14 migrations:

| Table | Purpose | Core Fields | Foreign Keys / Indexes |
|---|---|---|---|
| `users` | SRE Operators & Leads | `id`, `name`, `email`, `role`, `grade`, `department`, `privileges`, `designation`, `phone` | Indexed role, grade, deleted_at |
| `activities` | Defined operational checks | `id`, `title`, `description`, `category`, `recurrence`, `priority`, `sla_time`, `is_pinned`, `is_active`, `created_by`, `assigned_to` | FKs to `users` (`created_by`, `assigned_to`). Indexes on `assigned_to`, `priority`, `is_pinned`, `is_active` |
| `activity_logs` | Append-only status events | `id`, `activity_id`, `date`, `status`, `remark`, `incident_ticket`, `is_escalated`, `updated_by`, `actor_name`, `actor_role`, `actor_designation`, `actor_ip` | FK to `activities`, `users`. Composite index on `(date, activity_id)`. Indexes on `status`, `is_escalated` |
| `audit_logs` | Compliance audit trail | `id`, `actor_id`, `actor_name`, `actor_role`, `actor_ip`, `subject_type`, `subject_id`, `event`, `old_values`, `new_values` | Morph index on `(subject_type, subject_id)`, index on `actor_id`, index on `created_at` |
| `shift_handovers`| Two-way shift handovers | `id`, `date`, `shift`, `outgoing_lead_id`, `incoming_lead_id`, `summary`, `incidents`, `pending_tasks_count`, `completed_tasks_count`, `signed_at`, `accepted_at`, `accepted_by_id`, `acceptance_remarks` | FKs to `users` (`outgoing_lead_id`, `incoming_lead_id`, `accepted_by_id`). Index on `(date, shift)` |
| `conversations` | Ops chat & war rooms | `id`, `type`, `title`, `description`, `is_private`, `created_by` | Index on `type` |
| `conversation_participants`| Participant roster & read receipts | `id`, `conversation_id`, `user_id`, `last_read_at` | Unique on `(conversation_id, user_id)` |
| `messages` | Chat messages & attachments | `id`, `conversation_id`, `sender_id`, `body`, `attachment_name`, `attachment_mime`, `attachment_size`, `attachment_blob` | FKs to `conversations`, `users`. Indexed `created_at` |
| `personal_access_tokens` | Sanctum tokens (Added) | `id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at` | Standard Sanctum index and schema |

---

## 4. User Roles, Grades, and Granular Permissions

### Roles
1. **`admin`**: Full administrative custody. Manages users, resets passwords, configures activities, views security audits, exports reports.
2. **`lead`**: Shift Supervisor / Team Lead. Drafts and signs handovers, accepts incoming shifts, delegates checks, escalates P1/P2 incidents, creates war rooms.
3. **`agent`**: SRE Support Operator. Executes checkoffs, adds handover remarks, raises incident escalation flags, chats in channels.

### Engineering Grades (L1 – L5)
- `L1`: Associate Support Operator
- `L2`: Support Engineer (SRE)
- `L3`: Senior SRE Specialist
- `L4`: Team Lead & Shift Supervisor
- `L5`: Principal Architect & Enterprise Lead

### Granular Privileges Catalog
- `manage_activities`: Create, edit, and archive check definitions.
- `assign_tasks`: Inline and bulk task delegation.
- `sign_handovers`: Author and sign outgoing shift handover briefings.
- `accept_handovers`: Verify and sign on incoming shift responsibility.
- `escalate_incidents`: Flag checks as operational incidents and link ticket IDs.
- `export_reports`: Query date-range reports, stream CSVs, and access print views.
- `manage_users`: Provision accounts, adjust privileges, trigger password resets.
- `view_audit_logs`: Inspect security audit log diffs.
- `create_channels`: Spawn group channels and incident war rooms.

---

## 5. Existing Business Workflows

1. **Daily Operational Checkoff Flow**:
   - Operator navigates to Daily Shift Checklist.
   - Pinned checks float to the top, ordered by Priority Tier (Critical P1 -> High P2 -> Medium P3 -> Low P4).
   - Operator updates status to `done` or `pending` with an optional remark.
   - If an outage is detected, operator flags `is_escalated = true` and enters ticket reference (e.g. `INC-2041`).
   - Action appends a new `activity_logs` entry capturing snapshot of operator credentials and IP address.
   - AuditLog entry is immutably stored.

2. **Task Delegation Flow**:
   - Supervisors can delegate individual checks or bulk-delegate selected checks to specific engineers.
   - Engineers can filter their board to "Assigned to Me".
   - Reassignments generate compliance audit entries.

3. **Two-Way Shift Handover Handshake**:
   - Outgoing lead drafts briefing (Morning, Afternoon, Night) summarizing shift issues and blockers.
   - Metrics snapshot is frozen (pending vs completed check counts).
   - Outgoing lead signs off (`signed_at`).
   - Incoming lead inspects open items, enters verification remarks, and accepts responsibility (`accepted_at`, `accepted_by_id`).
   - Audit trail captures custody transfer.

4. **Operational Communications & War Rooms**:
   - Live chat supports 1-on-1 direct messaging, public `#general-shift` channel, and incident war rooms.
   - Read receipt tracking (`last_read_at`) drives unread badge counters.
   - Base64 compressed attachments support screenshots and log snippets.

5. **Telemetry & System Health Probes**:
   - `SystemHealthService` checks DB roundtrip latency, memory, cache, storage read/write, mail gateway, and uptime SLA.
   - Live telemetry endpoint (`/health/telemetry`) provides real-time streaming metrics.

---

## 6. Reusable Actions and Services

| Class | Reusable Functionality | API Integration |
|---|---|---|
| `CreateActivityAction` | Activity creation + AuditLog + Telemetry | Directly called by `POST /api/v1/activities` |
| `UpdateActivityAction` | Activity field edits + AuditLog + Telemetry | Directly called by `PUT /api/v1/activities/{activity}` |
| `UpdateActivityStatusAction` | Append-only status transition + bio snapshot | Directly called by `POST /api/v1/activities/{activity}/status` |
| `DeleteActivityAction` | Soft deletion + AuditLog | Directly called by `DELETE /api/v1/activities/{activity}` |
| `CreateShiftHandoverAction` | Shift briefing sign-off + metrics freeze | Directly called by `POST /api/v1/handovers` |
| `AcceptShiftHandoverAction` | Two-way handshake acceptance + audit log | Directly called by `POST /api/v1/handovers/{handover}/accept` |
| `AuditService` | Immutable audit log writer with actor context | Injected across all API domain actions |
| `ReportingService` | Date-range queries, daily summaries, chart data | Powers `/api/v1/reports/*` and `/api/v1/dashboard` |
| `SystemHealthService` | Comprehensive SRE telemetry & diagnostics | Powers `/api/v1/health` and `/api/v1/health/telemetry` |

---

## 7. Recommended API Boundaries (`/api/v1`)

| Endpoint | Method | Role / Privilege | Purpose |
|---|---|---|---|
| `/api/v1/auth/login` | POST | Public | Authenticate user, issue Sanctum Bearer token |
| `/api/v1/auth/logout` | POST | Authenticated | Revoke current access token |
| `/api/v1/auth/revoke-sessions` | POST | Authenticated | Revoke all personal access tokens for user |
| `/api/v1/me` | GET | Authenticated | Return profile, role, grade, privileges, unread count |
| `/api/v1/dashboard` | GET | Authenticated | Daily shift overview, pending/done counts, alerts |
| `/api/v1/activities` | GET | Authenticated | List activities with filters (date, status, assigned, priority) |
| `/api/v1/activities` | POST | `manage_activities` | Create operational check definition |
| `/api/v1/activities/{activity}` | GET | Authenticated | Get activity detail with history timeline |
| `/api/v1/activities/{activity}` | PUT | `manage_activities` | Update activity check definition |
| `/api/v1/activities/{activity}` | DELETE | `admin` | Soft delete activity |
| `/api/v1/activities/{activity}/status` | POST | Authenticated | Transition checkoff status with remark and incident flag |
| `/api/v1/activities/bulk-assign` | POST | `assign_tasks` | Bulk reassign check definitions |
| `/api/v1/handovers` | GET | Authenticated | List shift handover records |
| `/api/v1/handovers` | POST | `sign_handovers` | Create outgoing shift handover briefing |
| `/api/v1/handovers/{handover}` | GET | Authenticated | Get handover detail and sign-off status |
| `/api/v1/handovers/{handover}/accept` | POST | `accept_handovers` | Accept incoming shift responsibility |
| `/api/v1/conversations` | GET | Authenticated | List conversations for user |
| `/api/v1/conversations` | POST | `create_channels` | Create team channel or direct message |
| `/api/v1/conversations/{conv}/messages` | GET | Participant | Paginated message stream |
| `/api/v1/conversations/{conv}/messages` | POST | Participant | Post message with optional attachment |
| `/api/v1/conversations/{conv}/read` | POST | Participant | Mark conversation messages as read |
| `/api/v1/health` | GET | Public | Uptime monitor JSON probe |
| `/api/v1/health/telemetry` | GET | Public/Internal | Real-time performance telemetry |
| `/api/v1/health/diagnostics` | GET | `admin`, `lead` | Full subsystem diagnostics |
| `/api/v1/reports` | GET | `export_reports` | Date-range activity report and charts |
| `/api/v1/reports/handovers` | GET | `export_reports` | Handover compliance report |
| `/api/v1/reports/timelines` | GET | `export_reports` | Operator working hours timeline |
| `/api/v1/team` | GET | Authenticated | Team directory for assignment and messaging |
| `/api/v1/audit-logs` | GET | `view_audit_logs` | Read-only security audit log stream |

---

## 8. Existing Testing Coverage

- **Pest Test Suite**: 79 tests, 409 assertions, 100% passing.
- **Coverage Areas**:
  - Authentication (login, logout, redirects)
  - Activity CRUD and soft deletion
  - Append-only status flow, bio capture, and immutable audit logs
  - Task assignment, personal queues, bulk delegation
  - SRE priority tiers (P1-P4), SLA targets, pinned checks
  - Two-way shift handover handshake and metrics freeze
  - Operational messaging, unread counts, attachments, email tokens
  - System health probes and telemetry
  - Reporting date-range queries and automated commands
  - Granular privilege enforcement and custom error handling

---

## 9. Security Weaknesses & Mobile Risks with Mitigations

| Risk | Impact | Mitigation Strategy |
|---|---|---|
| **Stolen Mobile Token** | Unauthorized API access | Use `flutter_secure_storage` (iOS Keychain / Android EncryptedSharedPreferences). Never use SharedPreferences for tokens. Provide `/api/v1/auth/revoke-sessions`. Short token lifespans where applicable. |
| **Broken Object-Level Auth (BOLA)** | User modifies another's checks or private channels | Strict Form Requests + Policies on every API route (`ActivityPolicy`, `UserPolicy`, conversation participant checks). |
| **Data Leakage via Logs** | Sensitive credentials in terminal | Mobile Dio interceptor redacts Authorization headers and passwords. Backend `state_changes` logger never records passwords or tokens. |
| **Unreliable Mobile Connectivity** | Dropped checkoffs, duplicate requests | Idempotent updates, local state caching, offline banner, retry mechanism with pull-to-refresh. |
| **Mass-Assignment Flaws** | Client injects elevated role or privileges | Model `$fillable` protection strictly preserved; Form Requests validate permitted fields only. |

---

## 10. Recommended Implementation Plan

1. **Phase 1: Backend API Foundation**
   - Install Laravel Sanctum.
   - Configure versioned API routing under `/api/v1`.
   - Build API Resources for JSON serialization (`UserResource`, `ActivityResource`, `ActivityLogResource`, `ShiftHandoverResource`, `ConversationResource`, `MessageResource`, `AuditLogResource`).
   - Create Form Requests with strict validation and authorization.
   - Implement API Controllers delegating to existing Actions.
   - Add Pest Feature tests for all `/api/v1/` routes.
   - Publish OpenAPI 3.x specification at `docs/api/openapi.yaml`.

2. **Phase 2 & 3: Flutter Mobile Application**
   - Configure Flutter project with Material 3 and Npontu brand tokens (`#1B6B3A`, `#F5C518`, `#E63946`).
   - Feature-first structure: `auth`, `dashboard`, `activities`, `handovers`, `incidents`, `messaging`, `reports`, `team`, `audit`, `health`.
   - Secure storage with `flutter_secure_storage`.
   - Dio HTTP networking client with logging, auth interceptor, and error handling.
   - Riverpod state management.
   - Pull-to-refresh, skeleton loaders, empty and error recovery states, offline banner.
   - Automated unit, widget, and integration tests.

3. **Phase 4 & 5: Hardening, Threat Model & Deployment**
   - Write `docs/security/mobile-threat-model.md`.
   - Write `docs/deployment/mobile-deployment.md`.
   - Write `docs/mobile-development.md`.
   - Write `docs/mobile-api.md`.
   - Update `README.md`.
