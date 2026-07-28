# Architecture — Support Activity Tracker

> **Status**: Placeholder — to be completed in Prompt 2 (architecture review session).
> Do not implement application code until this document is approved.

---

## 1. Entity-Relationship Diagram (ERD)

> **TODO — Prompt 2**: Produce a full ERD covering all entities below.
> Use Mermaid erDiagram syntax so it renders in GitHub and the agent IDE.

### Anticipated Entities

| Entity | Purpose |
|---|---|
| `users` | Authenticated support personnel + admins |
| `activities` | The trackable operational items per day |
| `activity_updates` | Each status change / remark on an activity (history-preserving) |
| `audit_logs` | Immutable event log: actor, event type, subject, before/after JSON, IP |

### Anticipated Relationships

- A `user` creates many `activities`
- An `activity` has many `activity_updates`
- An `activity_update` belongs to a `user` (the updater)
- `audit_logs` are polymorphic — can point to any subject (`activities`, `activity_updates`, `users`)

---

## 2. Module Boundaries

> **TODO — Prompt 2**: Define bounded contexts / Laravel module groupings.

### Anticipated Modules

| Module | Namespace | Responsibility |
|---|---|---|
| Auth | `App\Http\Controllers\Auth` | Login, logout, session |
| Activities | `App\Actions\Activities`, `App\Http\Controllers\ActivityController` | CRUD, status update |
| Reporting | `App\Services\ReportingService`, `App\Http\Controllers\ReportController` | Date-range queries, aggregations |
| Audit | `App\Services\AuditService`, `App\Models\AuditLog` | Write-only log, query for display |
| Admin | `App\Http\Controllers\Admin` | User management (admin role only) |

### Livewire Components (anticipated)

| Component | Purpose |
|---|---|
| `DailyActivityBoard` | Today's activities + real-time status, shift handover view |
| `ActivityStatusUpdater` | Inline status + remark form |
| `ReportingDateRange` | Date picker + filter UI for FR-5 |
| `ActivityForm` | Create / edit activity |

---

## 3. Request Lifecycle

> **TODO — Prompt 2**: Draw a sequence diagram for the critical path:
> User updates activity status → Form Request validates → Action writes update → Audit log written → Livewire refreshes view.

`
[Browser] → [Livewire Component] → [Form Request] → [Action Class] → [Model] → [DB]
                                                            ↓
                                                    [AuditService]
                                                            ↓
                                                    [audit_logs table]
`

---

## 4. Database Schema (Detailed)

> **TODO — Prompt 2**: Produce column-level schema for each table with types, constraints, and indexes.

### Anticipated Key Indexes

- `activities.date` — supports FR-4 (daily view) and FR-5 (date-range query)
- `activities.status` — supports filtering in reporting view
- `activity_updates.activity_id` — FK, supports joining updates to activities
- `audit_logs.subject_type + subject_id` — composite, supports polymorphic lookup
- `audit_logs.created_at` — supports time-range audit queries

---

## 5. Deployment Diagram

> **TODO — Prompt 2**: Illustrate the target runtime environment.

### Anticipated Stack (local / development)

`
┌─────────────────────────────┐
│         Browser             │
└────────────┬────────────────┘
             │ HTTP
┌────────────▼────────────────┐
│   Laravel Dev Server        │
│   php artisan serve         │
│   :8000                     │
└────────────┬────────────────┘
             │ PDO / MySQL
┌────────────▼────────────────┐
│   MySQL 8.0                 │
│   npontu_tracker (db)       │
└─────────────────────────────┘
`

### Production Target (to be confirmed)

- PHP 8.2+ on Linux (Ubuntu 22.04 assumed)
- Nginx as web server
- MySQL 8.0 managed instance
- Laravel Octane optional (out of scope for base submission)
- Queue driver: `sync` for base submission (no Redis required unless notifications are added)

---

## 6. Security Architecture

> **TODO — Prompt 2**: Document auth flow, session config, and role enforcement points.

### Role Enforcement Points (anticipated)

| Route group | Middleware | Policy |
|---|---|---|
| All app routes | `auth` | — |
| Admin routes | `auth`, `role:admin` | `UserPolicy` |
| Activity create | `auth` | `ActivityPolicy@create` |
| Activity update | `auth` | `ActivityPolicy@update` |
| Reports | `auth` | `ActivityPolicy@viewAny` |

---

## 7. Open Architecture Questions

> These must be resolved in Prompt 2 before implementation begins.

- [ ] Will roles be stored as a `role` enum column on `users`, or use a pivot/roles table?
- [ ] Is soft-delete on `activities` confirmed? (Recommended — see requirements.md §Open Questions)
- [ ] What is the target PHP/Laravel version deployed on the evaluator's machine?
- [ ] Will the evaluation be done via Docker, `php artisan serve`, or a shared hosting environment?
- [ ] Should `activity_updates` store a full snapshot of the activity at time of update, or only the changed fields?
