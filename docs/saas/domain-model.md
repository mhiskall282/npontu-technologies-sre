# Opsora SaaS Transformation — Multi-Tenant Domain Model

> **Status**: Approved Domain Design (Stage 1 & 2)  
> **Target Entities**: Identity, Organizations, Workspaces, Deployments, Entitlements, Operations

---

## 1. Core Domain Hierarchy

```
[ Platform Control Plane ]
  ├── User (Global Identity)
  │    ├── Personal Workspace (1:1 per user)
  │    └── Organization Memberships (1:N)
  │
  ├── Organization (Commercial & Governance Tenant)
  │    ├── Organization Applications & Reviews
  │    ├── Workspaces (1:N operational environments)
  │    ├── Subscriptions & Entitlements (1:N)
  │    ├── License Keys (1:N)
  │    └── Deployments (Shared SaaS / Dedicated Managed / Self-Hosted)
  │
  └── Workspace (Operational Execution Plane)
       ├── Workspace Memberships (Roles: Admin, Lead, Engineer, Viewer)
       ├── Daily Activities & Checklists
       ├── Activity Update History Logs
       ├── Shift Handover Briefings & Sign-offs
       ├── Operational Conversations (Channels, War Rooms, Direct)
       ├── Operational Notifications
       └── Compliance Audit Trail Logs
```

---

## 2. Detailed Entity Specifications

### 2.1 Identity & Organizations
- **`User`**:
  - `id`: BigInt (PK)
  - `name`: String
  - `email`: String (Unique)
  - `password`: String (Hashed bcrypt)
  - `phone`: String (Nullable)
  - `is_platform_admin`: Boolean (Default false)
  - `platform_role`: String (Nullable — `owner`, `admin`, `support`, `auditor`)
  - `timestamps`

- **`Organization`**:
  - `id`: BigInt (PK)
  - `uuid`: UUID (Public external reference)
  - `name`: String (e.g. "Acme FinTech SRE")
  - `slug`: String (Unique slug for routing)
  - `company_code`: String (Unique 6-character code for mobile onboarding, e.g. `ACM-410`)
  - `status`: Enum (`pending_review`, `approved`, `active`, `suspended`, `archived`)
  - `tier`: String (`free`, `starter`, `pro`, `enterprise`)
  - `deployment_model`: Enum (`shared_saas`, `dedicated_managed`, `customer_funded`, `customer_hosted`)
  - `preferred_region`: String (`us-east`, `eu-west`, `af-south`, `ap-southeast`)
  - `billing_email`: String (Nullable)
  - `settings`: JSON (Security policies, session timeout, domain whitelists)
  - `created_by_user_id`: FK -> `users.id`
  - `timestamps`

- **`OrganizationApplication`**:
  - `id`: BigInt (PK)
  - `organization_id`: FK -> `organizations.id`
  - `applicant_user_id`: FK -> `users.id`
  - `company_website`: String (Nullable)
  - `corporate_domain`: String (Nullable)
  - `team_size`: String
  - `use_case`: Text
  - `status`: Enum (`pending`, `under_review`, `auto_approved`, `manually_approved`, `rejected`, `info_requested`)
  - `review_notes`: Text (Nullable)
  - `reviewed_by_user_id`: FK -> `users.id` (Nullable)
  - `reviewed_at`: Timestamp (Nullable)
  - `timestamps`

- **`OrganizationMembership`**:
  - `id`: BigInt (PK)
  - `organization_id`: FK -> `organizations.id`
  - `user_id`: FK -> `users.id`
  - `role`: Enum (`owner`, `admin`, `member`, `billing_manager`, `auditor`)
  - `department`: String (Nullable)
  - `grade`: String (Nullable — `L1`, `L2`, `L3`, `L4`, `L5`)
  - `privileges`: JSON (Granular permission flags)
  - `status`: Enum (`active`, `invited`, `suspended`)
  - `timestamps`

### 2.2 Workspaces
- **`Workspace`**:
  - `id`: BigInt (PK)
  - `uuid`: UUID
  - `organization_id`: FK -> `organizations.id` (Nullable if personal workspace)
  - `owner_user_id`: FK -> `users.id` (For personal workspaces)
  - `name`: String (e.g. "Production Core Services")
  - `slug`: String (Unique per organization)
  - `subdomain`: String (Nullable, unique globally, e.g. `acme-prod`)
  - `custom_domain`: String (Nullable, unique globally)
  - `is_personal`: Boolean (Default false)
  - `status`: Enum (`active`, `maintenance`, `suspended`, `archived`)
  - `retention_days`: Integer (Default 90)
  - `settings`: JSON (Timezone, shift schedules, SLA defaults)
  - `timestamps`

- **`WorkspaceMembership`**:
  - `id`: BigInt (PK)
  - `workspace_id`: FK -> `workspaces.id`
  - `user_id`: FK -> `users.id`
  - `role`: Enum (`admin`, `lead`, `engineer`, `viewer`)
  - `created_at`, `updated_at`

### 2.3 Operational Entities (Scoped to `workspace_id`)
- **`activities`**:
  - Added: `workspace_id`: FK -> `workspaces.id` (Indexed)
  - Composite indexes: `INDEX (workspace_id, date, status)`, `INDEX (workspace_id, is_pinned, priority)`
- **`activity_logs`**:
  - Added: `workspace_id`: FK -> `workspaces.id` (Indexed)
- **`shift_handovers`**:
  - Added: `workspace_id`: FK -> `workspaces.id` (Indexed)
- **`conversations`**:
  - Added: `workspace_id`: FK -> `workspaces.id` (Indexed)
- **`operational_notifications`**:
  - Added: `workspace_id`: FK -> `workspaces.id` (Nullable for system-wide notifications)
- **`audit_logs`**:
  - Added: `workspace_id`: FK -> `workspaces.id` (Nullable for platform-level events)
  - Added: `organization_id`: FK -> `organizations.id` (Nullable)
