# Opsora Backend — Laravel 11 Architecture Standards

> **Status:** IMPLEMENTED  
> **PHP Version:** 8.2+  
> **Framework:** Laravel 11 LTS

Opsora adheres strictly to the **PSR-12** standard and enforces thin controllers, Form Requests for validation, Action classes for single-responsibility business logic, and Service classes for cross-domain coordination.

---

## 1. Architectural Rules
1. **Thin Controllers**: Controllers only validate input, call Actions, and return responses or redirects.
2. **Form Requests**: Every mutated state endpoint uses a dedicated Form Request class (`app/Http/Requests/`).
3. **Action Classes**: Reusable domain operations live in `app/Actions/` (e.g., `CreateWorkspaceAction`, `JoinOrganizationByCodeAction`, `ApplyOrganizationAction`, `ReviewOrganizationApplicationAction`).
4. **Policy-Driven Authorization**: Gateways enforce `$this->authorize(...)` using dedicated policy classes (`ActivityPolicy`, `OrganizationPolicy`, `WorkspacePolicy`).
5. **Strict Types**: Every PHP file begins with `declare(strict_types=1);`.
6. **Code Formatting**: Checked via `vendor/bin/pint --test` before every commit.
