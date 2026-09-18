# Opsora Documentation — Master System Index

> **Status:** IMPLEMENTED  
> **Last Verified:** 2026-09-18  
> **Platform Version:** Opsora SRE v1.5.0 Multi-Tenant

Welcome to the central, authoritative documentation hub for **Opsora** — the Site Reliability Engineering and technical operations SaaS platform. This repository and documentation suite govern the Laravel modular monolith backend, Blade/Livewire frontend, and Flutter cross-platform mobile companion.

---

## 🗺️ Master Documentation Directory Tree

```text
docs/
├── README.md                              # This master index and sitemap
├── roadmap.md                             # Implemented vs Partially Implemented vs Planned
├── documentation-coverage.md              # Documentation coverage matrix
├── contributing.md                        # Documentation maintenance guidelines
│
├── architecture/                          # Core System Architecture & Multi-Tenancy
│   ├── overview.md                        # High-level architecture & design philosophy
│   ├── system.md                          # Client -> Control Plane -> Execution Plane
│   ├── multi-tenancy.md                   # TenantScope, BelongsToWorkspace, TenantContext
│   ├── tenant-boundaries.md               # Platform vs Org vs Workspace vs User data
│   ├── resource-ownership.md              # Explicit resource ownership matrix
│   ├── deployment.md                      # Shared SaaS, Dedicated VPC, Customer-Hosted
│   ├── data-residency.md                  # Regional routing and storage boundaries
│   └── decisions/                         # Architecture Decision Records (ADRs)
│       ├── ADR-001-multi-tenant-isolation.md
│       ├── ADR-002-laravel-blade-livewire-stack.md
│       └── ADR-003-flutter-multi-workspace-client.md
│
├── api/                                   # REST API Reference & OpenAPI
│   ├── README.md                          # API conventions, headers, and envelopes
│   ├── quickstart.md                      # Quickstart with curl, JS, and Dart examples
│   ├── authentication.md                  # Sanctum Bearer tokens & session bridge
│   ├── openapi.yaml                       # Complete OpenAPI 3.0 specification
│   └── endpoints/                         # Resource-specific endpoint guides
│       ├── workspaces.md                  # /workspaces, /switch, /join
│       ├── activities.md                  # /activities CRUD & status updates
│       ├── handovers.md                   # /handovers create & acknowledge
│       ├── reports.md                     # /reports date-range & exports
│       └── health.md                      # /health & /telemetry probes
│
├── backend/                               # Laravel Backend Internals
│   ├── laravel.md                         # Framework conventions, PSR-12, Pint
│   ├── jobs.md                            # Background jobs & async processing
│   ├── queues.md                          # Queue configuration & retry workers
│   ├── events.md                          # Domain events & broadcasts
│   ├── listeners.md                       # Security audit & notification listeners
│   ├── notifications.md                   # Multi-channel notification pipeline
│   └── scheduled-tasks.md                 # Daily report crons & shift handovers
│
├── frontend/                              # Web Application (Blade & Livewire)
│   ├── architecture.md                    # Reactive Blade + Livewire 3 stack
│   ├── blade.md                           # Layouts, navigation, and modal components
│   ├── livewire.md                        # DailyActivityBoard & OperationalChat
│   └── design-system.md                   # Brand tokens, colors, typography, cards
│
├── mobile/                                # Flutter Android & iOS Client
│   ├── architecture.md                    # Feature-first state management & Dio client
│   ├── setup.md                           # Local SDK, Windows runner, emulator
│   ├── authentication.md                  # Secure token storage & session handling
│   ├── api-client.md                      # X-Workspace-Id header interceptor
│   ├── navigation.md                      # BottomNavigationBar & GoRouter routes
│   ├── storage.md                         # FlutterSecureStorage & cache
│   ├── notifications.md                   # Push notifications & local alerts
│   ├── deep-links.md                      # Deep link handling & company code join
│   ├── android.md                         # Android 14+ permissions & loopback (10.0.2.2)
│   ├── ios.md                             # iOS Podfile, capabilities, Info.plist
│   ├── testing.md                         # Widget & integration test verification (25 tests)
│   └── releases.md                        # AAB & IPA build and signing
│
├── platform/                              # Multi-Tenant Platform Core
│   ├── organizations.md                   # Registration, risk scoring, auto-approval
│   ├── workspaces.md                      # Personal and Org workspaces, switching
│   ├── domains.md                         # Subdomain, path-based, and custom domains
│   ├── notifications.md                   # Real-time operational communications
│   └── administration.md                  # Platform roles, approval queue, audit
│
├── product/                               # SRE Operational Modules
│   ├── dashboard.md                       # Operational metrics & active shift status
│   ├── activities.md                      # Daily recurring operational checklist
│   ├── handovers.md                       # 2-way briefing sign-off protocol
│   ├── incidents.md                       # Severity flags & ticket escalation
│   ├── escalations.md                     # Tiered alerts & on-call contacts
│   ├── messaging.md                       # Operational chat & direct messaging
│   ├── channels.md                        # Ops channels & war rooms
│   ├── war-rooms.md                       # Real-time incident collaboration
│   ├── reports.md                         # Compliance reporting & CSV exports
│   └── health.md                          # Diagnostic probes & latency telemetry
│
├── billing/                               # Commercial & Entitlement Architecture
│   ├── overview.md                        # Subscription models & monetization
│   ├── plans.md                           # Free, Team, Enterprise tier definitions
│   ├── subscriptions.md                   # Lifecycle & renewal state machine
│   ├── entitlements.md                    # Feature flags & quota enforcement
│   ├── usage-metering.md                  # Monitored checks & user metrics
│   ├── trials.md                          # Trial periods & grace periods
│   ├── invoices.md                        # Invoice generation & records
│   ├── license-keys.md                    # Offline & on-premise license validation
│   └── payment-providers.md               # Payment gateway abstraction adapters
│
├── database/                              # MySQL 8.0 Data Architecture
│   ├── overview.md                        # Schema overview & entity dictionary
│   ├── schema.md                          # Comprehensive table and column specs
│   ├── relationships.md                   # Foreign keys, cascades, polymorphic links
│   ├── indexing.md                        # Compound indexes & query optimizations
│   ├── migrations.md                      # Migration list, down() methods, history
│   ├── seeders.md                         # UserSeeder & ActivitySeeder personas
│   └── backups.md                         # mysqldump snapshots & point-in-time recovery
│
├── security/                              # Security Posture & Compliance
│   ├── overview.md                        # Defense in depth & zero trust
│   ├── authentication.md                  # Passwords, tokens, sessions, lockout
│   ├── authorization.md                   # Policies, FormRequests, permission matrix
│   ├── tenant-isolation.md                # Scopes, foreign keys, leak prevention
│   ├── threat-model.md                    # Threat model & risk mitigations
│   ├── secrets.md                         # Secret management & zero-leak policy
│   ├── encryption.md                      # TLS 1.3, bcrypt, encrypted cookies
│   ├── audit-logging.md                   # Immutable audit trail standard
│   ├── secure-development.md              # Security coding guidelines & Pint
│   └── incident-response.md               # Incident classification & escalation
│
├── deployment/                            # Deployment & Infrastructure
│   ├── architecture.md                    # Render / AWS / Managed VPC architecture
│   ├── shared-saas.md                     # Multi-tenant shared cluster
│   ├── dedicated.md                       # Dedicated VPC managed deployment
│   ├── customer-managed.md                # Customer-funded AWS/Azure deployment
│   ├── customer-hosted.md                 # Self-hosted / on-premise deployment
│   ├── environments.md                    # Local, Test, Staging, Production
│   ├── configuration.md                   # Environment variables & caching
│   ├── environment-variables.md           # Exhaustive production environment variables guide
│   ├── secrets.md                         # Key management & rotation
│   ├── backups.md                         # Automated S3/GCS backups
│   ├── disaster-recovery.md               # RTO / RPO and recovery protocols
│   ├── upgrades.md                        # Rolling deploy & maintenance mode
│   └── rollback.md                        # Emergency rollback runbook
│
├── devops/                                # Continuous Delivery & CI
│   └── ci-cd.md                           # GitHub Actions / Render build pipelines
│
├── development/                           # Local Developer Experience
│   ├── setup.md                           # Step-by-step developer onboarding
│   ├── environment-variables.md           # Exhaustive .env dictionary
│   ├── testing.md                         # Test execution guide (Pest & Flutter)
│   └── contribution.md                    # Conventional commits & PR checklist
│
├── testing/                               # Quality Assurance Strategy
│   ├── overview.md                        # Testing philosophy & test pyramid
│   ├── backend.md                         # Feature & Unit Pest tests (139 tests)
│   ├── api.md                             # REST API contract tests
│   ├── mobile.md                          # Flutter widget & integration tests (25 tests)
│   ├── security.md                        # Tenant isolation & authorization tests
│   ├── tenant-isolation.md                # Cross-tenant query leak tests
│   └── end-to-end.md                      # End-to-end operational workflows
│
├── mvp/                                   # MVP Testing & Pilot Package
│   ├── README.md                          # MVP testing package overview
│   ├── test-plan.md                       # Master test plan & scope
│   ├── test-cases.md                      # Step-by-step test cases across suites
│   ├── acceptance-criteria.md             # Formal release quality gates
│   ├── test-environment.md                # Test sandbox setup & personas
│   ├── bug-reporting.md                   # Defect triage & severity matrix
│   ├── pilot-onboarding.md                # Pilot customer onboarding runbook
│   └── release-checklist.md               # Pre-flight production sign-off
│
├── integrations/                          # External Services & Webhooks
│   └── overview.md                        # Email SMTP/SES, webhooks, monitors
│
├── admin/                                 # Platform Administration
│   └── overview.md                        # Platform admin queue & operations
│
├── customer/                              # Customer Journeys
│   └── onboarding.md                      # Customer registration & team invites
│
├── enterprise/                            # Enterprise Offerings
│   └── overview.md                        # White-label, SLA, dedicated support
│
├── migration/                             # Legacy Environment Migration
│   └── overview.md                        # Legacy Npontu migration & reconciliation
│
├── troubleshooting/                       # Runbooks & Issue Diagnosis
│   └── overview.md                        # Common errors, diagnosis & solutions
│
├── redesign/                              # Architecture Boundaries
│   └── overview.md                        # Redesign boundaries & compatibility
│
└── ai/                                    # AI Coding Agent Protocols
    └── agent-guide.md                     # Antigravity / AI coding agent manual
```
