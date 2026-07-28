# Interview Preparation — Support Activity Tracker

> A concise guide to the most common technical interview questions about this codebase.
> Study this alongside [FILE_REFERENCE.md](FILE_REFERENCE.md).

---

## System Design

**Q: Describe the high-level architecture of this application.**
> The application follows a **layered MVC architecture** built on Laravel 11. HTTP requests enter through controllers that validate via Form Requests and delegate business logic to Action classes. Livewire handles reactive UI without a full JavaScript SPA. Two separate database tables track domain events (`activity_logs`) and security/compliance events (`audit_logs`). All routes are guarded by `auth` middleware, with model-level authorization delegated to Laravel Policies.

**Q: Why two separate log tables instead of one?**
> `activity_logs` is a **domain log** — it records the sequence of status changes for operational reporting and the shift handover view. `audit_logs` is a **security/compliance log** — it records who changed what across all models, with IP address and JSON diffs. At scale, the audit log would be shipped to a SIEM tool like Splunk. Mixing these concerns would couple the business reporting query patterns with the security infrastructure.

**Q: How does the app know the "current status" of an activity without a mutable status field?**
> `activity_logs` is append-only. The current status is **derived** from the most recent log entry for a given `(activity_id, date)` pair — the row with `MAX(id)`. `ReportingService::dailySummary()` implements this as a single SQL query using `latestOfMany()`. This design preserves the full history of status changes (useful for audit and handover).

---

## Laravel Specifics

**Q: What is the difference between middleware and policies in this project?**
> **Middleware** (`EnsureRole`) provides route-level access control — it prevents users from even reaching a route they shouldn't access. **Policies** (`ActivityPolicy`, `UserPolicy`) provide model-level authorization — they decide whether a user can perform a specific action on a specific record. Both layers are needed: middleware stops broad route access, policies enforce fine-grained per-object rules.

**Q: What is a Form Request and why use one?**
> A Form Request is a Laravel class that encapsulates validation rules and authorization for an HTTP request. Each endpoint has its own Form Request (e.g. `StoreActivityRequest`), keeping controllers thin and self-documenting. The `authorize()` method delegates to the relevant Policy, and `rules()` returns the validation array.

**Q: Why are Action classes used instead of putting logic in controllers?**
> Controllers should only handle HTTP concerns: extract input, validate, delegate, return response. Action classes (`app/Actions/`) contain the pure business logic and are independently unit-testable without an HTTP request. They also make the codebase more readable — `CreateActivityAction::execute()` is unambiguously self-describing.

**Q: How does Livewire work in this project?**
> Livewire components are PHP classes that render Blade views. When a Livewire property changes (e.g. `$date` in `DailyActivityBoard`), Livewire sends an AJAX request to the server, re-renders the component server-side, and diffs/patches the DOM on the client. The shift board uses `wire:poll.30000ms` to auto-refresh every 30 seconds — no websockets required.

---

## Database Design

**Q: Walk me through the database schema.**
> Four main tables:
> 1. `users` — authentication + role (`agent`/`lead`/`admin`), soft-deleted
> 2. `activities` — the checklist definition (title, category, recurrence, is_active), soft-deleted
> 3. `activity_logs` — append-only event store; each row = one status change on one date; composite index on `(date, activity_id)`
> 4. `audit_logs` — polymorphic compliance log; stores actor snapshot, subject FK, event type, JSON diffs, IP address

**Q: What is a polymorphic relationship and where is it used?**
> A polymorphic relationship allows one table to belong to multiple different model types via `subject_type` (the model class name) and `subject_id` (the model PK). `audit_logs` is polymorphic — it can log changes to `Activity`, `User`, or any other model without requiring a separate audit table per model.

**Q: Why do all migrations implement `down()`?**
> `down()` enables `php artisan migrate:rollback`. Without it, a bad migration cannot be reversed without manual SQL. In production, rollback capability is a safety net for failed deployments. The project rules explicitly reject migrations without `down()`.

**Q: Why use soft deletes?**
> `SoftDeletes` sets `deleted_at` instead of physically removing the row. If an `Activity` is hard-deleted, all historical `activity_logs` records referencing it via FK would either violate constraints or be cascade-deleted — breaking historical reports. Soft deletes preserve referential integrity and allow restoration.

---

## Security

**Q: How does the app prevent CSRF attacks?**
> All state-mutating forms use the `@csrf` Blade directive, which renders a hidden `_token` input. Laravel's `VerifyCsrfToken` middleware validates this token on every POST/PUT/DELETE request. Livewire handles CSRF automatically on its AJAX requests.

**Q: How does the app prevent mass-assignment vulnerabilities?**
> Every Eloquent model declares an explicit `$fillable` array. Laravel's mass-assignment protection rejects any field not in `$fillable`, even if an attacker submits extra fields in the request body.

**Q: How is the audit trail tamper-resistant?**
> The `AuditService` reads actor identity exclusively from the server-side auth session (`Auth::user()`) and IP from `Request::ip()`. Client-submitted data is never used for audit identity. The `audit_logs` table has no `UPDATE` operations in the application code — it is append-only by convention.

**Q: How are passwords stored?**
> Passwords are hashed via `Hash::make()` which uses bcrypt by default. The `password` cast in `User` auto-hashes on assignment. Plaintext passwords are never stored or logged.

---

## Testing

**Q: What testing framework is used and why?**
> **Pest** — chosen over PHPUnit for its expressive, readable syntax. `it('does something', fn() => ...)` reads like a specification. It integrates natively with Laravel (uses the same underlying PHPUnit runner) and produces compact, scannable output.

**Q: What does `RefreshDatabase` do?**
> It wraps each test case in a database transaction that is rolled back after the test completes. This guarantees test isolation — no test can pollute another test's data. All test data is created fresh via factories within each test.

**Q: How are tests structured in this project?**
> `tests/Feature/` contains HTTP-level tests that exercise the full request lifecycle (routing → middleware → controller → response). `tests/Unit/` tests individual Action or Service classes in isolation. The feature tests cover the four mandatory areas: authentication, activity CRUD, status update flow, and date-range reporting.

---

## Frontend / Blade

**Q: Why Livewire instead of Vue/React/Alpine?**
> The application is a small internal tool. Livewire provides reactivity (date picker re-fetch, auto-poll) without a JavaScript build pipeline or a separate API layer. The evaluators are PHP developers — keeping the frontend in PHP/Blade reduces cognitive overhead and makes the code reviewable in a single language context.

**Q: How does the print-to-PDF feature work?**
> When the "Print PDF" button is clicked, it opens the reports page in a new tab with `?print=true`. The controller detects this parameter and returns the **full** un-paginated collection instead of a paginated subset. The view then auto-triggers `window.print()` after 800ms via a deferred `<script>` block. Print-specific CSS hides navigation, charts, and buttons.

**Q: How does the email report modal work?**
> The "Email Report" button opens a `<div id="emailModal">` hidden div via JavaScript. The modal contains a form with checkboxes for all registered users (pre-checked), an editable subject line, and a custom message textarea. On submit, the form POSTs to `reports.email` which dispatches `ActivityReportMail` to the selected recipients.

---

## Architecture Decisions

**Q: Why three separate roles instead of a permission-based system?**
> The assignment specifies three roles with clearly defined boundaries. A full permission system (e.g. Spatie Permissions) would be over-engineering for this scope. The role logic is centralised in `User::canManageActivities()` and the Policy classes — it's easy to extend if requirements change.

**Q: How would you scale this application to handle 100x the current load?**
> Key steps:
> 1. Switch `activity_logs` current-status derivation from a GROUP BY query to a **materialised read model** (a `current_status` column updated on write via an Eloquent event/observer)
> 2. Add **Redis caching** for the daily summary query (invalidated on each status update)
> 3. Move to a **managed MySQL** instance with read replicas for the reporting queries
> 4. Ship `audit_logs` to an external SIEM (e.g. via a queue job to Splunk/Datadog)
> 5. Use Laravel Horizon + Redis queues for the email dispatch

**Q: What would you change if you had more time?**
> - Add a comprehensive Notification system (Slack/email alerts for shifts with all activities still pending 30 minutes before shift end)
> - Implement API endpoints (`api.php`) to allow a mobile app or integration to update statuses
> - Add a Gantt-style shift timeline visualisation
> - Introduce a proper permission system (Spatie) to allow custom per-activity role overrides
> - Add unit tests for each Action class in isolation
