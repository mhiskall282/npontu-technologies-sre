# Opsora SRE Platform — MVP Master Test Plan

---

## 1. Document Overview

This Master Test Plan outlines the testing strategy, operational scope, verification methodologies, and pass/fail quality criteria for the **Opsora SRE Software-as-a-Service (SaaS) Platform** (v1.0.0-MVP).

---

## 2. Test Objectives

1. **Verify Functional Completeness**: Ensure all core shift operations, checklists, handovers, diagnostics, and team collaboration modules function without fatal exceptions or data corruption.
2. **Enforce Tenant Boundary Isolation**: Prove mathematically and empirically that no organization or workspace can view, mutate, or infer data belonging to another tenant via UI or API.
3. **Validate Authentication & Authorization**: Verify that role permissions (`admin`, `lead`, `engineer`) are strictly enforced at the HTTP middleware, controller, Livewire, and database levels.
4. **Ensure Audit Trail Compliance**: Ensure that 100% of data-mutating events are captured in the immutable audit log with user attribution, timestamp, client IP, and diff snapshots.
5. **Certify Mobile Performance & Reliability**: Verify that the Flutter mobile app starts smoothly without white flashes, synchronizes offline telemetry, and provides native-grade usability.

---

## 3. Scope of Testing

### 3.1 In-Scope (MVP Release)
- **Web Application (Blade + Livewire 3)**:
  - User registration & authentication (Login, Logout, Session lifecycle)
  - Personal & Organization workspace onboarding & switching
  - Daily SRE Activity management (Create, Update, Filter, Search, Pagination, Mandatory remarks on status change)
  - Shift Handover protocol (Drafting, Signing off, Counter-signing, Roll-over of incomplete items)
  - Diagnostic System Telemetry (Live Ping probes, API Latency, MySQL health)
  - Organization Member management & role assignment
  - Audit Trail inspection & CSV export
  - In-app notification center
  - Markdown-based Documentation Portal (`/docs`)
- **REST API v1 Layer**:
  - Bearer token authentication via Laravel Sanctum
  - Multi-tenant workspace resolution via `X-Workspace-Id` header
  - CRUD operations for Activities, Handovers, System Health, and Audit Logs
  - Standard JSON response envelope (`success`, `data`, `meta`, `errors`)
  - Rate limiting & 401/403/404/422 HTTP exception mapping
- **Mobile Application (Flutter 3.24+ Android/iOS)**:
  - Branded startup splash & authentication state bootstrap
  - Offline cache hydration & automatic retry
  - Cockpit Dashboard with real-time shift metrics and priority breakdown
  - Shift Checklist screen with filter chips and search
  - Shift Handover screen with interactive counter-signature flow
  - Push notification channel registration & settings
  - Local biometrics integration & statutory privacy screens

### 3.2 Out-of-Scope (Post-MVP / Future Phases)
- Real-time video conferencing inside incident war-rooms (Ops chat is text/telemetry only in MVP).
- Automated credit card payment gateway billing (Subscription plans and seat licenses are modeled, but live Stripe/Paystack checkout is deferred).
- Self-hosted on-premise air-gapped deployment packages.

---

## 4. Test Levels & Strategy

```
                       /\
                      /  \
                     / E2E\        End-to-End User Workflows
                    /------\       (Web + Mobile + API)
                   /  Integ \      Feature & API Tests (139 Pest Tests)
                  /----------\     Policy, Gate & Form Request Authorization
                 /    Unit    \    Actions, Services, Data Models, DTOs
                /--------------\   Flutter Widget & Unit Tests (25 Tests)
```

### 4.1 Unit Testing
- Models, Actions, Enums, DTOs, and utility services tested in isolation using Pest PHP and Dart `flutter_test`.

### 4.2 Feature & Integration Testing
- Form Request validation rules.
- Policy authorization matrix (Admin vs Lead vs Engineer).
- Database migration down/up idempotency and foreign key cascading.
- Event listeners, jobs, and notification dispatches.

### 4.3 API Contract Testing
- OpenAPI 3.0 specification compliance.
- HTTP status code adherence (`200 OK`, `201 Created`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `422 Unprocessable Entity`).

### 4.4 Tenant Isolation Security Testing
- Injected cross-tenant IDs, malicious header manipulations, and URL tampering to detect IDOR vulnerabilities.

---

## 5. Environmental Requirements & Tools

| Component | Specification | Tooling / Framework |
| :--- | :--- | :--- |
| **Backend Test Runner** | Pest PHP 2.x on PHP 8.2 | `./vendor/bin/pest` |
| **Code Style & Linting** | Laravel Pint (PSR-12) | `./vendor/bin/pint --test` |
| **Mobile Test Runner** | Flutter Test Framework | `flutter test` |
| **Mobile Static Analysis**| Dart Analyzer | `flutter analyze` |
| **API Testing** | cURL / Postman / Insomnia | OpenAPI v3 Specification |
| **Database Engine** | MySQL 8.0 / SQLite Memory | InnoDB Engine |

---

## 6. Exit & Release Criteria

Testing shall be considered complete and the MVP release authorized only when:
1. **100% of automated Pest backend tests pass** with zero failures.
2. **100% of Flutter mobile tests pass** with zero failures.
3. **Static code analysis (`pint` & `flutter analyze`) produces 0 errors**.
4. **Zero open Critical (P1) or Major (P2) defects** exist in the bug tracking registry.
5. All mandatory acceptance criteria defined in [`acceptance-criteria.md`](./acceptance-criteria.md) are satisfied.
