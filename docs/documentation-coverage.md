# Opsora Documentation Coverage Matrix

> **Status:** AUDITED  
> **Last Verified:** 2026-09-18

This document records the exact coverage between the actual codebase and the `/docs` documentation suite.

---

## 1. Documentation Coverage Summary

| Area | Implemented Artifacts | Documented Artifacts | Coverage Status |
|---|---|---|---|
| **Web Routes** | 22 routes in `routes/web.php` | 22 routes | 100% Documented |
| **API Endpoints** | 14 endpoints in `routes/api.php` | 14 endpoints | 100% Documented in `docs/api/` & `openapi.yaml` |
| **Eloquent Models** | 12 operational & tenant models | 12 models | 100% Documented in `docs/database/` |
| **Database Migrations** | 11 migrations with `down()` methods | 11 migrations | 100% Documented in `docs/database/migrations.md` |
| **Flutter Mobile Screens** | 9 screens in `npontu_sre_mobile` | 9 screens | 100% Documented in `docs/mobile/` |
| **Operational Commands** | 2 Artisan commands (`reports`, `migrate`) | 2 commands | 100% Documented in `docs/backend/` |
| **Backend Tests** | 139 Pest tests (655 assertions) | All test suites | 100% Documented in `docs/testing/` |
| **Mobile Tests** | 25 Flutter unit/widget tests | All test files | 100% Documented in `docs/mobile/testing.md` |

---

## 2. Model-to-Tenant Scope Verification

| Model Class | Tenant Sensitive | Uses `BelongsToWorkspace` | Global Scope Active |
|---|---|---|---|
| `App\Models\Activity` | Yes | Yes | `TenantScope` applied |
| `App\Models\ActivityLog` | Yes | Yes | `TenantScope` applied |
| `App\Models\ShiftHandover` | Yes | Yes | `TenantScope` applied |
| `App\Models\Conversation` | Yes | Yes | `TenantScope` applied |
| `App\Models\OperationalNotification` | Yes | Yes | `TenantScope` applied |
| `App\Models\AuditLog` | Yes | Yes | `TenantScope` applied |
| `App\Models\Workspace` | Control Plane | N/A | Organization parent link |
| `App\Models\Organization` | Control Plane | N/A | Root entity |
| `App\Models\User` | Identity Plane | N/A | Cross-tenant membership |

---

## 3. Flutter Screen to API Endpoint Traceability

| Flutter Screen | Associated API Endpoint | Laravel Controller / Action |
|---|---|---|
| `LoginScreen` | `POST /api/v1/auth/login` | `Api\V1\AuthController::login` |
| `DashboardScreen` | `GET /api/v1/dashboard` | `Api\V1\DashboardController::index` |
| `WorkspaceSwitcherSheet` | `GET /api/v1/workspaces` | `Api\V1\WorkspaceController::index` |
| `WorkspaceJoinDialog` | `POST /api/v1/workspaces/join` | `JoinOrganizationByCodeAction` |
| `ActivitiesScreen` | `GET /api/v1/activities` | `Api\V1\ActivityController::index` |
| `ActivityFormScreen` | `POST /api/v1/activities` | `Api\V1\ActivityController::store` |
| `HandoverScreen` | `POST /api/v1/handovers` | `Api\V1\ShiftHandoverController::store` |
| `ReportsScreen` | `GET /api/v1/reports` | `Api\V1\ReportController::index` |
| `SystemHealthScreen` | `GET /api/health` | `HealthController::index` |
