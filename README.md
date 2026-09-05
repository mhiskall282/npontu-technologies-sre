# Support Activity Tracker — Npontu Technologies

> **A Laravel 11 web application for operations support teams** to log daily shift activities, record status updates with immutable audit trails, and facilitate clean shift handovers.
>
> 

🌐 **Production Deployment**: [https://npontu-tracker.johnokyere.xyz](https://npontu-tracker.johnokyere.xyz)

[![Tests](https://img.shields.io/badge/tests-55%20passing-brightgreen)](tests/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red)](https://laravel.com)
[![Tailwind](https://img.shields.io/badge/Tailwind-3.x-cyan)](https://tailwindcss.com)

---

## What This Application Does

Support teams managing live production systems need a lightweight, auditable tool to track what was checked, what was resolved, and what must be handed over to the next shift. This application provides:

| Feature | Description |
|---|---|
| **Public SRE Landing Page** | High-impact overview of Npontu's SRE platform (`GET /`): capability matrix, 4-step handover lifecycle, live telemetry probes, and 1-click test roles |
| **SRE Left Sidebar Console** | Dark cockpit navigation (`bg-[#0F1A14]`) with responsive drawer, real-time UTC clock, and SRE status indicators |
| **Daily Shift Board** | Live checklist of today's activities — pending items glow amber, done items fade green, with task delegation |
| **Two-Way Shift Handshake** | Outgoing lead formal sign-off paired with incoming lead sign-on and verification acceptance remarks |
| **SRE Operations Comms** | Live team messaging hub: 1-on-1 direct chat, team channels (`#general-shift`), war rooms, and `@mention` email alerts |
| **System Health & Telemetry** | Multi-service probes (DB, cache, memory, mail), live 3s HUD streaming, 24h heartbeat, and public JSON API |
| **Granular Privileges & Grades** | 9 configurable access checkboxes per user and L1–L5 SRE operational tiers |
| **Branded Error Pages & Session Security** | Custom SRE 419 (Session Expired), 404 (Route Not Found), 403 (Forbidden), 500 (Runtime Exception), and 503 (Maintenance) with Livewire 419 interceptor and redesigned operator sign-in with 1-click test credentials |
| **Role-Based Dashboards** | Admins, Leads, and Agents each see a tailored interface with relevant quick-action buttons |
| **Status Updates & Escalations** | Mark activities Done or Pending with a remark, flag incident tickets (`INC-1042`), and trigger alert pings |
| **Immutable Audit Trail** | Every state mutation is logged with actor identity, IP address, and before/after JSON diff values |
| **Multi-Domain SRE Reports** | Date-range checkoff history, shift handover compliance KPIs, and operator work timelines & duty hours |
| **PDF Print / CSV Export** | Print complete reports for compliance documentation or stream to CSV for analysis |
| **Email Reports** | Send customised activity reports to selected team members via email |
| **Account Settings** | All users can update their profile and change their password |
| **Admin Console** | Admins manage users (CRUD), assign granular privileges, and define the activity checklist |

---

## Tech Stack

| Layer | Technology | Version | Why |
|---|---|---|---|
| Framework | Laravel | 11.x (LTS) | Mature, well-tested ecosystem; aligns with PHP 8.2 requirement |
| Language | PHP | 8.2+ | Typed properties, enums, readonly where appropriate |
| Database | SQLite (dev) / MySQL (prod) | 8.0+ | InnoDB with FK constraints; SQLite for zero-config local dev |
| Frontend | Blade + Livewire | Livewire 3.x | No JS build complexity; reactive wire:poll for live shift view |
| CSS | Tailwind CSS | 3.x | Utility-first; Npontu brand tokens configured |
| Tests | Pest | 2.x | Expressive, Laravel-native; more readable than PHPUnit verbosity |
| Linter | Laravel Pint | latest | Enforces PSR-12 automatically |
| Mail | Laravel Mailable | — | Markdown email templates with custom subject and body |
| Charts | Chart.js (CDN) | 4.x | Doughnut + bar charts for report visualisations |

---

## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 18+ & npm
- SQLite (default, zero config) **or** MySQL 8.0+

---

## Quick Start (Local Development)

### 1. Clone the repository
```bash
git clone https://github.com/mhiskall282/npontu-technologies-sre.git
cd npontu-technologies-sre
```

### 2. Install dependencies
```bash
composer install
npm install && npm run build
```

### 3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

The default `.env.example` uses **SQLite** — no database server needed:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

For MySQL, update:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=npontu_tracker
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run migrations and seed
```bash
php artisan migrate
php artisan db:seed
```

### 5. Serve the application
```bash
php artisan serve
```

Open **[http://localhost:8000](http://localhost:8000)**

---

## Seeded Test Credentials

| Role | Email | Password |
|---|---|---|
| Administrator | `admin@npontu.local` | `password` |
| Team Lead | `lead@npontu.local` | `password` |
| Support Agent | `agent@npontu.local` | `password` |

> ⚠️ **Change all passwords immediately in production.**

---

## Running Tests

```bash
# Full test suite (55 tests, 280 assertions)
./vendor/bin/pest

# With verbose output
./vendor/bin/pest --verbose

# Filter by test name
./vendor/bin/pest --filter "LandingPageTest"

# Run a specific file
./vendor/bin/pest tests/Feature/LandingPageTest.php
```

Tests use an **in-memory SQLite** database (configured in `phpunit.xml`) — no external DB needed.

**Test coverage areas:**
- **Public SRE Landing Page**: Unauthenticated visitor showcase, 6 capability pillars, architecture walkthrough, pre-seeded test roles, authenticated SRE cockpit CTA
- **Authentication & Security**: Login success, failure validation alerts, logout, redirect, session expiration banners
- **Custom Branded Error Handling**: 419 (Session Expired), 404 (Route Not Found), 403 (Forbidden), 500 (Runtime Error), 503 (Maintenance Mode)
- **Livewire 419 Interceptor**: Hook intercepting expired session tokens and redirecting cleanly to `/login?expired=1` without raw modal popups
- **Activity CRUD**: Create, read, update, soft-delete, with task assignment and delegation
- **Status Update Flow**: Status changes (Done/Pending) with mandatory remarks, incident escalation flags, and domain/audit logs
- **Operational Communications**: Direct 1-on-1 chats, team shift channels, incident war rooms, `@name` & `@all` email receipts
- **Shift Handover Handshake**: Outgoing briefing sign-off and incoming lead verification sign-on
- **Multi-Domain Reporting**: Custom date-range activity checks, handover audit reports, and operator work timelines & duty hours
- **System Health Diagnostics**: Live telemetry streaming, subsystem probes, and availability SLA metrics

---

## Code Style

```bash
# Auto-fix all PSR-12 violations
./vendor/bin/pint

# Dry-run (check only)
./vendor/bin/pint --test
```

**Run Pint before every commit** — enforced in AGENTS.md.

---

## Project Structure

```
.
├── app/
│   ├── Actions/Activities/         # Single-responsibility business logic classes
│   │   ├── CreateActivityAction.php
│   │   ├── UpdateActivityAction.php
│   │   ├── UpdateActivityStatusAction.php
│   │   └── DeleteActivityAction.php
│   ├── Http/
│   │   ├── Controllers/            # Thin HTTP glue (validate → delegate → respond)
│   │   │   ├── ActivityController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ReportController.php
│   │   │   ├── SettingsController.php
│   │   │   └── Admin/
│   │   │       ├── ActivityController.php
│   │   │       └── UserController.php
│   │   ├── Middleware/
│   │   │   ├── EnsureRole.php      # Role-based route guard
│   │   │   └── SecureHeaders.php   # Security HTTP headers
│   │   └── Requests/               # Form Requests (validation + authorization)
│   ├── Livewire/
│   │   ├── DailyActivityBoard.php  # Shift handover real-time board
│   │   └── ActivityStatusUpdater.php # Inline status toggle component
│   ├── Mail/
│   │   └── ActivityReportMail.php  # Mailable for emailed activity reports
│   ├── Models/
│   │   ├── Activity.php            # Core entity (what is checked)
│   │   ├── ActivityLog.php         # Append-only status event log
│   │   ├── AuditLog.php            # Security/compliance change log
│   │   └── User.php                # Auth user with role helpers
│   ├── Policies/
│   │   ├── ActivityPolicy.php      # Who can create/update/delete activities
│   │   └── UserPolicy.php          # Who can manage users
│   ├── Providers/
│   │   └── AppServiceProvider.php  # Layout component aliases
│   └── Services/
│       ├── AuditService.php        # Write immutable audit log entries
│       └── ReportingService.php    # Date-range report queries
├── database/
│   ├── factories/                  # Model factories for seeding & testing
│   ├── migrations/                 # Versioned schema (all have down())
│   └── seeders/                    # Default data (users, sample activities)
├── docs/
│   ├── requirements.md             # Functional requirements + grading rubric
│   ├── architecture.md             # ERD, module boundaries, deployment diagram
│   ├── context.md                  # Brand guidelines, business context
│   └── FILE_REFERENCE.md           # ← This file — per-file interview reference
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php   # Main app shell with navigation
│   │   ├── livewire/               # Livewire component views
│   │   ├── activities/             # Activity CRUD views
│   │   ├── admin/                  # Admin panel views
│   │   ├── reports/                # Reporting and chart views
│   │   ├── settings/               # Account settings views
│   │   └── auth/                   # Login form
│   └── css/app.css                 # Tailwind entry point + brand tokens
├── routes/
│   ├── web.php                     # All web routes (guarded by auth middleware)
│   └── auth.php                    # Login/logout routes
└── tests/
    ├── Feature/                    # HTTP-level Pest feature tests
    └── Unit/                       # Action/Service unit tests
```

---

## Key Architecture Decisions

### Why Livewire over Inertia/React?
Livewire avoids a JavaScript build pipeline for a small internal tool. The shift handover view benefits from `wire:poll` reactive updates (auto-refresh every 30s) without needing a full SPA. Evaluators can read pure PHP — no React layer to navigate.

### Why two separate log tables?
| Table | Purpose |
|---|---|
| `activity_logs` | Domain state changes — used for the shift board, business reporting, and the "current status" derivation |
| `audit_logs` | Security/compliance record — every mutation across all subjects, with IP address and JSON diffs |

These are intentionally separate. At scale, audit logs would be shipped to a SIEM. The activity_logs table is a first-class business entity.

### Why denormalise actor names?
Both `activity_logs` and `audit_logs` store `actor_name` as a snapshot. This preserves historical accuracy even if a user is renamed or deleted. The FK (`actor_id`) is also kept for joining, but is nullable so the row survives user deletion.

### Why Action classes?
Controllers only handle HTTP glue: validate via Form Request → delegate to Action → return response. Actions (`app/Actions/`) contain the pure business logic and are independently testable without HTTP.

### Why soft deletes on activities?
`Activity::delete()` sets `deleted_at` rather than removing the row. Historical `activity_logs` records reference the activity FK — hard-deleting would break reports for past periods.

---

## Roles & Permissions

| Permission | Agent | Lead | Admin |
|---|---|---|---|
| View daily board | ✅ | ✅ | ✅ |
| Update own activity status | ✅ | ✅ | ✅ |
| Create / edit activities | ❌ | ✅ | ✅ |
| Delete activities | ❌ | ❌ | ✅ |
| View reports | ❌ | ✅ | ✅ |
| Email reports | ❌ | ✅ | ✅ |
| Manage users | ❌ | ❌ | ✅ |
| Update own profile & password | ✅ | ✅ | ✅ |

---

## Deployment (Render)

This project ships a `render.yaml` Blueprint for one-click Render deployment.

1. Log in to [Render](https://dashboard.render.com) and click **New → Blueprint**
2. Connect your GitHub repo `mhiskall282/npontu-technologies-sre`
3. Set the environment variable `APP_KEY` to the value from your local `.env`
4. Click **Apply** — Render runs `build.sh` then migrates and serves the app

The Blueprint provisions a **1 GB persistent disk** at `/var/data` for the SQLite database file, ensuring data survives redeploys.

---

## Documentation Index

| Document | Contents |
|---|---|
| [README.md](README.md) | This file — setup, architecture overview |
| [docs/requirements.md](docs/requirements.md) | Functional requirements + grading rubric |
| [docs/architecture.md](docs/architecture.md) | ERD, module map, deployment diagram |
| [docs/context.md](docs/context.md) | Brand guidelines, business context |
| [docs/FILE_REFERENCE.md](docs/FILE_REFERENCE.md) | Per-file purpose + interview Q&A |

---

## Git Conventions

```
feat:     New feature
fix:      Bug fix
docs:     Documentation only
test:     Adding or fixing tests
refactor: Code change without feature/fix
chore:    Build, tooling, config changes
style:    Formatting, no logic change
```

---

*Built for Npontu Technologies — "Making you free to achieve..."*
