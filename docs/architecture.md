# Architecture — Support Activity Tracker

> Visual reference for the system architecture using Mermaid diagrams.
> All diagrams render natively in GitHub, VS Code, and most modern Markdown viewers.

---

## 1. Entity-Relationship Diagram (ERD)

Relationships between the four main database tables. `activity_logs` is an append-only event store;
`audit_logs` is the polymorphic security/compliance log.

```mermaid
erDiagram
    users ||--o{ activities : "creates (created_by)"
    users ||--o{ activity_logs : "logs status (updated_by)"
    users ||--o{ audit_logs : "actor_id"

    activities ||--o{ activity_logs : "has many log events"

    audit_logs }o--o| activities : "polymorphic subject (optional)"
    audit_logs }o--o| users : "polymorphic subject (optional)"

    users {
        bigint id PK
        string name
        string email
        string password "hashed"
        enum role "admin | lead | agent"
        string designation
        string phone
        timestamp deleted_at "soft delete"
        timestamp created_at
        timestamp updated_at
    }

    activities {
        bigint id PK
        string title
        text description
        string category "Application | Infrastructure | DB | Network | Security"
        enum recurrence "daily | adhoc"
        boolean is_active
        bigint created_by FK
        timestamp deleted_at "soft delete"
        timestamp created_at
        timestamp updated_at
    }

    activity_logs {
        bigint id PK
        bigint activity_id FK
        date date "INDEX"
        enum status "pending | done"
        text remark
        bigint updated_by FK "nullable"
        string actor_name "denormalised snapshot"
        string actor_role "denormalised snapshot"
        string actor_designation
        string actor_ip
        timestamp created_at
    }

    audit_logs {
        bigint id PK
        bigint actor_id FK "nullable"
        string actor_name "denormalised snapshot"
        string actor_role
        string actor_ip "server-captured"
        string subject_type "polymorphic"
        bigint subject_id "polymorphic"
        string event "created | updated | status_changed | deleted | profile_updated | password_changed"
        json old_values "nullable"
        json new_values "nullable"
        timestamp created_at "immutable — no updated_at"
    }
```

---

## 2. 4-Tier Application Architecture

The system is engineered using a enterprise **4-Tier Architecture** pattern to isolate UI presentation, HTTP control flow, core business execution, and database persistence.

```mermaid
flowchart TB
    subgraph Tier1["Tier 1: Presentation Layer (UI)"]
        UI["Blade Templates + Livewire 3\nTailwind CSS v4 + CDN Fallback\nAnimated SRE Splash Screen"]
    end

    subgraph Tier2["Tier 2: Application / HTTP Control Layer"]
        MW["Middleware Stack\n(auth, EnsureRole, SecureHeaders, VerifyCsrfToken)"]
        FR["Form Requests\n(Validation + Policy Authorization)"]
        CTRL["Controllers & Livewire Components\n(Monitoring, Report, Settings, Admin, DailyActivityBoard)"]
    end

    subgraph Tier3["Tier 3: Business Logic & Domain Service Layer"]
        ACT["Action Classes\n(CreateActivityAction, UpdateActivityStatusAction, etc.)"]
        SVC["Domain Services\n(AuditService, ReportingService)"]
        NOTIF["Notification Services\n(WelcomeNotification, AdminPasswordResetNotification, ActivityReportMail)"]
    end

    subgraph Tier4["Tier 4: Data Access & Persistence Layer"]
        MDL["Eloquent ORM Models & Policies\n(User, Activity, ActivityLog, AuditLog, UserPolicy, ActivityPolicy)"]
        DB[(Render Free PostgreSQL / SQLite Database)]
    end

    UI -->|HTTPS User Interaction| MW
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
    participant Livewire as ActivityStatusUpdater<br/>(Livewire Component)
    participant Request as UpdateActivityStatusRequest<br/>(Form Request)
    participant Policy as ActivityPolicy
    participant Action as UpdateActivityStatusAction
    participant AuditSvc as AuditService
    participant DB as Database

    Operator->>Livewire: Clicks "Done" + adds remark
    Livewire->>Request: validate(status, remark)
    Request->>Policy: authorize('updateStatus', $activity)
    Policy-->>Request: ✅ allowed (all auth users)
    Request-->>Livewire: ✅ validated

    Livewire->>Action: execute($activity, $date, $status, $remark, $user)
    Action->>DB: INSERT INTO activity_logs (append-only)
    DB-->>Action: ✅ row created

    Action->>AuditSvc: log($activityLog, 'status_changed', old, new)
    AuditSvc->>DB: INSERT INTO audit_logs (IP from Request::ip())
    DB-->>AuditSvc: ✅ audit entry created

    Action-->>Livewire: ✅ done
    Livewire-->>Operator: Re-render board (status updated)
```

---

## 4. Role-Based Access Control (RBAC) Map

Which roles can access which routes and perform which actions.

```mermaid
flowchart LR
    subgraph Roles
        A[👑 Admin]
        L[🎯 Lead]
        G[🔵 Agent]
    end

    subgraph Routes["Protected Routes"]
        R1["GET /daily\nDaily Board"]
        R2["GET /activities\nActivity List"]
        R3["GET /reports\nReports"]
        R4["GET /monitoring\nSRE Monitoring"]
        R5["admin/activities/*\nManage Activities"]
        R6["admin/users/*\nManage Users"]
        R7["PUT activity status\nUpdate Status"]
        R8["GET /settings\nProfile Settings"]
    end

    A -->|✅ Full Access| R1 & R2 & R3 & R4 & R5 & R6 & R7 & R8
    L -->|✅ Access| R1 & R2 & R3 & R4 & R5 & R7 & R8
    L -->|❌ Blocked| R6
    G -->|✅ Access| R1 & R2 & R7 & R8
    G -->|❌ Blocked| R3 & R4 & R5 & R6

    style A fill:#1B6B3A,color:#fff
    style L fill:#F5C518,color:#000
    style G fill:#6B7280,color:#fff
```

---

## 5. Data Flow — Report Generation

How a date-range report is built, rendered, printed and emailed.

```mermaid
flowchart TD
    User([User applies filters])
    RC[ReportController::index]
    RS[ReportingService::exportQuery]
    DB[(activity_logs + activities JOIN)]
    Check{print=true?}
    Page[Paginated view\n+ Chart.js visualisations]
    Print[Full collection view\nauto window.print]
    Modal{Email Report?}
    Mail[ActivityReportMail\nsent to selected recipients]
    CSV[StreamedResponse\ntext/csv download]

    User -->|GET /reports?from=&to=| RC
    RC --> RS
    RS --> DB
    DB --> Check
    Check -->|No| Page
    Check -->|Yes| Print
    Page -->|Export CSV| CSV
    Page -->|Email Report| Modal
    Modal -->|POST /reports/email| Mail
```

---

## 6. Module Dependency Graph

Shows which application modules depend on which other modules.

```mermaid
graph LR
    subgraph Controllers
        AC[ActivityController]
        RC[ReportController]
        MC[MonitoringController]
        SC[SettingsController]
        AU[Admin/UserController]
        AA[Admin/ActivityController]
    end

    subgraph Livewire
        DAB[DailyActivityBoard]
        ASU[ActivityStatusUpdater]
    end

    subgraph Actions
        CAA[CreateActivityAction]
        UAA[UpdateActivityAction]
        UASA[UpdateActivityStatusAction]
        DAA[DeleteActivityAction]
    end

    subgraph Services
        AS[AuditService]
        RS[ReportingService]
    end

    subgraph Models
        M_U[User]
        M_A[Activity]
        M_AL[ActivityLog]
        M_AU[AuditLog]
    end

    AC --> M_A & M_AL
    RC --> RS & M_U
    MC --> M_A & M_AL & M_AU & M_U
    SC --> AS & M_U
    AU --> M_U
    AA --> CAA & UAA & DAA

    DAB --> RS
    ASU --> UASA

    CAA --> M_A & AS
    UAA --> M_A & AS
    UASA --> M_AL & AS
    DAA --> M_A & AS

    RS --> M_AL & M_A
    AS --> M_AU
```

---

## 7. Deployment Architecture (Render.com)

```mermaid
flowchart TB
    GH[(GitHub\nmhiskall282/npontu-technologies-sre)]
    R[Render Web Service\nnpontu-support-tracker]
    BS[build.sh\ncomposer install\nnpm run build\nartisan cache]
    SS[Start Command\nartisan migrate --force\nheroku-php-apache2 public/]
    D[/var/data/database.sqlite\n1 GB Persistent Disk]
    APP[Laravel Application\nPort 10000]
    CDN[Chart.js CDN\njsdelivr.net]
    SMTP[SMTP Mail Provider\ne.g. Mailtrap / SendGrid]

    GH -->|git push triggers deploy| R
    R --> BS
    BS --> SS
    SS --> D
    SS --> APP
    APP --> CDN
    APP --> SMTP

    style GH fill:#24292e,color:#fff
    style R fill:#46E3B7,color:#000
    style D fill:#fef3c7,color:#000
```

---

## 8. Security Enforcement Chain

Every request passes through multiple security checkpoints.

```mermaid
flowchart LR
    Req([HTTP Request])
    CORS[HandleCors]
    CSRF[VerifyCsrfToken]
    Auth[Authenticate\nauth middleware]
    Role[EnsureRole\nrole:admin,lead]
    Policy[Laravel Policy\nauthorize call]
    Ctrl[Controller\nAction]
    Audit[AuditService\nwrite audit log]

    Req --> CORS --> CSRF --> Auth
    Auth -->|Not logged in| Redirect[/login redirect]
    Auth -->|Logged in| Role
    Role -->|Wrong role| 403[403 Forbidden]
    Role -->|Correct role| Policy
    Policy -->|Unauthorized| 403b[403 Forbidden]
    Policy -->|Authorized| Ctrl
    Ctrl --> Audit

    style Redirect fill:#E63946,color:#fff
    style 403 fill:#E63946,color:#fff
    style 403b fill:#E63946,color:#fff
    style Audit fill:#1B6B3A,color:#fff
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
| 8 | Database (production) | SQLite + Render disk | Managed MySQL | Zero additional cost on Render free tier; acceptable for small team |
