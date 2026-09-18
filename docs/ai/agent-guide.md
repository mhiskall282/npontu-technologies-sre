# Opsora — AI Coding Agent Architecture Guide

> **Status:** AUTHORITATIVE  
> **Audience:** AI Coding Assistants & Automated Engineering Agents

When performing maintenance, refactoring, or feature development in this repository, follow these cardinal rules:

---

## 1. Operating Rules & Constraints

1. **Branch Discipline**: Never push unverified changes to `main`. All SaaS transformation work must proceed on `feat/opsora-saas`.
2. **Multi-Tenancy Invariant**: Never query operational models (`Activity`, `ShiftHandover`, etc.) without workspace scoping. Operational models automatically apply `TenantScope`. When cross-tenant querying is required for background workers, use `TenantContext::withoutTenancy(callable)`.
3. **Audit Trail Invariant**: Every user-facing mutation MUST record an immutable audit entry via `AuditService::log()`.
4. **Reversible Migrations**: Every new migration must implement a functional `down()` rollback method.
5. **Coding Standards**: Adhere strictly to **PSR-12**. Always run `vendor/bin/pint --test` before committing.
6. **Brand Tokens**: Do not change colors or layouts. Use `#1B6B3A` green, `#F5C518` gold, `#E63946` red, and `#08120B` dark background.
