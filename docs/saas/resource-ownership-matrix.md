# Opsora SaaS Transformation — Resource Ownership Matrix

> **Status**: Approved Governance Matrix (Stage 1)

---

| Entity / Resource | Primary Owner | Secondary Scope | Creation Rights | Access Boundaries | Deletion Policy |
|---|---|---|---|---|---|
| **`User`** | Platform | Self | Self-Registration / Admin Invite | Global Identity across Workspaces | Anonymized Soft-Delete |
| **`Personal Workspace`** | User | None | Automatic upon User Registration | Restricted solely to owning User | Owner Deleted -> Cascaded |
| **`Organization`** | Organization Owner | Organization Admins | Application & Approval Pipeline | Members with valid OrganizationMembership | Soft-Delete with 30-day grace |
| **`OrganizationApplication`** | Platform Admin | Applicant User | Any Authenticated User | Applicant & Platform Admins | Retained permanently for audit |
| **`Workspace`** | Organization | Workspace Admins | Organization Owner / Admin | Users with valid WorkspaceMembership | Archive or Soft-Delete |
| **`Activity` (Daily Check)** | Workspace | Assigned Operator | Workspace Lead / Admin / Engineer | Current Workspace Members only | Soft-delete with historical preserve |
| **`ActivityLog` (Status History)**| Workspace | Activity | Authenticated Operator with check rights | Current Workspace Members only | Immutable append-only record |
| **`ShiftHandover`** | Workspace | Shift Leads | Outgoing Lead / Incoming Lead | Current Workspace Members only | Immutable compliance record |
| **`Conversation` (Channel)** | Workspace | Channel Participants | Workspace Admin / Lead / User | Current Workspace Members in channel | Soft-delete by creator/admin |
| **`Message`** | Conversation | Author | Participant in Conversation | Channel Members only | Soft-delete with tombstone |
| **`OperationalNotification`** | User | Workspace | System Events / Action Triggers | Recipient User only | Dismissal / Purge after 90 days |
| **`AuditLog`** | Platform / Workspace | Actor User | System Event Listeners & AuditService | Tenant Admin / Platform Auditor | Immutable, write-once |
| **`Subscription` & Plan** | Organization | Platform Billing | Platform Admin / Automated Engine | Org Owner & Billing Managers | Historical records preserved |
| **`LicenseKey`** | Organization | Platform Admin | Platform Commercial Team | Org Owner & Platform Admins | Revocable, auditable |
| **`Deployment`** | Organization | Platform DevOps | Platform DevOps / Automated Engine | Org Admins & Platform DevOps | Managed lifecycle state machine |
