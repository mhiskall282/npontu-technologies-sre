# Opsora Documentation & Engineering Contribution Guide

> **Status:** AUTHORITATIVE  
> **Last Verified:** 2026-09-18

This guide outlines rules for maintaining and extending the Opsora platform and its documentation suite.

---

## 1. Documentation Maintenance Protocol

Whenever modifying code in the repository:
1. **API Changes**: If adding or altering an endpoint in `routes/api.php`, update:
   - `docs/api/openapi.yaml`
   - `docs/api/endpoints/<resource>.md`
   - `docs/documentation-coverage.md`
2. **Database Changes**: If creating a migration:
   - Ensure the migration strictly implements a reversible `down()` method.
   - If adding tenant-owned operational records, add `workspace_id` foreign key and use the `BelongsToWorkspace` trait.
   - Update `docs/database/schema.md` and `docs/database/migrations.md`.
3. **Mobile Client Changes**:
   - If adding a new screen or network request in `npontu_sre_mobile`, ensure `X-Workspace-Id` is propagated via `ApiClient`.
   - Update `docs/mobile/architecture.md` and add automated tests under `test/`.

---

## 2. Code Quality Gates Before Commit

Run the mandatory quality verification sequence:
```bash
# 1. PHP Linting & PSR-12 Enforcement
vendor/bin/pint --test

# 2. Pest Backend Test Suite (All tests must pass)
php artisan test

# 3. Flutter Mobile Test Suite
cd npontu_sre_mobile && flutter test
```

---

## 3. Git Commit Discipline

Follow Conventional Commits:
- `feat:` for new capabilities or user-facing enhancements.
- `fix:` for bug fixes.
- `docs:` for documentation updates.
- `test:` for test additions or improvements.
- `refactor:` for internal improvements without behavior changes.
- `chore:` for dependency or build adjustments.
