# Architecture — Support Activity Tracker

> Visual reference for the system architecture using Mermaid diagrams.
> All diagrams render natively in GitHub, VS Code, and most modern Markdown viewers.

---

## 1. Entity-Relationship Diagram (ERD)

Relationships between the four main database tables. `activity_logs` is an append-only event store;
`audit_logs` is the polymorphic security/compliance log.

```mermaid
erDiagram
    users ||--o{ activities : "creates"
    users ||--o{ activities : "assigned_to"
    users ||--o{ activity_logs : "logs_status"
    users ||--o{ audit_logs : "performs_actions"

    activities ||--o{ activity_logs : "has_many_logs"

    audit_logs }o--o| activities : "polymorphic_subject"
    audit_logs }o--o| users : "polymorphic_subject"

    users {
        bigint id PK
        string name
        string email
        string password
        string role
        string designation
        string phone
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    activities {
        bigint id PK
        string title
        text description
        string category
        string recurrence
        boolean is_active
        bigint created_by FK
        bigint assigned_to FK "nullable"
        timestamp deleted_at
        timestamp created_at
        timestamp updated_at
    }

    activity_logs {
        bigint id PK
        bigint activity_id FK
        date date
        string status
        text remark
        bigint updated_by FK
        string actor_name
        string actor_role
        string actor_designation
        string actor_ip
        timestamp created_at
    }

    audit_logs {
        bigint id PK
        bigint actor_id FK
        string actor_name
        string actor_role
        string actor_ip
        string subject_type
        bigint subject_id
        string event
        json old_values
        json new_values
        timestamp created_at
    }
```

---

## 2. 4-Tier Application Architecture

The system is engineered using an enterprise **4-Tier Architecture** pattern to isolate UI presentation, HTTP control flow, core business execution, and database persistence.

```mermaid
flowchart TB
    subgraph Tier1["Tier 1: Presentation Layer (UI)"]
        UI["Blade Templates + Livewire 3<br/>Tailwind CSS v4 + CDN Fallback<br/>Animated SRE Splash Screen"]
    end

    subgraph Tier2["Tier 2: Application / HTTP Control Layer"]
        MW["Middleware Stack<br/>(auth, EnsureRole, SecureHeaders, VerifyCsrfToken)"]
        FR["Form Requests<br/>(Validation + Policy Authorization)"]
        CTRL["Controllers & Livewire Components<br/>(Monitoring, Report, Settings, Admin, DailyActivityBoard)"]
    end

    subgraph Tier3["Tier 3: Business Logic & Domain Service Layer"]
        ACT["Action Classes<br/>(CreateActivityAction, UpdateActivityStatusAction, etc.)"]
        SVC["Domain Services<br/>(AuditService, ReportingService)"]
        NOTIF["Notification Services<br/>(WelcomeNotification, AdminPasswordResetNotification, ActivityReportMail)"]
    end

    subgraph Tier4["Tier 4: Data Access & Persistence Layer"]
        MDL["Eloquent ORM Models & Policies<br/>(User, Activity, ActivityLog, AuditLog, UserPolicy, ActivityPolicy)"]
        DB[("Render PostgreSQL Database")]
    end

    UI --> MW
    MW --> FR
    FR --> CTRL
    CTRL --> ACT
    CTRL --> NOTIF
    ACT --> SVC
    ACT --> MDL
    SVC --> MDL
    MDL --> DB

    style Tier1 fill:#f0fdf4,stroke:#1B6B3A,stroke-width:2px
    style Tier2 fill:#eff6ff,stroke:#2563eb,stroke-width:2px
    style Tier3 fill:#fef3c7,stroke:#d97706,stroke-width:2px
    style Tier4 fill:#f3e8ff,stroke:#9333ea,stroke-width:2px
```

### 4-Tier Breakdown & Key Responsibilities

| Tier | Component | Responsibility |
|---|---|---|
| **Tier 1: Presentation** | Blade, Livewire, Tailwind | Renders rich responsive dashboards, splash screens, real-time polling UI, and PDF report layouts. |
| **Tier 2: Application / HTTP** | Middleware, FormRequests, Controllers | Guards routes (RBAC), validates incoming payloads, handles session auth, and delegates requests. |
| **Tier 3: Business & Services** | Actions, Services, Mail/Notifications | Contains single-responsibility business logic, audit trail recording, reporting algorithms, and automated email dispatches. |
| **Tier 4: Data Persistence** | Eloquent Models, Policies, PostgreSQL | Handles ORM relationships, soft-delete scopes, polymorphic morphs, schema migrations, and database queries. |

---

## 3. Request Lifecycle — Status Update Flow

Detailed sequence of what happens when an operator marks an activity as Done.

```mermaid
sequenceDiagram
    actor Operator
    participant Livewire as ActivityStatusUpdater
    participant Request as UpdateActivityStatusRequest
    participant Policy as ActivityPolicy
    participant Action as UpdateActivityStatusAction
    participant AuditSvc as AuditService
    participant DB as Database

    Operator->>Livewire: Clicks "Done" and adds remark
    Livewire->>Request: validate(status, remark)
    Request->>Policy: authorize("updateStatus", $activity)
    Policy-->>Request: allowed (all auth users)
    Request-->>Livewire: validated

    Livewire->>Action: execute($activity, $date, $status, $remark, $user)
    Action->>DB: INSERT INTO activity_logs (append-only)
    DB-->>Action: row created

    Action->>AuditSvc: log($activityLog, "status_changed", old, new)
    AuditSvc->>DB: INSERT INTO audit_logs (IP from Request::ip())
    DB-->>AuditSvc: audit entry created

    Action-->>Livewire: done
    Livewire-->>Operator: Re-render board (status updated)
```

---

## 4. Role-Based Access Control (RBAC) Map

Which roles can access which routes and perform which actions.

```mermaid
flowchart LR
    subgraph Roles
        A["Admin"]
        L["Lead"]
        G["Agent"]
    end

    subgraph Routes["Protected Routes"]
        R1["GET /daily - Daily Board"]
        R2["GET /activities - Activity List"]
        R3["GET /reports - Reports"]
        R4["GET /monitoring - SRE Monitoring"]
        R5["admin/activities - Manage Activities"]
        R6["admin/users - Manage Users"]
        R7["PUT activity status - Update Status"]
        R8["GET /settings - Profile Settings"]
    end

    A --> R1
    A --> R2
    A --> R3
    A --> R4
    A --> R5
    A --> R6
    A --> R7
    A --> R8

    L --> R1
    L --> R2
    L --> R3
    L --> R4
    L --> R5
    L --> R7
    L --> R8

    G --> R1
    G --> R2
    G --> R7
    G --> R8

    style A fill:#1B6B3A,color:#fff
    style L fill:#F5C518,color:#000
    style G fill:#6B7280,color:#fff
```

---

## 5. Data Flow — Report Generation

How a date-range report is built, rendered, printed and emailed.

```mermaid
flowchart TD
    User["User applies filters"]
    RC["ReportController::index"]
    RS["ReportingService::exportQuery"]
    DB[("activity_logs + activities JOIN")]
    Check{"print=true?"}
    Page["Paginated view + Chart.js"]
    Print["Full collection view auto window.print"]
    Modal{"Email Report?"}
    Mail["ActivityReportMail sent"]
    CSV["StreamedResponse CSV download"]

    User --> RC
    RC --> RS
    RS --> DB
    DB --> Check
    Check -->|No| Page
    Check -->|Yes| Print
    Page --> CSV
    Page --> Modal
    Modal --> Mail
```

---

## 6. Module Dependency Graph

Shows which application modules depend on which other modules.

```mermaid
graph LR
    subgraph Controllers
        AC["ActivityController"]
        RC["ReportController"]
        MC["MonitoringController"]
        SC["SettingsController"]
        AU["Admin/UserController"]
        AA["Admin/ActivityController"]
    end

    subgraph Livewire
        DAB["DailyActivityBoard"]
        ASU["ActivityStatusUpdater"]
    end

    subgraph Actions
        CAA["CreateActivityAction"]
        UAA["UpdateActivityAction"]
        UASA["UpdateActivityStatusAction"]
        DAA["DeleteActivityAction"]
    end

    subgraph Services
        AS["AuditService"]
        RS["ReportingService"]
    end

    subgraph Models
        M_U["User Model"]
        M_A["Activity Model"]
        M_AL["ActivityLog Model"]
        M_AU["AuditLog Model"]
    end

    AC --> M_A
    AC --> M_AL
    RC --> RS
    RC --> M_U
    MC --> M_A
    MC --> M_AL
    MC --> M_AU
    MC --> M_U
    SC --> AS
    SC --> M_U
    AU --> M_U
    AA --> CAA
    AA --> UAA
    AA --> DAA

    DAB --> RS
    ASU --> UASA

    CAA --> M_A
    CAA --> AS
    UAA --> M_A
    UAA --> AS
    UASA --> M_AL
    UASA --> AS
    DAA --> M_A
    DAA --> AS

    RS --> M_AL
    RS --> M_A
    AS --> M_AU
```

---

## 7. Deployment Architecture (Render.com + Docker)

```mermaid
flowchart TB
    GH[("GitHub Repository")]
    R["Render Cloud Web Service"]
    DK["Docker Build Stage (PHP 8.2 + Apache)"]
    DB[("Render Free PostgreSQL Database")]
    CDN["Chart.js & Tailwind CDN"]
    SMTP["SMTP Mail Server"]

    GH -->|Git Push| R
    R --> DK
    DK --> DB
    DK --> CDN
    DK --> SMTP

    style GH fill:#24292e,color:#fff
    style R fill:#1B6B3A,color:#fff
    style DB fill:#fef3c7,color:#000
```

---

## 8. Security Enforcement Chain

Every request passes through multiple security checkpoints.

```mermaid
flowchart LR
    Req["HTTP Request"]
    CORS["HandleCors"]
    CSRF["VerifyCsrfToken"]
    Auth["Authenticate (auth)"]
    Role["EnsureRole (admin, lead)"]
    Policy["Laravel Policy"]
    Ctrl["Controller / Action"]
    Audit["AuditService"]

    Req --> CORS
    CORS --> CSRF
    CSRF --> Auth
    Auth --> Role
    Role --> Policy
    Policy --> Ctrl
    Ctrl --> Audit

    style Auth fill:#2563eb,color:#fff
    style Role fill:#F5C518,color:#000
    style Policy fill:#9333ea,color:#fff
    style Audit fill:#1B6B3A,color:#fff
```

---

---

## 9. Task Assignment & Delegation Architecture

Allows Team Leads and Administrators to optionally delegate operational checks to specific engineers while maintaining a general shift pool for unassigned tasks.

```mermaid
flowchart TD
    subgraph Delegation["Supervisor Delegation (Admin / Lead)"]
        ADMIN["Lead / Admin Console"]
        INLINE["Daily Board Inline Dropdown<br/>(assignActivity action)"]
        FORM["Create/Edit Activity Form<br/>(StoreActivityRequest / UpdateActivityRequest)"]
    end

    subgraph Data["Persistence & Compliance"]
        ACT["Activity (assigned_to FK)"]
        AUD["AuditLog (before/after diff)"]
    end

    subgraph OperatorView["Operator Experience (Daily Activity Board)"]
        FILTER["Interactive Filters<br/>('Assigned to Me' | 'Shift Pool' | Engineer)"]
        CARD["Personal Badge Highlight<br/>('Assigned to You' / Engineer Name)"]
        UPDATE["Instant Status Update (Done/Pending)"]
    end

    ADMIN --> INLINE
    ADMIN --> FORM
    INLINE -->|Livewire Mutation| ACT
    FORM -->|HTTP Mutation| ACT
    ACT -->|Trigger| AUD
    ACT --> FILTER
    FILTER --> CARD
    CARD --> UPDATE

    style Delegation fill:#eff6ff,stroke:#2563eb,stroke-width:2px
    style Data fill:#fef3c7,stroke:#d97706,stroke-width:2px
    style OperatorView fill:#f0fdf4,stroke:#1B6B3A,stroke-width:2px
```

---

## Architecture Decisions Log

| # | Decision | Chosen | Rejected | Rationale |
|---|---|---|---|---|
| 1 | Frontend reactivity | Livewire 3 | Inertia/React | No JS build pipeline; wire:poll handles live updates; PHP-only codebase |
| 2 | Status storage | Append-only `activity_logs` | Mutable status column | Preserves full timeline for shift handover and audit |
| 3 | Audit logging | Separate `audit_logs` table | Embedding in `activity_logs` | Different consumers: domain log vs security/SIEM log |
| 4 | Actor identity | Server-captured from Auth session | Client-submitted | Prevents bio spoofing |
| 5 | Soft deletes | `SoftDeletes` on Activity + User | Hard delete | Historical logs must not break when records are removed |
| 6 | Role system | String column + Policy | Spatie Permissions | YAGNI — 3 roles, fixed boundaries; no permission matrix needed |
| 7 | Monitoring access | Admin + Lead only | All users | Audit trails contain sensitive IP and change data |
| 8 | Database (production) | Render PostgreSQL | SQLite / MySQL | High durability, relational integrity, connection pooling on Render |
| 9 | Task Delegation | Optional Nullable FK (`users.id`) | Separate Team/Assignment Pivot | Preserves shift pool elasticity (null = shift pool) while giving 1-click personal accountability without relational overhead |
