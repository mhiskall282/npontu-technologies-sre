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
- [x] An authenticated user can create a new activity.
- [x] The activity is associated with the date it is logged for.
- [x] The creating user and timestamp are recorded.

---

### FR-1.1 — Team Task Assignment & Delegation (Enterprise Extension)

Administrators and Team Leads must be able to **optionally assign operational checks** to specific support engineers or leave them unassigned in the general shift pool.

**Capabilities:**
- **Optional Delegation**: When creating or editing an activity, supervisors can select a designated assignee from active team members.
- **Personal Queue**: On the Daily Activity Board, engineers can filter by "Assigned to Me" to immediately view their dedicated tasks, while seeing clear visual badges ("Assigned to You", Engineer name & role).
- **Inline Board Reassignment**: Team Leads and Admins can reassign checks directly from the Daily Activity Board dropdown without leaving the operational screen.
- **Bulk Reassignment**: Supervisors can select multiple pending checks via checkboxes and delegate them to a team member (or return to pool) with 1 click.
- **Audit Logging**: Any assignment or reassignment captures before/after values in the immutable security audit log.

**Acceptance criteria:**
- [x] `assigned_to` nullable foreign key to `users` with index on `activities`.
- [x] Form requests validate assignee existence in `users`.
- [x] Daily Activity Board provides "Assigned to Me", "Shift Pool (Unassigned)", and per-engineer filtering.
- [x] Non-supervisors cannot reassign tasks; supervisors can reassign inline.
- [x] Supervisors can bulk select and delegate checks.
- [x] Audit trail captures all assignment mutations.

---

### FR-1.2 — SRE Operational Priority, SLA Targets, and Pinned Checks (Enterprise Extension)

Critical operational checks (e.g. Core Payment Gateway Heartbeats, Database Replication Health) require elevated operational urgency and clear completion targets.

**Capabilities:**
- **Priority Tiers**: Each activity defines a priority tier (`critical` [P1], `high` [P2], `medium` [P3], `low` [P4]).
- **SLA Target Times**: Specific target checkoff time (e.g. `08:30 GMT`) displayed as a badge on the board.
- **Pinned Checks**: Important checks can be pinned (`is_pinned = true`) to float directly to the top of the daily shift checklist.
- **Intelligent Shift Ordering**: Daily shift board sorts pending tasks with pinned first, followed by priority descending (Critical -> High -> Medium -> Low), then title.

**Acceptance criteria:**
- [x] `priority`, `sla_time`, `is_pinned` columns with indices on `activities`.
- [x] Form requests validate priority and SLA formats.
- [x] Daily board orders pinned and high-priority checks at the top.
- [x] Visual badges for P1/P2/P3/P4 and SLA time windows.

---

### FR-4.1 — Formal SRE Shift Handover Management & Incident Escalation (Enterprise Extension)

Enforces operational continuity across 24/7 SRE shifts (Morning, Afternoon, Night) with digital briefing sign-offs and incident ticket references.

**Capabilities:**
- **Shift Handover Briefings (`shift_handovers`)**: Outgoing shift leads compose formal briefings, tag incoming shift leads, summarize operational status, and record blocker notes.
- **Operational Metrics Snapshot**: Automatically freezes and records the exact pending vs completed task counts at the moment of handover sign-off.
- **Incident Escalation in Checkoffs**: Operators can toggle "Flag Incident Escalation" and attach a tracking ticket ID (e.g., `INC-1042`) during checkoff updates.
- **Live Board Alerting**: P1/P2 counter, active incident alerts, and latest shift handover briefing banner rendered live on the Daily Activity Board.

**Acceptance criteria:**
- [x] Dedicated `shift_handovers` table with outgoing/incoming lead foreign keys, shift enum, summary, incidents, metrics snapshot, and timestamp.
- [x] `incident_ticket` and `is_escalated` fields in `activity_logs`.
- [x] Digital sign-off action with compliance audit trail logging.
- [x] Daily board surfaces active incident alerts and handover briefing banner.

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
