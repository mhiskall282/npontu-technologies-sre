# Platform Administration REST API Reference

> **Base URL:** `/api/v1/platform`  
> **Authentication:** Bearer token (`auth:sanctum`) with `platform.admin` role

## Overview

The Platform REST API enables external DevOps scripts, custom administrative portals, and automated CI/CD pipelines to interact securely with the Opsora Control Plane.

---

## Authentication Header

```http
Authorization: Bearer <sanctum_platform_admin_token>
Accept: application/json
```

Requests submitted without a valid platform administrator token receive an HTTP `403 Forbidden` response.

---

## Key Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/v1/platform/dashboard` | Returns real-time financial metrics, user/org aggregates, and subsystem health |
| `GET` | `/api/v1/platform/organizations` | Paginated listing of all tenant organizations |
| `GET` | `/api/v1/platform/organizations/{id}` | Detailed tenant profile with workspaces, members, and subscriptions |
| `POST` | `/api/v1/platform/organizations/{id}/suspend` | Administratively suspend an organization |
| `POST` | `/api/v1/platform/organizations/{id}/reactivate` | Reactivate a suspended organization |
| `GET` | `/api/v1/platform/users` | List and search all platform users and engineers |
| `GET` | `/api/v1/platform/users/{id}` | Detailed user profile and session tokens |
| `POST` | `/api/v1/platform/users/{id}/suspend` | Administratively suspend a user |
| `POST` | `/api/v1/platform/users/{id}/reactivate` | Reactivate a suspended user |
| `GET` | `/api/v1/platform/workspaces` | Paginated listing of all operational workspaces |
| `GET` | `/api/v1/platform/plans` | Catalog of commercial SaaS plans and feature matrices |
| `GET` | `/api/v1/platform/subscriptions` | Ledger of active subscriptions |
| `GET` | `/api/v1/platform/features` | Listing of platform feature flags |
| `GET` | `/api/v1/platform/health` | Live subsystem health probes (DB, cache, queue) |
| `GET` | `/api/v1/platform/security-events` | Stream of recent SIEM security events |
| `GET` | `/api/v1/platform/audit-logs` | Immutable audit log records |
