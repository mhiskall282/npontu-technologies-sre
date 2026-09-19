# Opsora SaaS Control Plane & Multi-Tenant Platform Architecture

> **Document Class**: Enterprise Architecture Specification  
> **System**: Opsora Site Reliability Engineering SaaS Platform  
> **Classification**: Internal Platform Engineering Document  
> **Status**: Approved & Implemented

---

## 1. Architectural Foundations & Domain Separation

The Opsora SRE platform enforces a strict architectural partition between the **Platform Control Plane** and the **Tenant Operations Plane**.

```mermaid
flowchart TB
    subgraph Users["Global User Base"]
        SuperAdmin["Super Admin (Root)"]
        SecAdmin["Security Admin"]
        BillAdmin["Billing Admin"]
        OpsEngineers["Tenant SRE Engineers"]
    end

    subgraph Boundaries["Routing & Ingress Layer"]
        ControlPlaneRoute["/admin/platform/*<br/>EnsurePlatformAdmin"]
        TenantRoute["/workspaces/*, /activities/*<br/>ResolveTenantContext"]
        APIRoute["/api/v1/*<br/>Sanctum + TenantContext"]
    end

    subgraph ControlPlaneDomain["Platform Control Plane (Tenant-Agnostic)"]
        Dashboard["Administrative Cockpit"]
        OrgManager["Tenant Organization Directory"]
        UserManager["Platform User Directory & Roles"]
        SubManager["Commercial Billing & Subscriptions"]
        FlagManager["Feature Flag Targeting & Entitlements"]
        HealthDiagnostics["System Probes (DB, Cache, Queue)"]
        SIEMTelemetry["Security Center & SIEM Events"]
        AuditLedger["Immutable Audit Trail"]
    end

    subgraph TenantDomain["Tenant Operations Plane (Tenant-Scoped)"]
        TenantScope["TenantContext Resolution"]
        WSMemberships["Workspace Memberships"]
        ActivitiesEngine["Activity Checklists & Statuses"]
        HandoverEngine["Shift Handovers (Dual Sign-Off)"]
        CommsEngine["Ops Comms & Emergency Feeds"]
    end

    subgraph StorageLayer["Data Storage Layer (MySQL 8.0)"]
        GlobalTables[("saas_plans<br/>saas_subscriptions<br/>feature_flags<br/>security_events<br/>organizations")]
        TenantTables[("workspaces<br/>workspace_memberships<br/>activities<br/>activity_logs<br/>shift_handovers")]
    end

    SuperAdmin --> ControlPlaneRoute
    SecAdmin --> ControlPlaneRoute
    BillAdmin --> ControlPlaneRoute
    OpsEngineers --> TenantRoute
    OpsEngineers --> APIRoute

    ControlPlaneRoute --> ControlPlaneDomain
    TenantRoute --> TenantDomain
    APIRoute --> TenantDomain

    ControlPlaneDomain --> GlobalTables
    ControlPlaneDomain -. Cross-Tenant Oversight .-> TenantTables
    TenantDomain --> TenantScope
    TenantScope --> TenantTables
```

---

## 2. Multi-Tenant Request Resolution & Isolation Model

### 2.1 Resolution Flow & Context Binding
Every request entering the application traverses the `ResolveTenantContext` middleware.

```mermaid
sequenceDiagram
    autonumber
    actor Client as Web / Mobile Client
    participant Proxy as Reverse Proxy / Ingress
    participant Middleware as ResolveTenantContext
    participant TenantContext as TenantContext Service
    participant Model as Eloquent Model (e.g. Activity)
    participant Database as Database Engine

    Client->>Proxy: HTTP GET /activities (Header: X-Workspace-UUID or Session)
    Proxy->>Middleware: Pass Request
    
    alt Route is in Platform Control Plane (/admin/platform/*)
        Middleware->>TenantContext: withoutTenancy(callback)
        TenantContext-->>Middleware: Global Scope Disabled
        Middleware->>Model: Direct Query across all organizations
        Model->>Database: SELECT * FROM organizations...
        Database-->>Client: Return Global Administrative Data
    else Route is Tenant-Scoped
        Middleware->>TenantContext: setWorkspace(resolvedWorkspace)
        TenantContext->>TenantContext: Bind current Organization ID & Workspace ID
        Middleware->>Model: Query executed
        Model->>Database: SELECT * FROM activities WHERE workspace_id = ? AND deleted_at IS NULL
        Database-->>Client: Return Isolated Tenant Data
    end
```

### 2.2 Row-Level Security & Global Scope Enforcement
Tenant data isolation is enforced through Laravel Eloquent Global Scopes:
- Any model extending tenant awareness automatically attaches `where('workspace_id', TenantContext::getWorkspaceId())`.
- In the Control Plane, all queries run inside `TenantContext::withoutTenancy(fn () => ...)`, guaranteeing that administrative metrics reflect real system-wide totals without leaking into tenant user sessions.

---

## 3. Commercial Subscriptions, Tiers & Feature Flags

### 3.1 Subscription State Machine & Entitlements
Commercial accounts are linked through `saas_subscriptions` referencing `saas_plans`.

```mermaid
stateDiagram-v2
    [*] --> Trialing: Self-Service Sign-up (14-day trial)
    Trialing --> Active: First Commercial Payment
    Trialing --> Canceled: Trial Expired without Payment
    Active --> PastDue: Payment Gateway Failure
    PastDue --> Active: Successful Dunning Retry
    PastDue --> Canceled: Final Dunning Expiry (30 days)
    Active --> Paused: Administrative Action
    Paused --> Active: Reactivated by Super Admin
    Canceled --> [*]
```

### 3.2 Dynamic Feature Flag Targeting Pipeline

```mermaid
flowchart LR
    Request["Feature Flag Check:<br/>FeatureFlag::isEnabled('live_telemetry_probes', $user, $org)"] --> GlobalCheck{"Flag Globally<br/>Enabled?"}
    
    GlobalCheck -- No --> Denied["Feature Disabled"]
    GlobalCheck -- Yes --> OrgCheck{"Targeting Rule<br/>Specified?"}
    
    OrgCheck -- No (All) --> Allowed["Feature Available"]
    OrgCheck -- Yes --> MatchCheck{"Org Tier Matches<br/>or Org ID Listed?"}
    
    MatchCheck -- Yes --> Allowed
    MatchCheck -- No --> Denied
```

---

## 4. SIEM Security Telemetry & Immutable Audit Trail Architecture

Security telemetry is captured synchronously on all operational and platform-level mutations via `SecurityEvent::record(...)` and `AuditService::log(...)`. Every mutation captures authenticated actor snapshot details, state diff payloads, and verifiable client IP address provenance.

```mermaid
flowchart TD
    subgraph TriggerPoints["Operational & Platform Mutation Triggers"]
        LoginEvent["User Login / Token Issuance"]
        SuspensionEvent["Account / Org Suspension"]
        RoleChangeEvent["Platform Role Modification"]
        PrivilegeUpdate["Granular Privileges Modification"]
        FlagEvent["Feature Flag Toggle"]
        IncidentEscalation["SRE Incident Flagged"]
    end

    subgraph ProvenanceEngine["Client IP Provenance & Telemetry Engine"]
        IPExtractor["Request::ip() ?? '127.0.0.1'<br/>(X-Forwarded-For Provenance)"]
        SIEMRecorder["SecurityEvent::record()"]
        AuditRecorder["AuditLog::create()<br/>(actor_ip + ip_address Accessor)"]
    end

    subgraph PersistentLogs["Security & Compliance Data Stores"]
        SecurityTable[("security_events<br/>severity, ip_address, details")]
        AuditTable[("audit_logs<br/>actor_ip, old_values, new_values")]
        ControlPlaneAuditView["/admin/platform/audit<br/>(Monospace IP Badge & Diff Modals)"]
        AuditAPI["GET /api/v1/audit-logs<br/>(SIEM Integration Relay)"]
    end

    TriggerPoints --> IPExtractor
    IPExtractor --> SIEMRecorder
    IPExtractor --> AuditRecorder

    SIEMRecorder --> SecurityTable
    AuditRecorder --> AuditTable
    AuditTable --> ControlPlaneAuditView
    AuditTable --> AuditAPI
```

---

## 5. Granular User Privileges & Fleet RBAC Governance

Opsora decouples tenant user roles (`admin`, `lead`, `agent`) from platform administrative roles (`PlatformRole`), enabling fine-grained operational authorizations across 18 specialized enterprise capabilities.

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Super Admin / Organization Lead
    participant API as Privilege Endpoints (/api/v1/privileges)
    participant Ctrl as PrivilegeController / PlatformUserController
    participant Gate as Authorization Gate (managePrivileges)
    participant Model as User Model (ALL_PRIVILEGES)
    participant Audit as AuditLog & SecurityEvent

    Admin->>API: PUT /api/v1/users/{id}/privileges {"privileges": [...]}
    API->>Ctrl: Route Request
    Ctrl->>Gate: Verify Authorization (Platform Admin or Workspace Admin)
    alt Unauthorized Caller
        Gate-->>Admin: 403 Forbidden
    else Authorized Operator
        Ctrl->>Model: Validate against User::ALL_PRIVILEGES (18 items)
        Ctrl->>Model: Update user->privileges JSON array
        Ctrl->>Audit: AuditLog::create(event: 'user_privileges_updated', ip: Request::ip())
        Ctrl->>Audit: SecurityEvent::record(event: 'privileges_updated')
        Ctrl-->>Admin: 200 OK (Serialized UserResource)
    end
```

---

## 6. Mobile Hybrid SRE Ingress & WebSocket/Polling Architecture

The Flutter mobile application connects via REST and Livewire-compatible polling endpoints.

```mermaid
flowchart TB
    subgraph MobileClient["Flutter SRE Mobile App"]
        MobileUI["Mobile Cockpit / Dashboard"]
        ActiveTenantBar["SaaS Tenant & Active Workspace Card"]
        WorkspaceSwitcher["WorkspaceSwitcherSheet (Join Code / Switch)"]
        BackgroundSync["Operational Notification Service"]
    end

    subgraph APIEndpoints["Backend API Endpoints (/api/v1)"]
        AuthMe["GET /api/v1/me (User + Org + Plan + Privileges)"]
        PrivilegesCatalog["GET /api/v1/privileges (18 Capabilities)"]
        Workspaces["GET /api/v1/workspaces (Tenant Workspaces)"]
        SwitchWS["POST /api/v1/workspaces/switch"]
        JoinOrg["POST /api/v1/organizations/join-by-code"]
        DailyActivities["GET /api/v1/activities"]
        ShiftHandovers["GET /api/v1/handovers"]
    end

    MobileUI --> AuthMe
    MobileUI --> PrivilegesCatalog
    ActiveTenantBar --> WorkspaceSwitcher
    WorkspaceSwitcher --> Workspaces
    WorkspaceSwitcher --> SwitchWS
    WorkspaceSwitcher --> JoinOrg
    MobileUI --> DailyActivities
    MobileUI --> ShiftHandovers
    BackgroundSync --> AuthMe
```

---

## 7. Administrative Operations & Platform Control Cockpit

Enterprise SaaS platforms require centralized governance capabilities to maintain service availability, diagnose tenant incidents, broadcast emergency alerts, and execute infrastructure maintenance without manual SSH access.

### 7.1 Tenant Support Impersonation Protocol ("Login as Tenant")
When diagnosing customer issues, Platform Administrators can securely assume the operational context of a tenant user or organization without requesting passwords or API keys.

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Super Admin
    participant CP as Control Plane (/admin/platform)
    participant ImpCtrl as PlatformImpersonationController
    participant Session as Encrypted Server Session
    participant Audit as Immutable AuditLog Ledger
    participant TenantApp as Tenant Operations Plane (/daily)

    Admin->>CP: Click "Impersonate Tenant / User" (with support reason)
    CP->>ImpCtrl: POST /admin/platform/impersonate/{user|organization}
    ImpCtrl->>ImpCtrl: Verify Admin holds platform_role != null
    ImpCtrl->>Audit: AuditLog::create(event: 'tenant_impersonation_started', actor_ip: Request::ip())
    ImpCtrl->>Session: Store opsora_impersonator_id & opsora_impersonator_name
    ImpCtrl->>Session: Auth::login(targetTenantUser)
    ImpCtrl-->>TenantApp: Redirect to /daily with Impersonation Banner
    Note over TenantApp: Sticky Amber Banner displayed: "Support Impersonation Mode Active"
    
    Admin->>TenantApp: Diagnose workspace configuration / view issues
    
    Admin->>TenantApp: Click "Exit Support Session"
    TenantApp->>ImpCtrl: POST /admin/platform/impersonate/exit
    ImpCtrl->>Audit: AuditLog::create(event: 'tenant_impersonation_ended', actor_ip: Request::ip())
    ImpCtrl->>Session: Retrieve original admin ID & Auth::login(admin)
    ImpCtrl->>Session: Forget opsora_impersonator_* keys
    ImpCtrl-->>CP: Redirect to /admin/platform/organizations (Context Restored)
```

### 7.2 System Maintenance Mode & Emergency Lockout Architecture
The platform features an integrated maintenance mode that isolates tenant traffic during migrations while preserving unrestricted access for platform engineers.

```mermaid
flowchart TD
    IngressReq["Incoming HTTP / API Request"] --> MaintCheck{"EnsurePlatformNotUnderMaintenance<br/>is maintenance_mode true?"}
    
    MaintCheck -- "No (Normal Operations)" --> NextPipeline["Pass to ResolveTenantContext -> Application"]
    MaintCheck -- "Yes (Maintenance Active)" --> ExemptionCheck{"Request Exempt?<br/>- Route /admin/platform/*<br/>- Super Admin authenticated<br/>- Health probe (/up, /health)<br/>- Valid ?bypass_key=SECRET"}
    
    ExemptionCheck -- "Yes (Authorized Engineer / Bypass)" --> NextPipeline
    ExemptionCheck -- "No (Tenant Traffic Blocked)" --> FormatCheck{"Request Format?"}
    
    FormatCheck -- "API / Mobile / JSON" --> ReturnJSON["503 Service Unavailable<br/>JSON payload with custom message & ETA"]
    FormatCheck -- "Browser Web Navigation" --> ReturnHTML["503 Custom Npontu Maintenance Page<br/>Live status, restoration ETA, support contact"]
```

### 7.3 Operational Announcements & Broadcast Distribution
Platform engineers can schedule and broadcast alerts across all tenant surfaces (Web and Mobile).

```mermaid
flowchart LR
    AdminInput["Platform Admin<br/>/admin/platform/announcements"] --> CreateNotice["Create Broadcast<br/>Title, Body, Urgency (Info/Warning/Critical), Schedule"]
    CreateNotice --> AuditDB[("platform_announcements<br/>+ Immutable AuditLog")]
    
    AuditDB --> WebBanner["Web Top Broadcast Banner<br/>(Live in layouts/app.blade.php)"]
    AuditDB --> MobileAPI["REST API Endpoint<br/>GET /api/v1/announcements/active"]
    MobileAPI --> MobileBanner["Flutter SRE Mobile App<br/>Active Alert Component"]
```

### 7.4 Platform Operations Runner (System Maintenance Utilities)
The Platform Health Console (`/admin/platform/health`) provides immediate operational remediation actions:
- **Cache Purge**: Flushes application cache, compiled Blade templates, and route lookup tables.
- **Sanctum Token Pruning**: Permanently deletes expired and revoked personal access tokens.
- **Queue Worker Recovery**: Re-dispatches failed queue jobs into active worker pipelines.
- **Automated SRE Reports Dispatch**: Manually triggers batch report compilation and distribution.
- **Deep Node Latency Probes**: Executes synchronous latency benchmarks against the Database, Cache store, and Storage mounts.

---

## 8. Architecture Verification & Quality Gates

The implementation conforms to the following strict operational metrics:
- **Zero Foreign Key Violations**: Cascading soft-deletes implemented across all tenant and platform models.
- **Strict Database Portability**: All SQL aggregation functions dynamically check the active driver (`sqlite`, `mysql`, `pgsql`).
- **Cryptographic Session Tokens**: Sanctum personal access tokens are hashed using SHA-256 and revocable from the Platform User Directory.
- **Contrast & Accessibility Compliance**: High-contrast typography palette strictly adheres to WCAG AA standards in both light and dark display modes.

