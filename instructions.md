# Opsora SaaS Transformation â€” Master Antigravity Implementation Prompt

## 0. Project Identity


---

## 1. Your Role

Act as a principal SaaS architect, senior Laravel engineer, Flutter engineer, cloud infrastructure engineer, DevSecOps engineer, database architect, product engineer, QA engineer, and technical writer.

You are working on the existing repository:

- Repository: `https://github.com/mhiskall282/npontu-technologies-sre`
- Existing backend: Laravel with Blade and Livewire
- Existing mobile application: Flutter for Android and iOS
- Existing product: SRE and technical operations workflow platform

Your task is to evolve the existing application into a scalable, multi-tenant SaaS platform branded as **Opsora**, without unnecessarily rewriting or damaging the existing application.

---

# 2. Product Vision

Opsora will be a flexible technical operations platform for:

- Small and medium-sized businesses
- Startups
- Software development teams
- Large enterprises
- Managed service providers
- Fintech and financial institutions
- Universities and other institutions
- Individual developers and freelancers
- Internal IT and operations teams

Opsora should support:

1. Personal workspaces
2. Multiple organization workspaces
3. Organization registration and approval
4. Shared SaaS deployments
5. Dedicated managed deployments
6. Customer-funded managed deployments
7. Customer-hosted deployments
8. Subdomain-based workspaces
9. Path-based workspaces
10. Custom domains
11. Subscription and entitlement architecture
12. One-time license keys
13. Monthly and annual billing models
14. Free tiers and trials
15. Enterprise contracts
16. Data-residency requirements
17. Delegated platform administration
18. Multi-workspace mobile access
19. Company-code and QR-code onboarding
20. Optional enterprise white-label mobile applications
21. Future monitoring and observability capabilities

The first product release should prioritize **workflow and operations management**. Deeper monitoring and observability should be architecturally supported but implemented later in controlled stages.

---

# 3. Non-Negotiable Principles

Follow these principles throughout the implementation:

- Audit the existing repository before making major changes.
- Do not create a generic demo or throwaway prototype.
- Do not destroy existing data.
- Do not rewrite working features without a documented reason.
- Preserve current functionality.
- Preserve the current visual design and color scheme.
- Use a modular monolith initially.
- Avoid premature microservices.
- Separate platform-level control-plane responsibilities from customer workspace responsibilities.
- Enforce authorization on the backend.
- Never rely on frontend checks for tenant isolation.
- Make tenant ownership explicit in the data model.
- Use feature flags for risky or incomplete functionality.
- Use reversible migrations whenever practical.
- Never place secrets in source code, logs, screenshots, or test fixtures.
- Add cross-tenant isolation tests.
- Do not claim compliance certifications that have not been verified.
- Do not claim a stage is complete without tests and evidence.
- Keep the existing Npontu environment separate during migration.
- Keep architecture extensible without overengineering the first release.

---

# 4. Initial Brand-Only Migration

## Objective

Change the customer-facing brand from **Npontu** to **Opsora** without changing the productâ€™s visual identity.

## Required Work

Audit the repository for all references to:

- Npontu
- Npontu Technologies
- Npontu Technologies SRE
- Npontu logos
- Npontu email addresses
- Npontu URLs
- Npontu metadata
- Npontu application names
- Npontu page titles
- Npontu notification text
- Npontu mobile application labels
- Npontu environment variables
- Npontu documentation

Classify each reference as:

1. Customer-facing and safe to rename
2. Internal legacy reference that must remain temporarily
3. Migration-sensitive reference
4. Deployment or infrastructure reference
5. Historical data that must not be modified automatically

Create:

`docs/saas/brand-migration-audit.md`

The audit must include:

- File path
- Existing reference
- Reference category
- Proposed action
- Risk level
- Whether the change is reversible

## Brand Rules

Use:

- Product name: `Opsora`
- Legacy name: `Npontu`
- Legacy environment: retained separately during transition

Do not modify historical audit records, customer data, or legal records merely to change the displayed brand.

Create a configurable brand layer where practical, such as:

- Application name configuration
- Email sender display name
- Page titles
- Mobile application display name
- Public metadata
- Documentation labels
- Environment-specific branding

Do not change colors or layouts.

## Acceptance Criteria

- Customer-facing product name displays as Opsora.
- Existing colors remain unchanged.
- Existing layouts remain unchanged.
- Existing Laravel and Flutter workflows continue working.
- Legacy Npontu environment remains separately identifiable.
- Tests cover the brand configuration.
- A rollback plan exists.

---

# 5. Stage 0 â€” Safety Baseline and Repository Audit

Before implementing multi-tenancy, complete the following:

## Repository Audit

Inspect:

- README files
- `composer.json`
- `package.json`
- `.env.example`
- Routes
- Models
- Migrations
- Factories
- Seeders
- Policies
- Gates
- Middleware
- Controllers
- Form Requests
- API Resources
- Actions
- Services
- Jobs
- Events
- Notifications
- Livewire components
- Blade views
- Flutter source code
- Tests
- CI/CD workflows
- Deployment files
- Queue configuration
- Cache configuration
- Storage configuration
- Authentication implementation
- Authorization implementation

Identify:

- Single-company assumptions
- Hard-coded company names
- Global queries that may become tenant-sensitive
- Existing user-role assumptions
- Existing SRE data ownership
- Existing API endpoints
- Existing mobile authentication flow
- Existing database relationships
- Existing real-time features
- Existing notification system
- Existing reporting and export functionality

Create:

- `docs/saas/current-state-audit.md`
- `docs/saas/risk-register.md`
- `docs/saas/architecture-decision-records.md`
- `docs/saas/brand-migration-audit.md`

## Safety Requirements

Before risky changes:

- Create a dedicated feature branch.
- Back up the database.
- Record the current application version.
- Establish a rollback procedure.
- Add or update smoke tests.
- Verify local and staging environments.
- Document migration order.

Do not begin large-scale implementation until the audit is complete.

---

# 6. Stage 1 â€” Target Architecture and Domain Design

Design Opsora as a modular monolith with clear boundaries.

## High-Level Architecture

Clients:

- Laravel Blade and Livewire web application
- Flutter Android application
- Flutter iOS application
- Future enterprise-branded mobile applications

Platform control plane:

- Identity and account management
- Organization registry
- Workspace registry
- Domain and routing registry
- Deployment registry
- Subscription and entitlement records
- License management
- Provisioning records
- Region registry
- Platform administration
- Security and audit management

Execution planes:

- Shared SaaS environments
- Dedicated managed environments
- Customer-funded managed environments
- Customer-hosted environments
- Region-specific environments where required

The control plane must retain essential deployment and customer metadata even when a dedicated environment is temporarily unavailable.

Create:

- `docs/saas/domain-model.md`
- `docs/saas/resource-ownership-matrix.md`
- `docs/saas/tenant-boundary-map.md`
- `docs/saas/deployment-architecture.md`
- `docs/saas/data-residency-design.md`
- `docs/saas/identity-architecture.md`
- `docs/saas/billing-domain-design.md`

---

# 7. Stage 2 â€” Account, Organization, and Workspace Model

Support both individual and organization accounts.

## Required Concepts

### User

A platform identity that can:

- Own a personal workspace
- Join multiple organizations
- Join multiple workspaces
- Have different roles in different organizations
- Access different deployments based on authorization

### Personal Workspace

Each user may have a personal workspace.

### Organization

An organization represents a customer, company, institution, team, or enterprise.

An organization may have:

- Multiple members
- Multiple workspaces
- Multiple deployments
- Multiple domains
- Multiple subscriptions or entitlements
- Multiple administrators

### Workspace

A workspace is an operational environment belonging to a user or organization.

Do not merge the concepts of organization and workspace.

## Suggested Entities

Evaluate and implement only where appropriate:

- `users`
- `identity_accounts`
- `sessions`
- `organizations`
- `organization_applications`
- `organization_memberships`
- `organization_roles`
- `workspaces`
- `workspace_memberships`
- `workspace_settings`
- `workspace_domains`
- `workspace_invitations`
- `workspace_access_logs`

Document ownership and relationships before creating migrations.

---

# 8. Stage 3 â€” Organization Registration and Approval

## Required Flow

1. Anyone can create a platform account.
2. A user can create a personal workspace.
3. A user can submit an organization application.
4. The platform applies configurable automatic-approval rules.
5. Approved applications create or activate an organization.
6. Applications that require review enter a manual-review queue.
7. Platform administrators can approve, reject, suspend, or request additional information.
8. Every decision is audited.

## Configurable Approval Rules

Design platform-admin-configurable rules for:

- Email verification status
- Domain verification
- Organization type
- Country or region
- Risk indicators
- Duplicate organization detection
- Requested deployment model
- Requested data-residency region
- Requested plan or contract type
- User history
- Manual-review requirements

Do not implement invasive or unjustified profiling.

Create:

- Application status lifecycle
- Admin review interface
- Review notes
- Approval and rejection reasons
- Audit events
- Notifications
- Rate limits
- Abuse prevention
- Duplicate detection

---

# 9. Stage 4 â€” Tenant Isolation and Authorization

Tenant isolation is a critical security requirement.

## Requirements

- Every tenant-owned resource must have explicit ownership.
- Use tenant-aware services, repositories, scopes, or query boundaries.
- Never trust a tenant ID supplied by the client.
- Resolve tenant context from authenticated membership and validated routing.
- Validate organization membership and workspace membership server-side.
- Prevent cross-tenant reads, writes, updates, deletes, exports, notifications, and broadcasts.
- Ensure background jobs carry validated tenant context.
- Ensure queued notifications cannot cross tenant boundaries.
- Ensure file storage is tenant-isolated.
- Ensure cache keys include tenant context where necessary.
- Ensure search and reporting are tenant-scoped.
- Ensure real-time channels authorize tenant membership.
- Ensure exports cannot include data from another tenant.

Create automated tests for:

- Cross-tenant model access
- Cross-tenant API access
- Cross-tenant Livewire actions
- Cross-tenant exports
- Cross-tenant notifications
- Cross-tenant broadcasts
- Cross-tenant background jobs
- Tenant suspension
- Unauthorized workspace switching

Create:

`docs/saas/tenant-security-model.md`

---

# 10. Stage 5 â€” Delegated Platform Administration

Support multiple platform-level roles with delegated permissions.

## Suggested Platform Roles

- Platform Owner
- Platform Administrator
- Trust and Safety Administrator
- Billing Administrator
- Deployment Administrator
- Support Administrator
- Security Administrator
- Regional Administrator
- Read-only Auditor

## Suggested Permissions

- `organizations.review`
- `organizations.approve`
- `organizations.suspend`
- `organizations.view`
- `billing.manage_plans`
- `billing.manage_subscriptions`
- `billing.view_records`
- `deployments.provision`
- `deployments.update`
- `deployments.suspend`
- `deployments.decommission`
- `security.view_events`
- `audit_logs.read`
- `customer_data.access`
- `regions.manage`
- `branding.manage`
- `licenses.manage`

Use least privilege.

Sensitive customer-data access must require:

- Explicit permission
- A documented reason
- Audit logging
- Appropriate visibility to authorized administrators
- Optional time-limited access where practical

---

# 11. Stage 6 â€” Deployment and Infrastructure Abstraction

Support these deployment models:

1. Shared SaaS
2. Provider-owned and provider-managed dedicated deployment
3. Customer-funded managed deployment
4. Customer-hosted deployment

## Deployment Lifecycle

Use a documented lifecycle such as:

- Requested
- Under review
- Approved
- Provisioning
- Health verification
- Active
- Maintenance
- Suspended
- Decommissioned
- Failed

## Deployment Registry

Track:

- Deployment ID
- Organization
- Workspace or tenant association
- Deployment model
- Ownership model
- Hosting provider
- Region
- Runtime version
- Application version
- Database location
- Storage location
- Backup policy
- Upgrade channel
- Provisioning status
- Health status
- License status
- Last health check
- Maintenance state
- Decommission date

For customer-hosted environments, design a secure outbound registration or deployment-agent mechanism. Do not expose control-plane credentials unnecessarily.

Start with manual or semi-automated provisioning. Automate only after the deployment model is proven.

Create:

- Deployment service interfaces
- Deployment health checks
- Deployment access policies
- Provisioning records
- Upgrade and rollback documentation
- Operational runbooks

---

# 12. Stage 7 â€” Regions and Data Residency

Allow customers to nominate preferred regions from the beginning, but only advertise regions that are actually supported.

Create a region registry containing:

- Region code
- Display name
- Hosting providers
- Available deployment models
- Database availability
- Object-storage availability
- Backup location
- Disaster-recovery options
- Data-processing limitations
- Compliance requirements
- Operational status
- Supported application versions

Design for:

- Shared regional deployments
- Dedicated regional deployments
- Region-aware routing
- Region-specific backups
- Cross-region access restrictions
- Data-residency policy enforcement
- Documented limitations

Do not claim legal or regulatory compliance without verification.

---

# 13. Stage 8 â€” Authentication and Identity

Support both:

## Platform-First Login

1. User visits the main Opsora platform.
2. User signs in.
3. User selects a personal workspace or organization workspace.
4. The platform routes the user to the correct workspace or deployment.

## Workspace-First Login

1. User visits a workspace URL.
2. The platform identifies the workspace.
3. The user authenticates.
4. The backend validates membership, workspace status, deployment status, and entitlements.
5. Access is granted only if all checks pass.

A workspace URL or company code must never grant access by itself.

## Initial Authentication

Start with secure basic authentication and existing Laravel authentication mechanisms.

Design extension points for:

- OIDC
- SAML
- MFA
- SCIM
- Enterprise identity providers
- Passkeys
- Organization-specific authentication policies

Use secure session handling, token revocation, rate limiting, account lockout protections, and audit logs.

---

# 14. Stage 9 â€” Routing, Domains, and Onboarding

Support:

- Subdomain workspaces
- Path-based workspaces
- Custom domains
- Domain verification
- Company codes
- QR-code onboarding
- Workspace discovery
- Deployment-aware routing

Examples:

- `company.opsora.example`
- `opsora.example/company`
- `operations.customer-domain.example`

Do not hard-code the final production domain until it is confirmed.

Routing must validate:

- Domain ownership
- Workspace existence
- Workspace status
- Organization status
- Deployment status
- User membership
- Subscription or license entitlements
- Region and deployment mapping

Create a safe fallback for unknown or suspended workspaces.

---

# 15. Stage 10 â€” Billing, Subscriptions, Entitlements, and Licenses

Build the billing architecture first. Payment activation can happen later.

## Supported Commercial Models

- Free tier
- Paid SaaS
- Free trials
- Monthly subscriptions
- Annual subscriptions
- User-based pricing
- Monitored-service-based pricing
- Feature-based pricing
- Enterprise custom pricing
- One-time license keys
- Self-hosted enterprise licensing
- Contract-based enterprise plans

## Suggested Entities

- `subscription_plans`
- `plan_versions`
- `plan_features`
- `subscriptions`
- `subscription_items`
- `entitlements`
- `usage_meters`
- `usage_records`
- `invoices`
- `payment_records`
- `license_keys`
- `license_activations`
- `billing_accounts`
- `billing_events`
- `provider_customers`

## Requirements

- Version plans instead of mutating historical pricing.
- Separate plan definitions from entitlements.
- Support trials and grace periods.
- Support subscription suspension and cancellation.
- Support usage limits.
- Support feature flags.
- Support license activation and revocation.
- Support multiple future payment providers through interfaces.
- Keep payment-provider logic behind adapters.
- Do not activate real payment collection until explicitly approved.
- Ensure billing decisions are enforced server-side.
- Audit all billing changes.

---

# 16. Stage 11 â€” Existing SRE Workflow Modules

Preserve and migrate the existing modules into tenant-aware architecture.

Audit and support, where present:

- Dashboard
- Daily shift activities
- Task assignments
- Shift handovers
- Handover sign-offs
- Incident management
- Incident escalation
- Incident status updates
- Direct messaging
- Team channels
- War rooms
- Notifications
- Reports
- Exports
- Team management
- Role-based access control
- Audit logs
- System health diagnostics
- Email notifications
- Real-time updates
- Background jobs

For every module:

1. Identify ownership.
2. Identify required tenant context.
3. Identify authorization rules.
4. Identify API requirements.
5. Identify Livewire requirements.
6. Identify Flutter requirements.
7. Add isolation tests.
8. Preserve current behavior.
9. Avoid fake metrics or placeholder production data.

---

# 17. Stage 12 â€” Future Operations and Monitoring Architecture

Design for deeper operations capabilities without implementing everything immediately.

## Future Modules

- Service catalog
- Uptime monitoring
- Metrics
- Logs
- Traces
- Alert ingestion
- Alert correlation
- Escalation policies
- SLOs
- SLIs
- Error budgets
- Cloud integrations
- Kubernetes integrations
- Infrastructure discovery
- Runbooks
- Automated remediation
- On-call scheduling
- Status pages
- Webhooks
- Third-party integrations

The initial launch should focus on workflow management and operational collaboration.

Use clear module boundaries so future monitoring features can be added without destabilizing the existing platform.

---

# 18. Stage 13 â€” Flutter Multi-Workspace Application

The existing Flutter Android and iOS applications must evolve into an Opsora multi-workspace client.

## Required Onboarding Methods

Support:

1. Workspace URL
2. Company code
3. QR code
4. Platform account login
5. Workspace selection
6. Multiple saved workspaces

## Mobile Requirements

- Preserve the existing visual design and colors initially.
- Support iOS conventions and platform-appropriate navigation.
- Support Android conventions where appropriate.
- Securely store credentials and tokens.
- Never log tokens or secrets.
- Support workspace switching.
- Support multiple accounts where practical.
- Support deep links.
- Support push notifications.
- Support offline states.
- Support loading, error, and empty states.
- Support tablet layouts where practical.
- Support tenant-aware API requests.
- Support deployment-aware routing.
- Support enterprise-branded apps through a configurable build/branding system later.

## Mobile Architecture

Use a maintainable feature-first structure.

Evaluate the existing stack before changing it. Do not replace working libraries without justification.

Maintain clear layers for:

- Authentication
- Workspace discovery
- Workspace selection
- API client
- Secure storage
- Session management
- Dashboard
- Activities
- Handovers
- Incidents
- Messaging
- Channels
- War rooms
- Notifications
- Reports
- Settings
- Profile
- Offline synchronization

---

# 19. Stage 14 â€” Enterprise White-Label Capability

Design an optional white-label capability for selected enterprise customers.

Support, depending on contract:

- Custom application name
- Custom logo
- Custom domain
- Custom app identifiers
- Custom app-store metadata
- Custom email branding
- Custom login experience
- Custom mobile application builds
- Enterprise-specific configuration

Do not implement uncontrolled per-customer forks.

Use a configuration-driven approach with controlled build pipelines and version management.

---

# 20. Stage 15 â€” Migration from the Existing Npontu Environment

Keep the existing Npontu environment separate during transition.

Build a migration tool rather than performing an unsafe direct conversion.

## Migration Pipeline

1. Read-only extraction
2. Schema and data inspection
3. Mapping configuration
4. Dry-run validation
5. Staging import
6. Relationship validation
7. Duplicate and orphan detection
8. Approved production import
9. Reconciliation
10. Audit report
11. Rollback or recovery procedure

The migration tool should support:

- Batch processing
- Retry handling
- ID mapping
- Legacy-to-new ownership mapping
- User mapping
- Organization mapping
- Workspace mapping
- Conflict reporting
- Data validation
- Import logs
- Reconciliation summaries

Do not modify historical records unnecessarily.

---

# 21. Stage 16 â€” Security and Compliance Foundations

Implement:

- HTTPS-only production traffic
- Secure cookies
- CSRF protection where applicable
- Strong password handling
- Rate limiting
- Authorization policies
- Tenant isolation
- Secure file uploads
- Input validation
- Output encoding
- Secret management
- Dependency scanning
- Security headers
- Audit logging
- Session revocation
- Token rotation where appropriate
- Backup and restore procedures
- Access reviews
- Security event monitoring
- Secure error handling
- No sensitive data in logs

Create:

`docs/security/opsora-threat-model.md`

Include threats such as:

- Tenant data leakage
- Broken access control
- Workspace spoofing
- Domain takeover
- Session theft
- Token leakage
- Malicious organization registration
- Abuse of automatic approval
- Cross-tenant exports
- Customer-hosted deployment compromise
- Supply-chain vulnerabilities
- Privileged administrator misuse
- Billing entitlement manipulation

---

# 22. Stage 17 â€” Testing Strategy

## Backend Tests

Use the repositoryâ€™s established testing framework.

Add tests for:

- Brand migration
- Authentication
- Authorization
- Organization registration
- Approval rules
- Manual review
- Personal workspaces
- Multiple organization memberships
- Workspace switching
- Tenant isolation
- Deployment lifecycle
- Region restrictions
- Subscription entitlements
- License keys
- Trial periods
- API contracts
- Notifications
- Queued jobs
- Exports
- Real-time authorization
- Migration tooling
- Audit logs

## Flutter Tests

Add:

- Unit tests
- Widget tests
- Integration tests
- Authentication tests
- Workspace-discovery tests
- Workspace-switching tests
- Offline-state tests
- Token-storage tests
- Deep-link tests
- Push-notification tests
- Error-state tests
- Accessibility checks where practical

## Security Tests

Include:

- Cross-tenant access attempts
- IDOR-style tests
- Unauthorized workspace access
- Role-escalation tests
- Suspended-tenant access
- Expired-session tests
- Invalid-token tests
- Rate-limit tests
- Export-isolation tests

Do not report completion until tests pass or known failures are documented.

---

# 23. Stage 18 â€” API and Documentation

Use versioned APIs, such as:

- `/api/v1/auth`
- `/api/v1/me`
- `/api/v1/workspaces`
- `/api/v1/organizations`
- `/api/v1/dashboard`
- `/api/v1/activities`
- `/api/v1/handovers`
- `/api/v1/incidents`
- `/api/v1/messages`
- `/api/v1/channels`
- `/api/v1/war-rooms`
- `/api/v1/notifications`
- `/api/v1/reports`
- `/api/v1/team`
- `/api/v1/health`

Use:

- API Resources
- Form Requests
- Consistent response envelopes
- Consistent error formats
- Correct HTTP status codes
- Pagination
- Filtering
- Sorting
- ISO 8601 timestamps
- Request IDs
- Rate-limit responses
- Authorization checks

Create and maintain:

`docs/api/openapi.yaml`

Document:

- Authentication
- Workspace context
- Organization context
- Error responses
- Pagination
- Permissions
- Webhooks
- Versioning
- Deprecation policy

---

# 24. Stage 19 â€” Observability and Operations

Add appropriate operational visibility for Opsora itself:

- Application logs
- Structured logs
- Error tracking
- Queue monitoring
- Database monitoring
- Health checks
- Deployment health
- Uptime checks
- Audit events
- Security events
- Usage metrics
- Subscription metrics
- Tenant-level operational metrics

Do not expose internal platform metrics to customers unless explicitly designed and authorized.

---

# 25. Stage 20 â€” CI/CD and Deployment

Support a scalable managed deployment approach using Render or a comparable managed platform, while keeping infrastructure abstraction flexible.

## Backend Pipeline

Include:

- Dependency installation
- Static analysis
- Formatting checks
- Unit tests
- Feature tests
- Security checks
- Migration checks
- Build verification
- Deployment health checks
- Rollback documentation

## Flutter Pipeline

Include:

- Formatting
- Static analysis
- Unit tests
- Widget tests
- Integration tests where available
- Android build validation
- iOS build validation where the environment permits
- Secure signing configuration
- Environment configuration
- Release-channel management

## Environment Separation

Maintain separate environments for:

- Local development
- Testing
- Staging
- Shared production
- Dedicated customer environments
- Legacy Npontu transition environment

Never commit production secrets.

---

# 26. Recommended Implementation Order

Implement the work in this order:

1. Brand-only audit and Opsora name replacement
2. Safety baseline and repository audit
3. Domain model and ownership design
4. Account, organization, and workspace foundations
5. Tenant context and isolation
6. Organization registration and approval
7. Delegated platform administration
8. Deployment abstraction
9. Region and data-residency foundations
10. Authentication and routing
11. Billing and entitlement architecture
12. Existing SRE workflow migration
13. Flutter multi-workspace support
14. Migration tooling
15. Security hardening
16. Testing and quality gates
17. CI/CD and deployment
18. Pilot readiness
19. Enterprise white-label capabilities
20. Future monitoring modules

Do not implement all stages in one uncontrolled change set.

---

# 27. Required Documentation

Maintain these documents:

- `docs/saas/brand-migration-audit.md`
- `docs/saas/current-state-audit.md`
- `docs/saas/domain-model.md`
- `docs/saas/resource-ownership-matrix.md`
- `docs/saas/tenant-boundary-map.md`
- `docs/saas/tenant-security-model.md`
- `docs/saas/deployment-architecture.md`
- `docs/saas/data-residency-design.md`
- `docs/saas/identity-architecture.md`
- `docs/saas/billing-domain-design.md`
- `docs/saas/migration-strategy.md`
- `docs/saas/risk-register.md`
- `docs/saas/architecture-decision-records.md`
- `docs/api/openapi.yaml`
- `docs/security/opsora-threat-model.md`
- Deployment runbooks
- Rollback runbooks
- Backup and restore documentation
- Tenant onboarding documentation
- Admin operation documentation
- Mobile release documentation

---

# 28. Definition of Done

The project is complete only when:

- The customer-facing brand is Opsora.
- Existing colors and layouts remain unchanged.
- The legacy Npontu environment remains separate.
- The repository has been audited.
- The target architecture is documented.
- Personal and organization workspaces are supported.
- Users can belong to multiple organizations.
- Organization approval rules are configurable.
- Manual review is available.
- Tenant isolation is enforced and tested.
- Delegated platform administration is implemented.
- Deployment models are represented safely.
- Region and data-residency concepts are documented.
- Authentication supports platform-first and workspace-first flows.
- Routing supports planned workspace access methods.
- Billing architecture exists without prematurely activating payments.
- Existing SRE workflow modules remain functional.
- Flutter supports multi-workspace onboarding.
- Migration tooling is available and tested.
- Security controls are implemented.
- API documentation is maintained.
- Backend and Flutter tests pass or documented exceptions exist.
- CI/CD checks are configured.
- Monitoring and rollback procedures exist.
- No secrets are committed.
- No unverified compliance claims are made.
- Every completed stage has evidence, tests, and documentation.

---

# 29. Antigravity Execution Instructions

Begin with **Stage 0 and the brand-only migration audit**.

Do not immediately redesign the application.

Your first response and first implementation cycle must contain:

1. A repository audit summary
2. A list of all Npontu references
3. A classification of each reference
4. A proposed Opsora brand migration plan
5. A list of single-company assumptions
6. A proposed domain model
7. A tenant-boundary risk assessment
8. A staged implementation plan
9. A list of files that will be changed
10. A list of files that will not be changed
11. A rollback plan
12. Tests to be run before and after the first change

Initially, implement only the safe brand configuration and customer-facing name replacement after the audit is reviewed.

Do not change colors, layouts, or unrelated functionality.

After each stage:

- Summarize changes
- List changed files
- List migrations
- List tests run
- Report test results honestly
- Identify known risks
- Provide rollback instructions
- Wait for approval before starting the next high-risk stage

The goal is to evolve the existing product into **Opsora**, a scalable technical operations SaaS platform, while preserving the existing product experience and protecting customer data.



the best thing do a new github brach and push it new dont mix with old one a deployed one 