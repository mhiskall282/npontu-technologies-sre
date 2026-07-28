# Requirements — Support Activity Tracker

> **Status**: Canonical. Do not modify without explicit user instruction.
> **Source**: Take-home assignment brief, Npontu Technologies SRE (NSS/Graduate) role.
> **Deadline**: 3 days from receipt of brief.
> Last updated: 2026-07-28

---

## Project Summary

Build a Laravel web application for an **applications support team** to track their daily operational activities, record status updates, and facilitate shift handovers. The system must provide a clear audit trail of who did what and when.

---

## Functional Requirements

### FR-1 — Activity Input

Support team members must be able to **create new activity records**. An activity represents a tracked operational metric or task for a given day.

**Examples of activities:**
- "Daily SMS count in comparison to SMS count from logs"
- "API uptime check vs monitoring dashboard reading"
- "Database backup verification"

**Required fields (minimum):**
- Activity title / description
- Date of activity
- Category or type (if applicable — implementer's discretion, see Requirement Interpretation rubric criterion)
- Initial status (default: Pending)

**Acceptance criteria:**
- [ ] An authenticated user can create a new activity.
- [ ] The activity is associated with the date it is logged for.
- [ ] The creating user and timestamp are recorded.

---

### FR-2 — Status Update

Authenticated support personnel must be able to **update the status** of any activity to one of:
- Pending (default)
- Done

Each status update must include an **optional remark** (free-text note about the update).

**Acceptance criteria:**
- [ ] Any authenticated user can update the status of an existing activity.
- [ ] A remark field is presented on the update form.
- [ ] Each status change is stored as a discrete update record (not just overwriting the activity row), so history is preserved.
- [ ] The UI clearly shows the current status.

---

### FR-3 — Personnel Bio Capture on Update

Every status update must **capture and store** the following about the person making the update:

| Field | Notes |
|---|---|
| Full name | From user profile |
| Role / designation | From user profile |
| Timestamp | Exact datetime of the update |
| IP address | For audit trail integrity |

**Acceptance criteria:**
- [ ] Personnel details are stored alongside each update record (not just a foreign key — denormalise name/role as a snapshot in case user records change).
- [ ] The shift-handover view (FR-4) must surface this information.
- [ ] This information must be in the audit log (see AGENTS.md §3).

---

### FR-4 — Daily View & Shift Handover

A **daily dashboard** must show:
- All activities logged for the current day (or a selected date).
- For each activity: its current status, all updates made to it that day (with remark + personnel info from FR-3), and whether it is still pending.
- **Pending items must be visually prominent** — this view is the primary tool for shift handover. An outgoing shift must be able to hand over to an incoming shift using this page alone.

**Acceptance criteria:**
- [ ] Default view shows today's activities.
- [ ] User can select a different date to view that day's snapshot.
- [ ] Pending activities are clearly distinguished from done ones (colour, badge, section grouping — implementer's choice).
- [ ] Each activity shows the full update timeline for that day.
- [ ] The page is printable / easy to screenshot for handover reports.

---

### FR-5 — Reporting & History

A **reporting view** must allow querying activity history over a **custom date range**.

**Acceptance criteria:**
- [ ] User selects a start date and end date.
- [ ] System returns all activities (and their updates) within that range.
- [ ] Results are filterable by status (pending / done / all).
- [ ] The query is performant on at least 12 months of data (add appropriate DB indexes).
- [ ] Export to CSV or PDF is desirable but not required for the base submission (flag as enhancement if implemented).

---

### FR-6 — Authentication Gate

**All routes** (except the login page itself) must require authentication. No data is accessible to unauthenticated users.

**Acceptance criteria:**
- [ ] Visiting any protected route while unauthenticated redirects to login.
- [ ] Laravel Breeze or a custom auth scaffold is acceptable.
- [ ] Roles: minimum two roles — `admin` (can manage users, view all) and `support` (can create/update activities). Role implementation details are at the implementer's discretion; document the decision.
- [ ] Remember-me and session timeout are desirable but not required for base submission.

---

## Non-Functional Requirements

| Category | Requirement |
|---|---|
| Framework | Must be built in **Laravel** |
| Code quality | PSR-12; thin controllers; Form Requests; Policies; Action classes |
| Audit trail | Every state change logged with actor + timestamp + before/after diff |
| Security | CSRF on all forms; mass-assignment protection; no secrets in git |
| Tests | Pest tests covering auth, CRUD, status flow, reporting query |
| Migrations | All migrations must be reversible (`down()` implemented) |
| Commits | Conventional commits, one logical unit per commit |
| Documentation | README with setup instructions; code must be self-explanatory |

---

## Grading Rubric

The submission will be evaluated on the following criteria (weightings not specified in brief — treat all as high priority):

| Criterion | What evaluators will look for |
|---|---|
| **Logic** | Does the application correctly implement all six functional requirements? Are edge cases handled? Is business logic in the right layer (Actions/Services, not controllers/views)? |
| **Code Clarity** | Is the code readable without inline explanation? Are names meaningful? Is architecture consistent? Would a new team member understand it in one sitting? |
| **UI Innovation** | Does the interface go beyond a plain CRUD form? Is the shift-handover view genuinely useful at a glance? Does the design reflect Npontu's brand and professional standards? |
| **Requirement Interpretation** | Did the implementer understand the spirit of the requirements, not just the letter? Are reasonable assumptions documented? Is scope appropriately scoped (no gold-plating, no under-delivery)? |
| **Non-Functional Requirements** | Are security, audit trail, test coverage, and code standards actually implemented — not just mentioned in a README? |

---

## Out of Scope (for base submission)

The following are explicitly **not** required unless noted as enhancements:
- Email / SMS notifications
- Real-time push (websockets / broadcasting)
- Mobile app or API-first architecture
- Multi-tenancy / multiple organisations
- File attachment uploads
- CSV/PDF export (desirable enhancement — flag if implemented)

---

## Open Questions / Ambiguities

> Items flagged here were not specified in the brief. Implementer decisions are documented below.

| # | Question | Decision |
|---|---|---|
| 1 | How many roles? | Two: `admin` and `support`. Admin can manage users; Support can log and update activities. Both can view reports. |
| 2 | Can any user update any activity, or only the creator? | Any authenticated user can update any activity (shift handover context — the person updating may not be the one who created it). |
| 3 | Is soft-delete required? | Yes, for activities — so historical reports are not broken by deletions. |
| 4 | Should update history be per-day only, or all time? | All time — FR-4 filters to a day, FR-5 queries across date ranges. The underlying history is always preserved. |
