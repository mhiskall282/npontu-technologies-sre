# Support Activity Tracker — Agent Operating Rules

> **Scope**: These rules apply to every agent turn in the `npontu-technologies-sre` workspace.
> They are authoritative. When in doubt, re-read this file before writing code.

---

## 1. Stack (Fixed — Do Not Deviate)

| Layer | Choice | Rationale |
|---|---|---|
| Framework | **Laravel 11 (LTS)** | Current LTS branch at time of assignment; aligns with PHP 8.2+ requirement |
| Language | **PHP 8.2+** | Required by assignment; use typed properties, enums, readonly where appropriate |
| Database | **MySQL 8.0+** | Specified by assignment; use InnoDB, proper FK constraints |
| Frontend templating | **Blade + Livewire 3** | Chosen over Inertia because: (a) Livewire avoids a JS build pipeline for a small internal tool, (b) real-time status updates (shift handover view) benefit from Livewire's wire:poll / reactive components without a full SPA, (c) evaluators can read pure PHP — no React layer to navigate. Inertia would be justified only for a richer SPA; this app's scope does not require it. |
| CSS | **Tailwind CSS v3** | Required by assignment; configured with Npontu brand tokens (see .agents/skills/npontu-brand-tokens) |
| Tests | **Pest** | Expressive, modern, integrates with Laravel natively; chosen over PHPUnit for readability in evaluation context |

**Changing any of the above requires explicit user approval and an update to this file.**

---

## 2. Coding Standards

### General
- Follow **PSR-12** strictly. Run `./vendor/bin/pint` before every commit.
- Use **strict types** (`declare(strict_types=1);`) in every PHP file.
- Class names: PascalCase. Methods/variables: camelCase. DB columns/routes: snake_case.
- No abbreviations in names (no ``, ``, ``). Full, readable identifiers.

### Architecture Rules (enforced — not suggestions)
| Rule | Rationale |
|---|---|
| **Thin controllers**: Controllers only handle HTTP glue — validate via Form Request, delegate to Action/Service, return response. | Graded on Code Clarity |
| **Form Requests** for all validation | Keeps controllers clean; self-documents inputs |
| **Policies** for all authorization | Prevents scattered checks |
| **Action classes** (`app/Actions/`) for all business logic | Single-responsibility, testable in isolation |
| **No logic in Blade/Livewire templates** — computed values belong in Livewire components or ViewModels | Templates are dumb renderers |
| **Service classes** (`app/Services/`) only when an Action needs to coordinate across multiple domains | Don't over-abstract single-step operations |

### Migrations
- Every migration **must implement `down()`** — a migration without a rollback will be rejected.
- Use descriptive migration names: `create_activities_table`, `add_resolved_at_to_activities_table`.
- Never rename or delete a column without a data-migration strategy documented in a comment.

---

## 3. Audit Trail (Core Requirement — Not Optional)

Every user-facing state change **must** be logged. See `.agents/skills/audit-trail/SKILL.md` for the implementation pattern.

Required fields on every audit log entry:
- `actor_id` (FK to users)
- `actor_name` (denormalised snapshot — users can be renamed)
- `subject_type` + `subject_id` (morphable)
- `event` (string: created, updated, status_changed, deleted)
- `old_values` (JSON, nullable)
- `new_values` (JSON, nullable)
- `ip_address`
- `created_at`

**This requirement is explicitly called out in the grading rubric.** Every controller action that mutates data must trigger a log entry. Silence on this = automatic deduction.

---

## 4. Security Defaults

- **CSRF**: All forms must use @csrf. Livewire handles this automatically — verify it is not disabled.
- **Mass-assignment protection**: Every Eloquent model must declare ``.
- **Authorization**: Every controller method must call `->authorize(...)` — middleware alone is not sufficient.
- **Secrets**: No secrets or API keys in tracked files. `.env.example` must be kept current whenever `.env` changes.
- **No `DB::statement` with raw user input** — always use query bindings.
- Passwords: use `Hash::make()` — never md5/sha1.

---

## 5. Tests (Mandatory — Not Optional)

Coverage areas with zero negotiation:
1. **Authentication**: login success, login failure (wrong password), logout, unauthenticated redirect
2. **Activity CRUD**: create, read (index + show), update, delete
3. **Status update flow**: update status to done/pending, verify remark is saved, verify audit log entry created
4. **Reporting / date-range query**: returns correct activities for given date range, excludes out-of-range records, handles edge cases

Test location: `tests/Feature/` for HTTP-level tests, `tests/Unit/` for Action/Service classes.
Use **Pest** feature test syntax. Use `RefreshDatabase` trait. Use factories for all test data.

---

## 6. Git Discipline

- **Conventional commits** format: `feat:`, `fix:`, `docs:`, `test:`, `refactor:`, `chore:`, `style:`
- One logical unit per commit.
- Never commit generated files (`/vendor`, `/node_modules`, `.env`).

---

## 7. Requirement Fidelity

- **Single source of truth**: `/docs/requirements.md`
- **Never invent a requirement** not present in that file. Flag it instead.
- If a requirement is ambiguous, add an `AMBIGUITY:` comment and surface it to the user before proceeding.
- The grading rubric is embedded in `/docs/requirements.md` — check work against it before marking anything complete.

---

## 8. UI / Brand

- Use the Npontu brand tokens defined in `.agents/skills/npontu-brand-tokens/SKILL.md`.
- Primary colour: `#1B6B3A` (Npontu green). Accent: `#F5C518` (gold/yellow). Alert/danger: `#E63946`.
- No generic Bootstrap-blue or grey SaaS aesthetic. The evaluators are Npontu staff.
- Geometric/angular section dividers, clean sans-serif (Inter), professional but not sterile.

---

## 9. What This Agent Must NOT Do

- Do not start writing application code until Prompt 2 (architecture) is approved.
- Do not modify `/docs/requirements.md` to suit convenience.
- Do not skip `down()` on any migration.
- Do not leave `dd()`, `dump()`, `var_dump()`, or `ray()` calls in committed code.
- Do not generate placeholder lorem-ipsum content in the actual app UI.
