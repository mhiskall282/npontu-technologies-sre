# File Reference — Interview Q&A Guide

> **Purpose**: This document explains what every significant file in this project does, why it exists,
> and how to answer common interview questions about it. Keep this alongside the codebase as a study aid.

---

## `app/Models/`

### `User.php`
**What it does**: The authenticated user entity. Extends Laravel's `Authenticatable`, uses `SoftDeletes` (deleted users keep their activity log references intact), and declares role and granular privilege helper methods.

**Key properties**:
- `$fillable`: `name`, `email`, `password`, `role`, `grade`, `department`, `privileges`, `designation`, `phone`
- `role`: one of `agent`, `lead`, `admin`
- `grade`: SRE operational tier (`L1` Support Operator, `L2` Support Engineer, `L3` Senior SRE, `L4` Principal Lead, `L5` Director/Architect)
- `department`: Operations division (e.g. `Cloud Infrastructure & SRE`, `Payment Gateway Operations`, `Database Operations & DBA`, `Security & Compliance`)
- `privileges`: JSON array of granular permissions assigned by Admin (`manage_activities`, `assign_tasks`, `sign_handovers`, `accept_handovers`, `escalate_incidents`, `export_reports`, `manage_users`, `view_audit_logs`, `create_channels`).

**Role & Privilege helper methods**:
```php
isAdmin(): bool
isLead(): bool
isAgent(): bool
hasPrivilege(string $privilege): bool
canManageActivities(): bool
canAssignTasks(): bool
canSignHandovers(): bool
canAcceptHandovers(): bool
unreadMessagesCount(): int
```

**Interview Q: Why combine fixed roles with granular privileges?**
> A purely role-based system is too rigid when team members wear multiple hats (e.g., an L2 engineer acting as an incident commander). The hybrid model preserves fast role checks while allowing admins to grant specific elevated privileges (such as channel creation or task reassignment) without promoting the user to full admin.

**Interview Q: Why soft-delete users instead of hard-delete?**
> If a user is hard-deleted, their FK references in `activity_logs.updated_by` and `audit_logs.actor_id` would violate FK constraints (or cascade-delete the logs). Both columns use `nullOnDelete()`, meaning the FK becomes NULL on deletion. The `actor_name` field (denormalised snapshot) preserves the human-readable record even after the user account is gone.

---

### `Activity.php`
**What it does**: Represents a recurring operational check (e.g. "Daily SMS count vs SMS count from logs"). Activities are the template — `ActivityLog` records the daily status events.

**Key relationships**:
- `hasMany(ActivityLog::class)` — all status events across all dates
- `belongsTo(User::class, 'created_by')` — user who created the definition
- `belongsTo(User::class, 'assigned_to')` — assigned engineer (optional delegation)
- `latestLog()` — scoped relationship for today's most-recent event (used for current status)

**Interview Q: Why is `assigned_to` nullable?**
> SRE operations frequently rely on a shared pool model where any active on-duty operator can fulfill a pending check. By making `assigned_to` nullable, tasks default to the general shift pool. When a supervisor delegates a check to a specific engineer, `assigned_to` is populated, enabling personal filtering ("Assigned to Me") and accountability without breaking pool elasticity.

---

### `ActivityLog.php`
**What it does**: Append-only event store. Each row = one status-change event. The current status for an activity on a given date is **derived** as the status of the row with the highest `id` for that `(activity_id, date)` pair.

**Why append-only?**
> FR-4 (shift handover) requires showing "who updated what and when" as a timeline. A last-write-wins model loses history. By appending, we can reconstruct the full sequence of changes.

**Interview Q: How do you derive "current status" without a mutable column?**
```sql
SELECT activity_id, status
FROM activity_logs
WHERE date = '2026-07-28'
GROUP BY activity_id
HAVING id = MAX(id)
```
> The `ReportingService::dailySummary()` implements this pattern using Eloquent's `latestOfMany()` scoped relationship.

---

### `AuditLog.php`
**What it does**: Polymorphic compliance log. Stores every state mutation across all model types, with actor identity, IP address, and JSON before/after diffs.

**Key columns**:
| Column | Purpose |
|---|---|
| `actor_id` | FK to users (nullable — survives user deletion) |
| `actor_name` | Denormalised snapshot (immutable historical record) |
| `subject_type` + `subject_id` | Polymorphic reference to any model |
| `event` | e.g. `created`, `status_changed`, `profile_updated` |
| `old_values` / `new_values` | JSON diff of the change |
| `ip_address` | Captured server-side from `Request::ip()` |

**Interview Q: Why is actor identity captured server-side?**
> Never from client-submitted data. A malicious actor could inject a different user's name in the request body. `AuditService` reads the actor from the authenticated session (`Auth::user()`) and the IP from `Request::ip()` — both are server-controlled.

---

### `ShiftHandover.php`
**What it does**: Represents formal SRE shift transition briefings (Morning, Afternoon, Night) between outgoing and incoming shift leads.
**Key properties**:
- `date`: Calendar date of the shift
- `shift`: Enum (`morning`, `afternoon`, `night`)
- `outgoing_lead_id`: FK to users (who drafted and signed the briefing)
- `incoming_lead_id`: FK to users (receiving shift lead)
- `summary`: High-level operational narrative and gateway health
- `incidents`: Open blocker tickets or discrepancy notes
- `pending_tasks_count` / `completed_tasks_count`: Statistical snapshot captured at moment of sign-off
- `signed_at`: Timestamp of digital handover completion
- `accepted_at`: Timestamp when the incoming lead formally accepted operational responsibility
- `accepted_by_id`: FK to users (the receiving lead who signed on)
- `acceptance_remarks`: Verification notes and incoming shift commitments

**Interview Q: What is the two-way handover handshake and why is it essential?**
> In high-reliability SRE organizations, a handover is not merely an outgoing broadcast. It is a legally binding two-way handshake: the outgoing lead certifies that checks were performed and active incidents are documented (`sign-off`), and the incoming lead certifies that systems were verified and custody is assumed (`sign-on / accept`). This eliminates gaps in operational ownership.

---

### `Conversation.php` & `ConversationParticipant.php`
**What they do**: Core models for SRE Operational Communications. Supports 1-on-1 direct messaging, public team shift channels (`#general-shift`), and private group war rooms. Tracks unread counts per user and maintains per-participant `last_read_at` timestamps.

### `Message.php`
**What it does**: Represents an operational chat message event linked to a conversation and sender.

---

## `app/Actions/`

All Action classes follow the **Single Responsibility Principle**: one class, one operation.

### `Activities/CreateActivityAction.php`
**What it does**: Validates the business rules for creating an activity and persists the record. Called by `Admin\ActivityController@store`.

### `Activities/UpdateActivityStatusAction.php`
**What it does**: The most critical Action. Appends a new `ActivityLog` row (with optional incident ticket and escalation flag) and writes an `AuditLog` entry. Called by the `ActivityStatusUpdater` Livewire component.

### `Handovers/CreateShiftHandoverAction.php`
**What it does**: Persists formal shift handover briefings, sets the `signed_at` timestamp, and emits an immutable compliance `AuditLog` entry. Called by `DailyActivityBoard@saveHandover`.

### `Handovers/AcceptShiftHandoverAction.php`
**What it does**: Executes incoming lead shift acceptance and sign-on, records incoming remarks, logs `handover_accepted` audit event, and completes the operational handover transfer.

**Interview Q: Why put this logic in an Action class instead of the controller?**
> Controllers should only handle HTTP concerns: validate the input, call the business logic, return a response. The action class is independently testable without HTTP — see `ActivityStatusFlowTest`, `SreEnterpriseFeaturesTest`, and `OperationalCommunicationsAndPrivilegesTest` which test action outcomes directly.

---

## `app/Services/`

### `AuditService.php`
**What it does**: Provides a single `log()` method that writes an `AuditLog` record. Reads actor identity from `Auth::user()` and IP from `Request::ip()` — never from caller-supplied data.

**Interview Q: Why is this a Service rather than an Action?**
> Actions implement business operations (create activity, update status). `AuditService` is a cross-cutting concern used by multiple Actions, Controllers, and Livewire components — it coordinates a supporting infrastructure concern, not a business workflow step.

---

### `ReportingService.php`
**What it does**: Contains the two main query patterns used across the app:
1. `dailySummary(string $date)` — returns all activities with their current status for the shift board
2. `exportQuery(...)` — returns filtered `ActivityLog` rows for the reporting page and email

**Interview Q: How does the daily summary query avoid N+1?**
> It uses a single query joining `activities` with the `activity_logs` subquery for the given date, then eager-loads the latest log entry. The `with('latestLog')` eager load prevents per-row queries inside the Livewire render loop.

---

## `app/Livewire/`

### `DailyActivityBoard.php`
**What it does**: Primary operational console for SRE teams. Handles:
- Real-time checklist of operational checks split into "Needs Attention (Pending)" and "Completed (Done)".
- Dynamic filters: search query, category selector, personal "Assigned to Me" queue, unassigned shift pool, and engineer assignment.
- Team task assignment: single-check inline delegation and multi-check bulk delegation.
- SRE Shift Handover Management: outgoing leads draft and sign briefings with snapshot metrics; incoming leads review and execute formal **Sign-On / Acceptance** with custom remarks.
- Security audit timeline widget for supervisors and admins.
- Event-driven reactivity and `wire:poll.30000ms` background synchronization.

### `ActivityStatusUpdater.php`
**What it does**: Inline checkoff component rendered inside each activity card on the board. Provides instant toggle between Pending and Done, opens a modal for entering remarks and optional incident ticket references, flags escalations, and dispatches `status-updated` events.

### `OperationalChat.php`
**What it does**: Enterprise real-time SRE communications hub. Features:
- 1-on-1 direct private messaging between support operators and engineers.
- Shared SRE Team shift channels (auto-provisions `#general-shift` with welcome broadcast).
- Group operations rooms & incident war rooms (with public/private visibility toggles).
- Unread message counter tracking and per-participant `last_read_at` updating.
- Granular authorization enforcement (checks `create_channels` privilege).
- Background polling via `wire:poll.4000ms` for live message stream updates.

---

## `app/Http/Controllers/`

### `ActivityController.php`
**What it does**: Resource controller for the front-end activity views (index, show, edit, update). Agents use this to view activities and their history. The `show` action renders the 7-day completion bar chart.

**Interview Q: How does authorization work here?**
> Every method calls `$this->authorize(ability, $activity)` which delegates to `ActivityPolicy`. The policy checks the user's role (e.g. only admins can delete). This is separate from the middleware check — middleware handles route-level access, policies handle model-level access.

---

### `ReportController.php`
**What it does**: Handles the reports page (`GET /reports`) and the email action (`POST /reports/email`).

- `index()`: When `print=true` query param is present, bypasses pagination and returns the full collection for print-to-PDF. Otherwise paginates.
- `email()`: Accepts custom `recipients[]`, `subject`, and `message` — builds and dispatches `ActivityReportMail`.

---

### `SettingsController.php`
**What it does**: Allows any authenticated user to update their own profile (`PUT /settings`) and change their password (`PUT /settings/password`). Uses `UserPolicy` to verify the user can only update themselves (admins can update anyone).

---

### `Admin\UserController.php`
**What it does**: Admin-only CRUD for team member accounts. Restricted via `role:admin` middleware. Uses `StoreUserRequest` and `UpdateUserRequest` for validation.

---

## `app/Livewire/`

### `DailyActivityBoard.php`
**What it does**: The main shift handover component. Holds a reactive `$date` property — changing the date picker re-renders the board via Livewire's two-way binding. Uses `wire:poll.30000ms` to auto-refresh every 30 seconds for live shift updates.

**Interview Q: Why is this a Livewire component instead of a regular controller + view?**
> The date picker needs to reactively re-fetch data without a full page reload. Livewire handles the AJAX round-trip transparently. The `wire:poll` directive also auto-refreshes for agents in the same shift, keeping the board live without websockets.

---

### `ActivityStatusUpdater.php`
**What it does**: Inline status toggle embedded in each activity row. Submits `status` and `remark` via a Livewire action, which calls `UpdateActivityStatusAction`, then emits an event to refresh the parent board.

**Interview Q: How does Livewire component isolation work here?**
> `ActivityStatusUpdater` is a nested child component inside `DailyActivityBoard`. It receives `$activity` and `$date` as properties. When it dispatches a `statusUpdated` event, the parent board component listens and re-renders — keeping the two components decoupled.

---

## `app/Http/Middleware/`

### `EnsureRole.php`
**What it does**: Route middleware that checks `auth()->user()->role` against an allowed list. Registered as `role` alias in `bootstrap/app.php`. Usage: `->middleware('role:admin,lead')`.

**Interview Q: Why use middleware AND policies?**
> Middleware provides **route-level** access control (can this user even reach this route?). Policies provide **model-level** access control (can this user perform this action on this specific record?). Using both prevents a user from crafting direct HTTP requests to bypass controller-level checks.

---

### `SecureHeaders.php`
**What it does**: Adds security HTTP response headers on every response:
- `X-Frame-Options: DENY` — prevents clickjacking
- `X-Content-Type-Options: nosniff` — prevents MIME sniffing
- `Referrer-Policy: strict-origin-when-cross-origin`

---

## `app/Http/Requests/`

All Form Requests follow the same pattern: `authorize()` calls the relevant Policy, `rules()` returns the validation array.

| File | Purpose |
|---|---|
| `StoreActivityRequest` | Validation for creating activities (admin/lead only) |
| `UpdateActivityRequest` | Validation for editing activities |
| `UpdateActivityStatusRequest` | Validates status enum and remark for Livewire status toggle |
| `StoreUserRequest` | Validates new user creation (admin only) |
| `UpdateUserRequest` | Validates user edits |
| `ReportRequest` | Validates date range, status filter, and email recipient fields |

**Interview Q: Why use Form Requests instead of inline `$request->validate()`?**
> Form Requests keep controllers thin and self-document the API surface of each endpoint. Authorization (`authorize()`) and validation (`rules()`) are co-located, making it easy to audit what each endpoint accepts and who can call it.

---

## `app/Policies/`

### `ActivityPolicy.php`
**What it does**: Defines who can perform each CRUD action on activities.

| Method | Allowed |
|---|---|
| `viewAny` | All authenticated users |
| `view` | All authenticated users |
| `create` | Leads + Admins |
| `update` | Leads + Admins |
| `delete` | Admins only |

### `UserPolicy.php`
**What it does**: Who can manage users. `viewAny`/`create`/`delete` is Admin-only. `update` allows a user to update themselves — useful for the Settings page.

---

## `app/Mail/ActivityReportMail.php`
**What it does**: Laravel Mailable that sends an activity report to a list of recipients. Accepts:
- `$logs` — the collection of `ActivityLog` records to include
- `$from` / `$to` — the date range string
- `$subject` — custom email subject
- `$message` — custom note from the sender

Renders `resources/views/emails/activity_report.blade.php`.

---

## `database/migrations/`

All migrations implement `down()` for rollback capability.

| Migration | What it creates |
|---|---|
| `create_users_table` | Authentication + role columns |
| `create_activities_table` | Activity definitions (title, category, recurrence, is_active) |
| `create_activity_logs_table` | Append-only status event log with composite index on `(date, activity_id)` |
| `create_audit_logs_table` | Polymorphic compliance log with JSON diff columns |

**Interview Q: Why is there a composite index on `(date, activity_id)` in activity_logs?**
> The daily shift board query filters by `date` first, then groups by `activity_id`. The composite index matches this query pattern exactly, keeping the board load fast even with large log volumes.

---

## `routes/web.php`
**What it does**: All application routes, grouped under `auth` middleware. Key structure:

```
GET  /daily                    → DailyActivityBoard (Livewire)
GET  /activities               → ActivityController@index
GET  /activities/{id}          → ActivityController@show
GET  /reports                  → ReportController@index
POST /reports/email            → ReportController@email
GET  /settings                 → SettingsController@edit
PUT  /settings                 → SettingsController@update
PUT  /settings/password        → SettingsController@updatePassword
prefix /admin  (role:admin,lead)
    /admin/activities/*        → Admin\ActivityController (resource)
    /admin/users/*             → Admin\UserController (resource, role:admin only)
```

---

## `tests/Feature/`

| File | What it tests |
|---|---|
| `AuthenticationTest.php` | Login page renders; correct creds authenticate; wrong creds reject; logout works; unauthenticated redirect |
| `ActivityCrudTest.php` | Create (lead/admin can, agent cannot); read (all auth); update; soft-delete |
| `ActivityStatusFlowTest.php` | Livewire status toggle appends ActivityLog and AuditLog with correct values |
| `ReportingTest.php` | Date-range query returns in-range records; excludes out-of-range; status filter works |
| `ExampleTest.php` | Root redirect to login when unauthenticated |

**Interview Q: What is `RefreshDatabase` and why do all tests use it?**
> `RefreshDatabase` wraps each test in a transaction that is rolled back at the end, or re-runs migrations for each test. This ensures tests are isolated — one test's data cannot affect another. It prevents flaky, order-dependent test suites.

---

## `resources/views/layouts/app.blade.php`
**What it does**: The main application shell. Contains:
- Npontu branded navigation bar (logo, nav links, user name/role, Settings link, Sign Out)
- Flash message display (`session('success')` / `session('error')`)
- Yields a `content` section for page-specific content
- Print CSS (`@media print`) that hides navigation and non-essential elements for PDF export

---

## `resources/views/livewire/daily-activity-board.blade.php`
**What it does**: The shift handover board view. Key sections:
1. **Date picker** — `wire:model` binding triggers reactive re-render
2. **Role-based welcome banner** — Admin/Lead see green gradient console, Agents see operator card
3. **Stats bar** — Total / Pending / Done counts with completion progress bar
4. **Pending section** — Amber-highlighted cards for activities needing attention
5. **Done section** — Faded green cards for completed items
6. **Audit trail timeline** — Admin/Lead only; shows 5 most recent audit events

---

## `resources/views/reports/index.blade.php`
**What it does**: The reports page with:
- Date range + status + activity filters
- Export CSV, Print PDF (opens new tab with all records + auto-triggers browser print), Email Report (modal)
- Chart.js doughnut (status distribution) and bar chart (daily log volume)
- Paginated or full-collection table (based on `print=true` param)
- Email modal with recipient checkboxes, custom subject, and message body

---

## `app/Notifications/`

### `WelcomeNotification.php`
**What it does**: Sent to newly registered users when created by an Admin. Contains login instructions, temporary password credentials, and a direct link to the sign-in portal.

### `AdminPasswordResetNotification.php`
**What it does**: Sent when an Admin triggers a password reset for a team member. Generates a secure, 60-minute tokenized reset URL so users reset their own passwords without admins seeing their credentials.

### `MessageMentionMail.php`
**What it does**: Sent when an operator is tagged via `@name` or when a broadcast is sent to `@all` or `@everyone` in team channels or incident war rooms. Provides an HTML email receipt showing the sender, channel name, message excerpt, timestamp, and a direct 1-click CTA button to open the chat thread.

---

## `app/Http/Controllers/`

### `MonitoringController.php`
**What it does**: Powers the SRE Monitoring Dashboard (`/monitoring`). Provides live system health metrics, stale activity alerts, a 7-day completion trend chart, category progress indicators, top contributor rankings, and a paginated audit stream with JSON diff viewer.

### `HealthController.php` (Real-Time System Health & Software Status)
**What it does**: Serves the unified System Health & Performance Monitoring suite (`/health` and `/health/telemetry`):
1. Automated Uptime Probes: returns JSON status (`{ "status": "ok", "db": "ok", "timestamp": "..." }`) for Render, Pingdom, Docker, and curl probes.
2. Interactive SRE Health Dashboard: displays real-time telemetry HUD, 8 core subsystems matrix, email gateway metrics, 24-hour availability heartbeat timeline, and 7-day latency trend plots.
3. Live Streaming Telemetry: asynchronous polling endpoint streaming real-time DB latency, cache speed, memory usage, and operational check counts every 3 seconds.

### `ReportController.php` (Enhanced Multi-Domain Reporting)
**What it does**: Orchestrates three specialized SRE operational compliance reporting suites:
1. `index`: Activity check history across date ranges with CSV and print-friendly export.
2. `handovers`: Formal SRE shift handover audit reports with acceptance compliance KPIs, lead filters, and CSV export.
3. `timelines`: SRE operator work timelines and active duty hours analytics, deriving duty duration from activity logs and handover signatures with CSV export.

---

## `app/Services/`

### `SystemHealthService.php`
**What it does**: Core telemetry and diagnostics service probing 8 software subsystems: Primary Database, Email & Notification Gateway (SMTP), PHP Runtime & Compute, Storage Mount, Session & Cache Engine, Real-time Comms, Shift Handover Custody, and Security Audit Trail. Calculates query latency benchmarks in milliseconds, memory utilization, rolling SLA availability, and hourly operations throughput.

---

## `resources/views/health/`

### `index.blade.php`
**What it does**: High-density SRE System Health & Status console featuring hero status banner ("ALL 8 CORE SERVICES OPERATIONAL"), real-time streaming telemetry HUD, live latency line plot, 24-hour availability heartbeat bar, component health matrix, and live email/notification pipeline metrics.

---

## `resources/views/reports/`

### `handovers.blade.php`
**What it does**: Handover audit console featuring KPI cards (Total Handovers, Accepted %, Awaiting Sign-on, Incidents Flagged), multi-parameter filters (Shift, Lead, Acceptance Status), paginated table, and CSV streaming.

### `timelines.blade.php`
**What it does**: SRE operator work timelines and duty hours tracking dashboard. Displays total active hours, average shift length, completed checkoffs, and incident escalations per operator/date, with full CSV export.

---

## Deployment & Containerization

### `Dockerfile`
**What it does**: Multi-stage Docker container build. Stage 1 compiles Tailwind CSS assets using Node. Stage 2 packages PHP 8.2 with Apache, enables `mod_rewrite`, sets `DocumentRoot` to `public/`, configures `ServerName localhost`, and installs `pdo_pgsql` for Render PostgreSQL integration.

### `docker-entrypoint.sh`
**What it does**: Container boot script. Automatically generates `APP_KEY` if missing, sets file permissions on `storage/` and `bootstrap/cache/`, clears/caches configuration at runtime (picking up Render environment variables dynamically), runs `php artisan migrate --force`, and seeds default idempotent data before launching Apache in the foreground.

---

## Live Deployment URL & Endpoints

- **Live Custom Domain**: [https://npontu-tracker.johnokyere.xyz](https://npontu-tracker.johnokyere.xyz)
- **Render Primary Endpoint**: `https://npontu-support-tracker.onrender.com`
- **Shift Board**: `GET /daily`
- **SRE Monitoring**: `GET /monitoring` (Admin/Lead)
- **Account Settings**: `GET /settings`
- **PDF Print View**: `GET /reports?from=...&to=...&print=true`
- **Health Check API**: `GET /health`

**Interview Q: Why SQLite for production on Render (free tier)?**
> Free-tier Render services do not include a managed database. A persistent disk with SQLite is the simplest zero-cost production-grade persistence option for a small internal tool. At scale, the DB_CONNECTION would switch to MySQL and a managed database service would be provisioned.

---

## `render.yaml` + `build.sh`
**What they do**: Render.com deployment configuration.

- `render.yaml` — Blueprint declaring a web service with a 1 GB persistent disk at `/var/data` (SQLite file)
- `build.sh` — Build phase: `composer install --no-dev`, `npm run build`, cache config/routes/views

---

## `.agents/AGENTS.md`
**What it does**: Operating rules for AI coding agents working on this codebase. Enforces the fixed tech stack, coding standards (PSR-12, strict types), architecture rules (thin controllers, Action classes, Policies), security defaults, mandatory tests, and UI brand guidelines.

**Interview Q: Why document agent rules in the repo?**
> In a team that uses AI-assisted development, having explicit rules in the repo ensures consistent patterns regardless of which agent or developer makes a change. It acts as a living architecture decision record (ADR) and onboarding guide simultaneously.
