# Support Activity Tracker

> A Laravel web application for operations support teams at Npontu Technologies to log daily
> activities, record status updates with full audit trails, and facilitate shift handovers.

## Overview

Support teams managing live systems need a lightweight, reliable tool to track what was checked,
what was resolved, and what needs to be handed over to the next shift. This application provides:

- **Activity logging** — record operational checks and tasks against a specific date
- **Status updates** — mark activities as Done or Pending with a remark, preserving full history
- **Shift handover view** — a scannable daily board that makes pending items visually unmissable
- **Historical reporting** — query activities across any custom date range
- **Audit trail** — every state change is logged with the actor, timestamp, IP, and before/after values
- **Authentication gate** — all data is protected behind login; two roles (Admin, Support)

Built as a take-home assignment for the Systems Reliability Engineer (NSS/Graduate) role at
**[Npontu Technologies](https://npontu.com)** — an AI, software, and IT consultancy based in Accra, Ghana.

---

## Tech Stack

| Layer | Technology | Version |
|---|---|---|
| Framework | Laravel | 11.x (LTS) |
| Language | PHP | 8.2+ |
| Database | MySQL | 8.0+ |
| Frontend | Blade + Livewire | Livewire 3.x |
| CSS | Tailwind CSS | 3.x |
| Tests | Pest | 2.x |
| Linter | Laravel Pint | latest |

---

## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.0+

---

## Setup

### 1. Clone the repository

```bash
git clone <repo-url> npontu-support-tracker
cd npontu-support-tracker
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies & build assets

```bash
npm install
npm run dev
```

### 4. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=npontu_tracker
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Create the database

```sql
CREATE DATABASE npontu_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Run migrations

```bash
php artisan migrate
```

### 7. Seed the database (creates default admin + sample data)

```bash
php artisan db:seed
```

Default admin credentials (seeded):
- Email: `admin@npontu.local`
- Password: `password` (**change immediately in production**)

### 8. Serve the application

```bash
php artisan serve
```

Open [http://localhost:8000](http://localhost:8000) in your browser.

---

## Running Tests

```bash
# Run the full test suite
./vendor/bin/pest

# Run with coverage report
./vendor/bin/pest --coverage

# Run a specific test file
./vendor/bin/pest tests/Feature/ActivityTest.php

# Run tests matching a description
./vendor/bin/pest --filter "status update"
```

All tests use an in-memory SQLite database by default. The `phpunit.xml` configures this:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

---

## Linting & Code Style

This project enforces PSR-12 via [Laravel Pint](https://laravel.com/docs/11.x/pint).

```bash
# Check for style violations (dry run)
./vendor/bin/pint --test

# Auto-fix all violations
./vendor/bin/pint
```

**Run Pint before every commit.**

---

## Folder Structure

```
.
+-- app/
|   +-- Actions/                  # Business logic (one class per operation)
|   |   +-- Activities/           # CreateActivityAction, UpdateActivityStatusAction, etc.
|   +-- Http/
|   |   +-- Controllers/          # Thin HTTP controllers — HTTP in/out only
|   |   +-- Requests/             # Form Requests for validation + authorization
|   +-- Livewire/                 # Livewire components (DailyActivityBoard, etc.)
|   +-- Models/                   # Eloquent models with $fillable, relationships
|   +-- Policies/                 # Authorization policies (ActivityPolicy, UserPolicy)
|   +-- Services/                 # Services for cross-domain coordination (AuditService)
+-- database/
|   +-- factories/                # Model factories for testing
|   +-- migrations/               # All migrations (with down() implemented)
|   +-- seeders/                  # Default data seeders
+-- docs/
|   +-- requirements.md           # Canonical functional requirements + grading rubric
|   +-- architecture.md           # ERD, module boundaries, deployment diagram
|   +-- context.md                # Business context, brand guidelines, reviewer persona
+-- resources/
|   +-- views/
|   |   +-- livewire/             # Livewire component Blade views
|   |   +-- layouts/              # App shell, navigation
|   |   +-- activities/           # Activity CRUD views
|   |   +-- reports/              # Reporting views
|   +-- css/app.css               # Tailwind entry point + Npontu brand tokens
+-- tests/
|   +-- Feature/                  # HTTP-level Pest feature tests
|   +-- Unit/                     # Unit tests for Action/Service classes
+-- .agents/                      # Agent operating rules and skills
|   +-- AGENTS.md                 # Agent rules for this workspace
|   +-- skills/
|       +-- audit-trail/          # Audit logging pattern
|       +-- authorization-checklist/ # Policy + Form Request pairing
|       +-- npontu-brand-tokens/  # Tailwind design tokens
```

---

## Key Design Decisions

| Decision | Rationale |
|---|---|
| **Livewire over Inertia** | No JS build complexity; reactive updates (wire:poll) benefit shift handover view; evaluators read pure PHP |
| **Action classes in app/Actions/** | Thin controllers; isolated, testable business logic; consistent with Laravel ecosystem conventions |
| **Polymorphic audit_logs table** | Single audit table covers all subjects (activities, users) without schema explosion |
| **Denormalised actor_name in audit logs** | Preserves historical accuracy even if a user renames their account |
| **Soft deletes on activities** | Historical reports must not break if an activity is deleted |
| **Two roles only (admin + support)** | Matches the brief; no gold-plating |

---

## Documentation

- [Requirements & Grading Rubric](docs/requirements.md)
- [Architecture (ERD, module map)](docs/architecture.md)
- [Brand & Business Context](docs/context.md)

---

## Screenshots

> To be added after the UI is built. Placeholder sections:

| View | Screenshot |
|---|---|
| Login page | _coming_ |
| Daily activity board (shift handover) | _coming_ |
| Activity create / update form | _coming_ |
| Reporting / date-range query | _coming_ |
| Audit log timeline | _coming_ |

---

## Contributing / Submission Notes

- This repository follows **conventional commits**: `feat:`, `fix:`, `test:`, `refactor:`, `docs:`, `chore:`
- Each commit represents one logical unit of work
- The `.env` file is not tracked; use `.env.example` as the template
- `vendor/` and `node_modules/` are gitignored

---

*Built for Npontu Technologies — "Making you free to achieve..."*
