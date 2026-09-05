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
    users ||--o{ shift_handovers : "outgoing_lead"
    users ||--o{ shift_handovers : "incoming_lead"
    users ||--o{ shift_handovers : "accepted_by"
    users ||--o{ conversations : "creates"
    users ||--o{ conversation_participants : "participates"
    users ||--o{ messages : "authors"

    conversations ||--o{ conversation_participants : "enrolls"
    conversations ||--o{ messages : "contains"

    activities ||--o{ activity_logs : "has_many_logs"

    audit_logs }o--o| activities : "polymorphic_subject"
    audit_logs }o--o| users : "polymorphic_subject"
    audit_logs }o--o| shift_handovers : "polymorphic_subject"

    users {
        bigint id PK
        string name
        string email
        string password
        string role
        string grade "L1-L5 SRE grade"
        string department "Ops division"
        json privileges "Granular permissions array"
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
        string priority "low|medium|high|critical"
        string sla_time "nullable"
        boolean is_pinned
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
        string incident_ticket "nullable"
        boolean is_escalated
        bigint updated_by FK
        string actor_name
        string actor_role
        string actor_designation
        string actor_ip
        timestamp created_at
    }

    shift_handovers {
        bigint id PK
        date date
        string shift "morning|afternoon|night"
        bigint outgoing_lead_id FK
        bigint incoming_lead_id FK "nullable"
        text summary
        text incidents "nullable"
        integer pending_tasks_count
        integer completed_tasks_count
        timestamp signed_at
        timestamp accepted_at "nullable"
        bigint accepted_by_id FK "nullable"
        text acceptance_remarks "nullable"
        timestamp created_at
        timestamp updated_at
    }

    conversations {
        bigint id PK
        string type "direct|team|group"
        string title "nullable"
        string description "nullable"
        boolean is_private
        bigint created_by FK "nullable"
        timestamp created_at
        timestamp updated_at
    }

    conversation_participants {
        bigint id PK
        bigint conversation_id FK
        bigint user_id FK
        timestamp last_read_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    messages {
        bigint id PK
        bigint conversation_id FK
        bigint sender_id FK
        text body
        string attachment_name "nullable"
        string attachment_mime "nullable"
        bigint attachment_size "nullable"
        longtext attachment_blob "Base64 data URI"
        timestamp created_at
        timestamp updated_at
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

---

## 10. SRE Operational Continuity & Shift Handover Architecture

Formalizes shift transitions across 24/7 SRE teams (Morning, Afternoon, Night) with SLA tracking, incident ticket linkage, and supervisor batch delegation.

```mermaid
flowchart TD
    subgraph ShiftExecution["Active Shift Operations"]
        OPS["Operators on Shift"]
        CHECK["Check Completion / Status Toggle"]
        ESC["Incident Flagging (INC-xxxx)"]
        SLA["SLA Verification (Target GMT)"]
    end

    subgraph BatchDelegation["Supervisor Reassignment"]
        LEAD["Shift Lead / Admin"]
        MULTI["Multi-Check Selection (Selected: N)"]
        BULK["bulkAssign(targetUserId)"]
    end

    subgraph HandoverSignOff["Formal Shift Transfer Protocol (Two-Way Handshake)"]
        DRAFT["Draft Handover Briefing (Shift, Incoming Lead)"]
        STATS["Auto-Snapshot (Pending vs Done Tasks)"]
        INC_NOTES["Escalation / Discrepancy Log"]
        SIGN["Digital Sign-Off by Outgoing Lead (signed_at)"]
        ACCEPT["Formal Sign-On / Acceptance by Incoming Lead (accepted_at, remarks)"]
    end

    subgraph ShiftStore["System of Record & Observability"]
        SH[("shift_handovers Table (accepted_at, accepted_by_id)")]
        AL[("activity_logs (incident_ticket, is_escalated)")]
        AUD[("audit_logs Compliance Trail (handover_accepted)")]
        BOARD["Next Shift Dashboard (Handover Banner + Live Status Chip)"]
        COMMS[("conversations & messages Pipeline")]
    end

    OPS --> CHECK
    CHECK --> ESC
    CHECK --> SLA
    ESC --> AL

    LEAD --> MULTI
    MULTI --> BULK
    BULK --> AUD

    LEAD --> DRAFT
    DRAFT --> STATS
    DRAFT --> INC_NOTES
    DRAFT --> SIGN
    SIGN --> SH
    SIGN --> AUD
    SH --> BOARD

    BOARD --> ACCEPT
    ACCEPT --> SH
    ACCEPT --> AUD

    style ShiftExecution fill:#f0fdf4,stroke:#1B6B3A,stroke-width:2px
    style BatchDelegation fill:#eff6ff,stroke:#2563eb,stroke-width:2px
    style HandoverSignOff fill:#fef3c7,stroke:#d97706,stroke-width:2px
    style ShiftStore fill:#faf5ff,stroke:#9333ea,stroke-width:2px
```

---

## 8. SRE Operational Communications Architecture

```mermaid
flowchart LR
    subgraph Client["Browser Interface (Livewire 3)"]
        SIDEBAR["Channel Drawer (Team, War Rooms, DMs)"]
        CHAT["Message Stream (wire:poll.4000ms)"]
        MODAL["New Chat / Group Creator Modal"]
    end

    subgraph Server["Backend Pipeline & Access Control"]
        AUTH["Gate / Privilege Enforcement (create_channels)"]
        ROUTER["OperationalChat Livewire Component"]
        READ_TRACKER["Unread / Read Receipt Engine"]
    end

    subgraph Storage["Persistent Relational Layer"]
        CONV[("conversations (direct, team, group)")]
        PART[("conversation_participants (last_read_at)")]
        MSG[("messages (body, sender_id, created_at)")]
    end

    SIDEBAR -->|Select Channel| ROUTER
    CHAT -->|Send Message| ROUTER
    MODAL -->|Create Room / Direct| AUTH
    AUTH --> ROUTER

    ROUTER --> CONV
    ROUTER --> PART
    ROUTER --> MSG

    READ_TRACKER --> PART
    ROUTER --> READ_TRACKER
```

---

## 9. Real-Time SRE System Health & Telemetry Subsystem

```mermaid
flowchart TD
    subgraph Probes["Kernel & Service Telemetry Probes"]
        P_DB["Database Ping & Latency Probe (ms)"]
        P_CACHE["Cache & Key-Value Latency (ms)"]
        P_MEM["Runtime Memory & Peak Footprint (MB)"]
        P_MAIL["Outbound Notification Gateway Probe"]
        P_DISK["Storage & Mount Point Capacity"]
        P_SLA["24-Hour Heartbeat Timeline Engine"]
    end

    subgraph Service["app/Services/SystemHealthService.php"]
        ENGINE["Telemetry Aggregate Engine"]
        CACHE_STORE["5-Second Telemetry Cache"]
        HEALTH_EVAL["SLA & Subsystem Health Evaluator"]
    end

    subgraph Presentation["Delivery Channels"]
        JSON_API["JSON Endpoint (/health & /health/telemetry)"]
        HUD["Live SRE Telemetry HUD (3s Polling)"]
        MONITOR["SRE Monitoring Dashboard (/monitoring)"]
        TOPBAR["Topbar Systems Online Badge"]
    end

    Probes --> ENGINE
    ENGINE --> CACHE_STORE
    CACHE_STORE --> HEALTH_EVAL
    HEALTH_EVAL --> JSON_API
    HEALTH_EVAL --> HUD
    HEALTH_EVAL --> MONITOR
    HEALTH_EVAL --> TOPBAR

    style Probes fill:#f8fafc,stroke:#64748b,stroke-width:1px
    style Service fill:#f0fdf4,stroke:#1B6B3A,stroke-width:2px
    style Presentation fill:#eff6ff,stroke:#2563eb,stroke-width:2px
```

---

## 10. Left Sidebar Global Navigation & UI Layout Architecture

```mermaid
flowchart LR
    subgraph Shell["Global App Shell (resources/views/layouts/app.blade.php)"]
        direction TB
        BRAND["Npontu Brand Header (Gold Triangle Motif)"]
        STATUS["SRE Cockpit Banner (Live Radar Pulse)"]
        NAV["Grouped Sidebar Nav (resources/views/layouts/sidebar-nav.blade.php)"]
        PROFILE["User Profile Card (Initials Avatar, Grade, Settings, Sign Out)"]
    end

    subgraph Content["Right-Side Main Stage"]
        direction TB
        CONTEXT["Context Bar (Back, Breadcrumbs, UTC Clock, Systems Online Badge)"]
        ALERTS["Flash Messages (x-alert)"]
        STAGE["Main Viewport (Blade @yield('content') + Livewire $slot)"]
        FOOTER["SRE SLA Footer (no-print)"]
    end

    Shell --- Content

    style Shell fill:#0F1A14,stroke:#1A2E22,stroke-width:2px,color:#ffffff
    style Content fill:#F4F7F5,stroke:#cbd5e1,stroke-width:2px
```

---

## 11. SRE Branded Error Handling & Session Expiry Lifecycle

```mermaid
flowchart TD
    subgraph Client["Client Browser / SRE Terminal"]
        ACTION["User Action (Livewire poll, form submit, link navigation)"]
    end

    subgraph Middleware["Laravel Security & Middleware Guard"]
        CSRF["VerifyCsrfToken / Session Lifespan (120 min)"]
        AUTH["Authenticate / Authorize Middleware"]
        ROUTING["Route Matching Engine"]
    end

    subgraph ErrorHandling["Branded SRE Error Architecture"]
        EXP_CHECK{"Session or CSRF Valid?"}
        AUTH_CHECK{"Privileged Capability Met?"}
        ROUTE_CHECK{"Endpoint Registered?"}
        
        LW_HOOK["Livewire.hook('request.fail') Interceptor (app.blade.php)"]
        EXP_REDIRECT["Clean Client Redirect: /login?expired=1"]
        LOGIN_ALERT["Redesigned Operator Sign-In (Banner: Session Expired + Quick Credentials)"]

        VIEW_419["resources/views/errors/419.blade.php (SESSION TIMEOUT + Re-Auth CTA)"]
        VIEW_403["resources/views/errors/403.blade.php (ACCESS FORBIDDEN + Admin Contact)"]
        VIEW_404["resources/views/errors/404.blade.php (RESOURCE NOT FOUND + Today's Board CTA)"]
        VIEW_500["resources/views/errors/500.blade.php (RUNTIME EXCEPTION + Incident Code)"]
        VIEW_503["resources/views/errors/503.blade.php (MAINTENANCE WINDOW + Status Check)"]
    end

    ACTION --> EXP_CHECK
    EXP_CHECK -- "No (CSRF 419 via Livewire)" --> LW_HOOK
    LW_HOOK --> EXP_REDIRECT
    EXP_REDIRECT --> LOGIN_ALERT

    EXP_CHECK -- "No (Standard HTTP 419)" --> VIEW_419
    EXP_CHECK -- "Yes" --> AUTH_CHECK
    
    AUTH_CHECK -- "Forbidden (403)" --> VIEW_403
    AUTH_CHECK -- "Authorized" --> ROUTE_CHECK
    
    ROUTE_CHECK -- "Not Found (404)" --> VIEW_404
    ROUTE_CHECK -- "Internal Crash (500)" --> VIEW_500

    style Client fill:#0F1A14,stroke:#1A2E22,stroke-width:2px,color:#ffffff
    style Middleware fill:#f8fafc,stroke:#64748b,stroke-width:1px
    style ErrorHandling fill:#f0fdf4,stroke:#1B6B3A,stroke-width:2px
```

---

## 12. Public SRE Landing Page & Route Gateway Architecture

```mermaid
flowchart TD
    subgraph Inbound["Inbound Traffic (Visitor, Leadership, On-Duty Engineer)"]
        REQ_ROOT["GET / (Root URL)"]
        REQ_DASH["GET /dashboard"]
        REQ_DAILY["GET /daily"]
    end

    subgraph Router["Routing Layer (routes/web.php)"]
        GATE_ROOT{"Authenticated?"}
        AUTH_GUARD{"Auth Middleware Guard"}
    end

    subgraph Destinations["Target Presentation Interfaces"]
        LANDING["Public Landing Page (LandingController@index)\n• Brand Hero & Live UTC Clock\n• 6 Capability Pillars\n• 4-Step Handover Lifecycle\n• 8 Subsystems Telemetry\n• Pre-seeded Test Accounts Showcase"]
        COCKPIT_REDIRECT["Redirect: /daily (DashboardController@index)"]
        LOGIN["Operator Sign-In (/login)\n• SRE Brand Layout\n• 1-Click Test Credentials Helper"]
        SHIFT_BOARD["SRE Shift Cockpit (/daily)\n• Livewire 3 Shift Board\n• Left Dark Sidebar Navigation"]
    end

    REQ_ROOT --> GATE_ROOT
    GATE_ROOT -- "Guest (Unauthenticated)" --> LANDING
    GATE_ROOT -- "Authenticated" --> LANDING
    LANDING -. "Click: Launch Shift Console" .-> LOGIN
    LANDING -. "Click: Enter SRE Cockpit" .-> SHIFT_BOARD

    REQ_DASH --> AUTH_GUARD
    REQ_DAILY --> AUTH_GUARD

    AUTH_GUARD -- "Unauthenticated" --> LOGIN
    AUTH_GUARD -- "Authenticated (/dashboard)" --> COCKPIT_REDIRECT
    COCKPIT_REDIRECT --> SHIFT_BOARD
    AUTH_GUARD -- "Authenticated (/daily)" --> SHIFT_BOARD

    style Inbound fill:#0F1A14,stroke:#1A2E22,stroke-width:2px,color:#ffffff
    style Router fill:#f8fafc,stroke:#64748b,stroke-width:1px
    style Destinations fill:#f0fdf4,stroke:#1B6B3A,stroke-width:2px
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
| 10 | Incident Escalation | Denormalized on `activity_logs` | External Ticketing Webhook Only | Connects shift checkoff discrepancies directly to incident ticket references (e.g., `INC-1042`) without blocking offline operations |
| 11 | Digital Shift Handover | Dedicated `shift_handovers` table | Unstructured remarks or chat apps | Enforces formal briefing sign-off between outgoing and incoming shift leads with statistical non-repudiation |
| 12 | Two-Way Handover Handshake | Sign-off + Sign-on Acceptance | Outgoing sign-off only | Eliminates ambiguity in operational custody: incoming lead explicitly acknowledges blockers, verifies systems, and assumes shift duty |
| 13 | Operational Messaging Pipeline | First-party Relational Comms + Livewire polling | Third-party Slack/Discord webhook dependency | Self-contained, zero-cost, compliant within SRE security boundary; supports 1-on-1 direct chat, team shift channels, and private incident war rooms |
| 14 | SRE User Grades & Granular Privileges | 5-tier Grades (L1-L5) + Checkbox Privileges JSON | Rigid single-role inheritance | Allows fine-grained operational permissions (e.g. task reassignment, channel creation) across varying engineer seniority without bloating full admin access |
| 15 | Multi-Service Telemetry Probes | `SystemHealthService` with 8 probes + HUD | Third-party APM SaaS agent (Datadog/NewRelic) | Provides native zero-overhead SRE diagnostics (DB ping, cache latency, queue health, uptime SLA) with public JSON probe endpoint |
| 16 | Left Sidebar Navigation Architecture | Sticky Left Dark Cockpit + Responsive Drawer | Crowded 64px Horizontal Navbar | Reclaims vertical breathing room, cleanly groups operational domains, isolates background polling via `wire:target`, and supports both Blade views and Livewire 3 slots |
| 17 | Branded SRE Error Pages & 419 Interceptor | Custom SRE Error Views + Livewire 419 Redirect Hook | Default stark Laravel/Symfony error pages and raw modal popups | Prevents jarring user disconnect during session expiration; redirects operators seamlessly to `/login?expired=1` with informative banner and 1-click credential recovery |
| 18 | Public SRE Landing Page at Root (`/`) | High-Impact SRE Landing View with Test Roles Showcase | Immediate blank redirect from `/` to `/login` | Educates external evaluators, management, and new operators on platform capabilities and architecture before authentication |



