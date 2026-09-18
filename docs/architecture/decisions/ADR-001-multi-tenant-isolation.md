# ADR-001: Multi-Tenant Workspace Scoping with Global Query Scopes

## Status
Accepted

## Context
Opsora must transition from a single-company SRE tool into a multi-tenant SaaS platform without breaking existing database structures, destroying legacy data, or introducing premature microservice complexity.

## Decision
We implemented a shared-database, workspace-scoped multi-tenancy model:
1. Every operational model implements `BelongsToWorkspace` and registers `TenantScope`.
2. `TenantContext` service manages the active workspace during the request lifecycle.
3. `ResolveTenantContext` middleware dynamically resolves and validates workspace membership via session or `X-Workspace-Id` HTTP header.
4. `TenantContext::withoutTenancy(callable)` allows audited administrative and background worker bypass.

## Consequences
- **Positive**: Zero data leakage, transparent scoping on all Eloquent queries, full backward compatibility with legacy single-company queries via Workspace #1.
- **Negative**: Developers must remember to use `withoutGlobalScopes()` when building administrative cross-tenant reports.

## Date
2026-09-18
