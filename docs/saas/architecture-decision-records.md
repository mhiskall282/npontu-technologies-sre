# Opsora SaaS Transformation — Architecture Decision Records (ADRs)

> **Status**: Approved Architectural Standards  
> **Target System**: Opsora Modular Multi-Tenant SRE Platform

---

## ADR-001: Modular Monolith vs. Premature Microservices

### Context
Opsora is evolving from a high-performance single-tenant SRE cockpit into a multi-tenant SaaS platform serving diverse customer profiles (freelancers, startups, enterprise teams, MSPs).

### Decision
We will build Opsora as a **disciplined Modular Monolith** inside Laravel 11 rather than splitting into microservices.
- Platform Control Plane (Identity, Organization Registry, Billing, Entitlements, Admin Review) and Operational Execution Planes (Daily Checklists, Incident Handovers, Chat War Rooms) reside in distinct namespaces within the monolithic application.
- Communication between modules happens through strict Action and Service interfaces with typed DTOs.

### Rationale
- Microservices introduce immense operational overhead (distributed transactions, network latency, event schema drift) before reaching scale.
- A modular monolith allows atomic database transactions across tenant provisioning, audit logging, and role assignment.
- Can be decomposed into independent services in future stages if specific execution planes demand dedicated compute scaling.

---

## ADR-002: Multi-Tenancy Isolation Model (Shared Database with Tenant Scope & Dedicated Managed Pods)

### Context
Customers require varying levels of isolation, ranging from free/standard SaaS tiers (cost-effective shared infrastructure) to regulated financial institutions requiring dedicated deployments and regional data residency.

### Decision
We adopt a **hybrid multi-tenant deployment architecture**:
1. **Shared Multi-Tenant Plane**: Single MySQL database with explicit `workspace_id` and `organization_id` foreign keys, enforced by global `TenantScope` and strict policy middleware.
2. **Dedicated Managed Plane**: High-tier enterprise customers can be mapped to dedicated database connections or isolated containerized pods tracked in the centralized `DeploymentRegistry`.

### Rationale
- Shared database allows instant self-service onboarding for personal workspaces and small teams.
- Global `TenantScope` combined with Policy gates provides cryptographically and logically verified cross-tenant isolation.
- Architecture supports dedicated enterprise pods without rewriting the core domain logic.

---

## ADR-003: Separation of User, Organization, and Workspace

### Context
In single-tenant SRE cockpit, a User was tied to a single team with a global role. In a SaaS platform, a user may own a personal workspace, belong to multiple organizations, and hold different roles in each.

### Decision
We explicitly separate:
1. **User (Identity)**: A global human persona with credentials, MFA, and profile attributes.
2. **Organization (Legal/Commercial Entity)**: A company, enterprise, or team that holds subscriptions, entitlements, and policies.
3. **Workspace (Operational Execution Environment)**: A concrete operational space (e.g., "Production Core", "Staging Infrastructure") where SRE activities, checklists, handovers, and war rooms take place.
4. **OrganizationMembership & WorkspaceMembership**: Scoped roles (`owner`, `admin`, `lead`, `engineer`, `viewer`) and granular privileges per tenant.

---

## ADR-004: Workspace Routing & Context Resolution Strategy

### Context
Users and mobile clients need flexible, intuitive ways to access workspaces (subdomains, paths, company codes, and QR codes).

### Decision
We implement a multi-strategy routing resolver:
1. **Subdomain Resolution**: `https://{workspace}.opsora.app` maps directly to the designated workspace.
2. **Path Resolution**: `https://opsora.app/w/{workspace-slug}` for shared environments.
3. **Company Code & QR Onboarding**: Short 6-character cryptographic tokens (e.g. `OPS-792`) used by mobile clients to discover tenant endpoint without manual URL typing.
4. **Context Injection**: Incoming requests pass through `ResolveTenantContext` middleware, which binds the active `Workspace` and `Organization` to Laravel's service container (`app(TenantContext::class)`).

---

## ADR-005: Decoupled Billing & Entitlement Engine

### Context
Commercial models must support free tiers, subscriptions, enterprise contracts, and one-time license keys without locking into a single payment gateway prematurely.

### Decision
We separate **Entitlement Enforcement** from **Payment Processing**:
- Entitlements (e.g. `max_active_operators`, `custom_retention_days`, `audit_export_enabled`, `sms_gateway_checks`) are calculated from active `SubscriptionPlan` or `LicenseKey`.
- Domain actions query `EntitlementService::can($workspace, 'feature')` rather than checking raw Stripe/Paystack/Paddle objects.
- Payment gateways are abstracted behind a `PaymentProviderInterface`. Real charging remains inactive until final commercial launch approval.
