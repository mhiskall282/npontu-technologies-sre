# Opsora SRE — Site Reliability Engineering Operations Platform

> **A mission-critical Laravel 11 + Flutter companion application** for 24/7 engineering operations teams to execute verified shift checklists, record status updates with immutable audit trails, manage two-way handovers, war rooms, and real-time operational telemetry.

🌐 **Production Deployment**: [https://npontu-support-tracker.onrender.com](https://npontu-support-tracker.onrender.com)

[![Backend Tests](https://img.shields.io/badge/backend%20tests-139%20passing%20(655%20assertions)-brightgreen)](tests/)
[![Mobile Tests](https://img.shields.io/badge/mobile%20tests-25%20passing-brightgreen)](npontu_sre_mobile/test/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red)](https://laravel.com)
[![Flutter](https://img.shields.io/badge/Flutter-3.24+-02569B?logo=flutter)](npontu_sre_mobile/)
[![Tailwind](https://img.shields.io/badge/Tailwind-3.x-cyan)](https://tailwindcss.com)
[![MVP Ready](https://img.shields.io/badge/MVP%20Pilot-Ready%20%26%20Verified-brightgreen)](docs/mvp/README.md)

---

## What This Application Does

Support teams managing live production systems need a lightweight, auditable tool to track what was checked, what was resolved, and what must be handed over to the next shift. This application provides:

| Feature | Description |
|---|---|
| **Public SRE Landing Page** | High-impact overview of Opsora's SRE platform (`GET /`): capability matrix, 4-step handover lifecycle, live telemetry probes, and 1-click test roles |
| **High-Level SRE Docs Portal** | High-level platform guide (`GET /docs`): 6 permanent chapters (`#quickstart`, `#architecture`, `#handover-flow`, `#mobile-setup`, `#governance`, `#faq`) for technical, non-technical, and executive stakeholders |
| **Mobile SRE Companion App** | Flutter 3.24+ mobile client for Android/iOS/Windows: offline-first local cache, zero-latency startup, active 3s chat sync, and animated onboarding walkthrough |
| **SRE Profile Inspection Modals** | Clickable operator cards across web & mobile displaying SRE seniority tiers (`L1` to `L5`), department/pod, designation, contact info, and active clearances |
| **Strict Role-Based UI Pruning** | Zero disabled clutter: buttons and navigation links a user lacks clearance for are completely removed from the DOM/screen |
| **Squeezed Collapsible Sidebar** | Sleek collapsible submenus (Operations, Comms, Supervisory, Docs) with subtle uptime indicator in footer |
| **Daily Shift Board** | Live checklist of today's activities — pending items glow amber, done items fade green, with task delegation |
| **Two-Way Shift Handshake** | Outgoing lead formal sign-off paired with incoming lead sign-on and verification acceptance remarks |
| **SRE Operations Comms** | Live team messaging hub: 1-on-1 direct chat, team channels (`#general-shift`), war rooms, Base64 PDF/image attachments, and `@mention` email alerts |
| **1-Click Email Reply Bridge** | Cryptographically signed HMAC SHA256 reply tokens allowing engineers to post to shift channels directly via email or 1-click web composer (`POST /api/webhooks/inbound-email`) |
| **Automated SRE Reports** | Scheduled automated daily, weekly, and monthly email digests (`php artisan reports:send-automated`) with SLA metrics and shift health KPIs |
| **System Health & Telemetry** | Multi-service probes (DB, cache, memory, mail), live 3s HUD streaming, 24h heartbeat, and public JSON API (`GET /health`) |
| **Play Store & App Store Compliance** | Full legal disclosures, mobile permissions audit, and Apple Guideline 5.1.1(v) account deletion request workflow |
| **Granular Privileges & Grades** | 9 configurable access checkboxes per user and L1–L5 SRE operational tiers |
| **Branded Error Pages & Security** | Custom SRE 419 (Session Expired), 404 (Route Not Found), 403 (Forbidden), 500 (Runtime Exception), and 503 (Maintenance) with zero mobile overflow, Livewire 419 interceptor, and redesigned operator sign-in with 1-click test credentials |
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
## Seeded Test Credentials (Local Development)

| Role | Email | Password | Scope |
|---|---|---|---|
| **Administrator** | `admin@npontu.local` | `password` | Full user & activity management |
| **Team Lead** | `lead@npontu.local` | `password` | Activity management & shift handover sign-off |
| **Support Agent** | `agent@npontu.local` | `password` | Activity checkoffs & remark updates |

> ⚠️ **Change all passwords immediately in production environments.**

---

## Running Tests

### Backend Pest Test Suite (Laravel 11)

```bash
# Full backend test suite (96 tests, 491 assertions)
php artisan test
# or
./vendor/bin/pest

# With verbose execution details
./vendor/bin/pest --verbose

# Run a specific test suite
./vendor/bin/pest tests/Feature/Api/ActivityApiTest.php
./vendor/bin/pest tests/Feature/Api/AuthApiTest.php
./vendor/bin/pest tests/Feature/DocsPortalTest.php
./vendor/bin/pest tests/Feature/OperationalCommunicationsAndPrivilegesTest.php
```

Tests use an **in-memory SQLite** database (configured in `phpunit.xml`) — zero external DB dependencies.

**Backend test coverage areas (96 tests / 491 assertions):**
- **Mobile REST API v1**: Complete test coverage for authentication, token revocation, activities CRUD, handovers two-way signoff, war rooms, messaging, system health probes, reports, and security audit trail.
- **Public SRE Landing Page**: Unauthenticated visitor showcase, 6 capability pillars, architecture walkthrough, pre-seeded test roles, authenticated SRE cockpit CTA
- **High-Level SRE Documentation Portal**: 5 permanent chapters (`#quickstart`, `#architecture`, `#handover-flow`, `#governance`, `#faq`), verification commands, and interactive FAQ accordion
- **Authentication & Security**: Login success, failure validation alerts, logout, redirect, session expiration banners
- **Custom Branded Error Handling**: 419 (Session Expired), 404 (Route Not Found), 403 (Forbidden), 500 (Runtime Error), 503 (Maintenance Mode) with zero mobile viewport overflow
- **Livewire 419 Interceptor**: Hook intercepting expired session tokens and redirecting cleanly to `/login?expired=1` without raw modal popups
- **Activity CRUD**: Create, read, update, soft-delete, with task assignment and delegation
- **Status Update Flow**: Status changes (Done/Pending) with mandatory remarks, incident escalation flags, and domain/audit logs
- **Operational Communications**: Direct 1-on-1 chats, team shift channels, incident war rooms, `@name` & `@all` email receipts, and Base64 PDF/image blob attachments
- **Email Reply Bridge & Inbound Webhook**: Cryptographically signed HMAC SHA256 tokens, 1-click web reply composer, and inbound email webhook parser (`POST /api/webhooks/inbound-email`)
- **Automated SRE Reports Scheduler**: `php artisan reports:send-automated {period=daily|weekly|monthly}` command validation and automated email dispatch
- **Shift Handover Handshake**: Outgoing briefing sign-off and incoming lead verification sign-on
- **Multi-Domain Reporting**: Custom date-range activity checks, handover audit reports, and operator work timelines & duty hours
- **System Health Diagnostics**: Live telemetry streaming, subsystem probes, and availability SLA metrics
- **Compliance Policies**: SLA 99.98% commitment, SOC2/SIEM audit policy, terms of service, and privacy standards

---

### Mobile Flutter Test Suite (npontu_sre_mobile)

```bash
cd npontu_sre_mobile

# Run all unit and widget tests (22 passing tests)
flutter test

# Run static analysis (0 warnings)
flutter analyze

# Verify code formatting
dart format --output=none --set-exit-if-changed .
```

**Mobile test coverage areas (22 unit & widget tests):**
- **Model JSON Deserialization**: `UserModel`, `ActivityModel`, `ShiftHandoverModel`, `ConversationModel`, `MessageModel`, `SystemHealthModel`, `AuditLogModel`, and `NotificationModel`.
- **Notification & Badge Counting**: Unit tests validating unread count badges, read transitions, and state copyWith.
- **UI Components & Badges**: Status badges (`DONE`, `PENDING`, `ACKNOWLEDGED`), Priority badges (`CRITICAL`, `HIGH`, `MEDIUM`, `LOW`), Skeleton loaders, and Empty/Error state widgets.
- **Screen Widget Tests**: `LoginScreen` rendering, input validation, authenticated session state transitions, and legal footer links.
- **Production Render Probes**: Connectivity and health probe validation against live endpoints.

---

## Local PC Testing & Android Emulator Guide

### 1. Testing the Web Backend on PC

```bash
# 1. Start local development server
php artisan serve --host=0.0.0.0 --port=8000

# 2. In a separate terminal, compile assets with hot reload
npm run dev

# 3. Access in browser: http://localhost:8000
# Log in with any pre-seeded persona: admin@npontu.local / lead@npontu.local / agent@npontu.local (password: password)
```

### 2. Testing the Mobile App on PC (3 Options)

#### Option A: Native Windows Desktop (Fastest — Zero Emulator Required)
```bash
cd npontu_sre_mobile
flutter run -d windows
```
*Compiles directly into a Windows native executable window on your PC. Connects to `http://localhost:8000/api/v1`.*

#### Option B: Android Studio Emulator (AVD Virtual Device)
1. In Android Studio, open **Virtual Device Manager** (`Tools` &rarr; `Device Manager`).
2. Create a virtual device: **Pixel 8**, system image: **API 34 (UpsideDownCake)** x86_64 with Google Play.
3. Start the emulator via GUI or command line:
   ```bash
   emulator -avd Pixel_8_API_34
   ```
4. Run the Flutter app targeting the emulator:
   ```bash
   cd npontu_sre_mobile
   flutter run -d emulator-5554
   ```
   > 💡 **Networking Note**: Inside the Android emulator, `http://localhost:8000` refers to the Android device itself. Android provides a loopback alias: **`http://10.0.2.2:8000/api/v1`** maps directly to your PC's `127.0.0.1:8000`.

#### Option C: Google Chrome Web Browser
```bash
cd npontu_sre_mobile
flutter run -d chrome
```

### 3. Installing Pre-Built Universal APK via ADB

Pre-built Universal Release APKs are automatically generated on every commit by our GitHub Actions CI workflow:

1. Download [`app-release.apk`](https://github.com/mhiskall282/npontu-technologies-sre/releases/latest/download/app-release.apk) from the latest release.
2. Connect your physical Android phone (with USB Debugging enabled) or start your emulator.
3. Install instantly via ADB:
   ```bash
   adb install -r app-release.apk
   ```

---

## Legal, Privacy Policy & App Store Compliance

The platform and companion mobile apps comply with:
- **Google Play User Data Policy**: Explicit disclosure of permissions, zero advertising SDKs, and transparent offline cache handling.
- **Apple App Store Review Guideline 5.1.1(v)**: Full account deletion and data extraction request support.
- **Ghana Data Protection Act 2012 (Act 843)** & **ISO 27001 / PCI-DSS v4.0**:
  - Web Privacy Policy: Accessible at [`/privacy-policy`](https://npontu-support-tracker.onrender.com/privacy-policy).
  - In-App Mobile Privacy Sheet: Accessible via `Settings` &rarr; `Privacy Policy & Data Handling` or on `LoginScreen`.
  - Account Deletion Requests: Submit in-app via *Request Account Deletion* or email to Data Protection Officer at `dpo@npontu.com` (48hr acknowledgment, 30-day SLA).
  - Statutory 7-year cold-storage retention for immutable audit logs.

---

## Automated SRE Reports Command

The platform ships an automated multi-cadence reporting engine registered in `routes/console.php`:

```bash
# Send daily shift digest (dispatches every night at 23:55 GMT)
php artisan reports:send-automated daily

# Send weekly executive SRE report (dispatches every Sunday at 23:55 GMT)
php artisan reports:send-automated weekly

# Send monthly SRE operational review (dispatches last day of month at 23:55 GMT)
php artisan reports:send-automated monthly

# Send report to a specific recipient address
php artisan reports:send-automated daily --to=lead@npontu.local
```

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
│   ├── Console/Commands/           # Artisan commands
│   │   └── SendAutomatedReportsCommand.php # Scheduled daily/weekly/monthly reports
│   ├── Http/
│   │   ├── Controllers/            # Thin HTTP glue (validate → delegate → respond)
│   │   │   ├── ActivityController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── DocsController.php      # High-level documentation portal
│   │   │   ├── EmailReplyController.php # 1-Click web replies & inbound webhook
│   │   │   ├── HealthController.php    # Interactive telemetry & health probes
│   │   │   ├── LandingController.php   # Public SRE landing page
│   │   │   ├── MonitoringController.php # SRE operational oversight & audit log stream
│   │   │   ├── PolicyController.php    # SLA, SIEM, and governance policies
│   │   │   ├── ReportController.php    # Date-range queries, CSV & print views
│   │   │   ├── SettingsController.php  # User profile & credential settings
│   │   │   └── Admin/
│   │   │       ├── ActivityController.php
│   │   │       └── UserController.php  # Granular privileges & technical grades
│   │   ├── Middleware/
│   │   │   ├── EnsureRole.php      # Role-based route guard
│   │   │   └── SecureHeaders.php   # Security HTTP headers
│   │   └── Requests/               # Form Requests (validation + authorization)
│   ├── Livewire/
│   │   ├── DailyActivityBoard.php  # Shift handover real-time board
│   │   ├── ActivityStatusUpdater.php # Inline status toggle component
│   │   └── OperationalChat.php     # Team channels, war rooms, Base64 attachments
│   ├── Mail/
│   │   ├── ActivityReportMail.php  # Mailable for manual activity reports
│   │   ├── AutomatedDigestReportMail.php # Scheduled daily/weekly/monthly digests
│   │   └── MessageMentionMail.php  # @mention receipts & email reply bridge
│   ├── Models/
│   │   ├── Activity.php            # Core operational check entity
│   │   ├── ActivityLog.php         # Append-only status checkoff log
│   │   ├── AuditLog.php            # Security/compliance change log
│   │   ├── Conversation.php        # Chat channels & war rooms
│   │   ├── Message.php             # Operational messages & Base64 attachments
│   │   ├── ShiftHandover.php       # Two-way shift handover agreements
│   │   └── User.php                # Auth user with role & privilege helpers
│   ├── Policies/
│   │   ├── ActivityPolicy.php      # Who can create/update/delete activities
│   │   └── UserPolicy.php          # Who can manage users
│   ├── Providers/
│   │   └── AppServiceProvider.php  # Layout component aliases & HTTPS enforcement
│   └── Services/
│       ├── AuditService.php        # Write immutable audit log entries
│       ├── EmailReplyTokenService.php # Cryptographic HMAC reply tokens
│       ├── ReportingService.php    # Date-range, handover & timeline queries
│       └── SystemHealthService.php # Multi-probe telemetry & health diagnostics
├── database/
│   ├── factories/                  # Model factories for seeding & testing
│   ├── migrations/                 # Versioned schema (all have down())
│   └── seeders/                    # Default data (users, sample activities)
├── docs/
│   ├── requirements.md             # Functional requirements + grading rubric
│   ├── architecture.md             # ERD, module boundaries, deployment diagram
│   ├── context.md                  # Brand guidelines, business context
│   ├── FILE_REFERENCE.md           # Per-file interview reference
│   └── PROJECT_SUBMISSION_REPORT.md # Formal submission report
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php   # SRE cockpit layout with left sidebar
│   │   ├── livewire/               # Livewire component views
│   │   ├── activities/             # Activity CRUD views
│   │   ├── admin/                  # Admin panel views
│   │   ├── docs/                   # High-level documentation portal
│   │   ├── emails/                 # Rich HTML email templates
│   │   ├── errors/                 # Branded 404, 419, 403, 500, 503 error pages
│   │   ├── health/                 # Interactive telemetry HUD
│   │   ├── messages/               # Operational chat & email reply screens
│   │   ├── monitoring/             # SRE monitoring console
│   │   ├── policies/               # SLA & governance policy pages
│   │   ├── reports/                # Reporting and chart views
│   │   ├── settings/               # Account settings views
│   │   └── auth/                   # Login form
│   └── css/app.css                 # Tailwind entry point + brand tokens
├── routes/
│   ├── web.php                     # Web routes, public pages & webhook endpoints
│   ├── auth.php                    # Login/logout routes
│   └── console.php                 # Artisan scheduler cron jobs
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

## Cross-Platform Mobile Application (Flutter — Android & iOS)

A native Flutter client (`npontu_sre_mobile`) built for site reliability engineers on call, field operators, and team leads managing production operations on Android and iOS devices.

### Architecture & Tech Stack
- **Framework**: Flutter 3.24+ (Dart 3.5+) on stable channel
- **Design System**: Material 3 styled with official Npontu Brand Tokens (`#1B6B3A` Forest Green, `#F5C518` Gold Accent, `#E63946` Alert Red, `#0F1A14` Dark Console Slate)
- **State Management**: Riverpod (`flutter_riverpod: ^2.6.1`) with feature-first modular structure
- **Networking**: Dio (`dio: ^5.11.1`) with centralized Bearer token interceptor, exponential backoff retries, and comprehensive error normalization (`ApiException`)
- **Navigation**: Declarative routing via GoRouter (`go_router: ^18.0.1`) with reactive authentication guards and redirection
- **Security Storage**: `flutter_secure_storage` storing API tokens encrypted in Android KeyStore (AES-GCM) and iOS Keychain (`kSecAttrAccessibleAfterFirstUnlock`)

### Mobile Feature Modules
1. **SRE Operational Dashboard**: Shift overview metrics (total, pending, done, acknowledged), active system health probes, unread chats, and active incident warnings.
2. **Daily Shift Activity Board**: Real-time checklist filtered by shift (`morning`, `afternoon`, `night`), status, or priority. Operators can update status inline with required remarks.
3. **Shift Handover Protocol & Sign-off**: Two-way operational handover lifecycle with outgoing supervisor sign-off and incoming lead acceptance remarks.
4. **Operations Messaging & War Rooms**: Shift channels (`#general-shift`), 1-on-1 direct operator messaging, active incident war rooms, and file/PDF attachment previews.
5. **System Health & Diagnostic HUD**: Live status of MySQL database, cache, system memory, background queues, and mail subsystem.
6. **Reporting & Compliance Metrics**: Real-time KPI summaries, date-range filtering, and SLA compliance statistics.
7. **Team Directory**: SRE operator directory with engineering tiers (L1–L5), departments, and on-call availability badges.
8. **Security Audit Trail**: Read-only timeline of all operational mutations with actor snapshots, IP addresses, and JSON before/after state diffs.

### Running the Mobile App Locally
```bash
# 1. Navigate to the mobile app directory
cd npontu_sre_mobile

# 2. Install dependencies
flutter pub get

# 3. Run against local Laravel backend
# For Android Emulator (using 10.0.2.2 bridge):
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1

# For iOS Simulator:
flutter run --dart-define=API_BASE_URL=http://localhost:8000/api/v1

# For Physical Device (replace with your machine LAN IP):
flutter run --dart-define=API_BASE_URL=http://192.168.1.100:8000/api/v1
```

---

## CI/CD Automation Workflows

Automated GitHub Actions pipelines ensure continuous code quality and release reliability:

| Pipeline | Path | Trigger | Steps |
|---|---|---|---|
| **Backend CI** | [`.github/workflows/backend-ci.yml`](.github/workflows/backend-ci.yml) | Push/PR to `main` (`app/`, `tests/`, etc.) | PHP 8.2 setup, Pint PSR-12 linting, SQLite migration & rollback verification, 96 Pest tests |
| **Mobile CI** | [`.github/workflows/flutter-ci.yml`](.github/workflows/flutter-ci.yml) | Push/PR to `main` (`npontu_sre_mobile/`) | Flutter SDK setup, `dart format` verification, `flutter analyze`, 15 unit/widget tests, debug APK build |

---

## Documentation Index

| Document | Contents |
|---|---|
| [README.md](README.md) | Project overview, web & mobile setup, architecture, and verification commands |
| [docs/mobile-expansion-audit.md](docs/mobile-expansion-audit.md) | Comprehensive initial architecture audit, database schemas, roles, and API gap analysis |
| [docs/mobile-api.md](docs/mobile-api.md) | Exhaustive REST API v1 developer reference with request/response envelopes |
| [docs/api/openapi.yaml](docs/api/openapi.yaml) | Complete OpenAPI 3.0 / Swagger specification covering all 33 endpoints |
| [docs/security/mobile-threat-model.md](docs/security/mobile-threat-model.md) | STRIDE threat model, mobile security vectors, token revocation, and residual risk mitigations |
| [docs/deployment/mobile-deployment.md](docs/deployment/mobile-deployment.md) | Backend hosting (Render/Forge), Google Play App Bundle (AAB), and iOS TestFlight procedures |
| [docs/deployment/store-publishing-guide.md](docs/deployment/store-publishing-guide.md) | Complete step-by-step Google Play Console & Apple App Store Connect submission guide |
| [docs/mobile-development.md](docs/mobile-development.md) | Mobile developer guide: emulator networking, Riverpod conventions, testing, and debugging |
| [docs/observability.md](docs/observability.md) | SRE observability, correlation IDs, logging standards, Prometheus/Grafana metrics, and runbooks |
| [docs/requirements.md](docs/requirements.md) | Functional requirements + original grading rubric |
| [docs/architecture.md](docs/architecture.md) | Original ERD, module map, and deployment diagram |
| [docs/context.md](docs/context.md) | Brand guidelines and business context |
| [docs/FILE_REFERENCE.md](docs/FILE_REFERENCE.md) | Per-file purpose + evaluation interview Q&A |

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
