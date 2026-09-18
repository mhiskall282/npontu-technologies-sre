# Opsora Product — Daily Shift Checklist & Board

> **Status:** IMPLEMENTED  
> **Route:** `/daily` (`DailyActivityBoard` Livewire component)

The Daily Shift Board is the central operational interface for on-duty SREs.

---

## 1. Board Features
- **Recurrence Filtering**: Switch between `daily`, `weekly`, and `monthly` checklist tracks.
- **Assignment Scoping**: Filter tasks by "Assigned to Me" or view the unassigned shift queue.
- **Inline Status Updates**: Mark checks as `done` or `pending` with mandatory resolution remarks.
- **Incident Escalation**: Flag active operational incidents with external ticket references (Jira, ServiceNow, PagerDuty).
- **Bulk Delegation**: Supervisors can delegate unassigned checks to on-duty engineers in a single batch.
- **Automatic Audit**: Every status change generates an audit log entry detailing the actor and status change.
