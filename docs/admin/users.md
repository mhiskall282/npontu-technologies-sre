# Platform User Administration & Identity Governance

> **Module:** `/admin/platform/users`  
> **Actions:** `SuspendUserAction`, `ReactivateUserAction`

## Overview

The Platform User Management module allows authorized administrators to inspect all registered accounts across all tenant organizations, escalate platform privileges, enforce administrative suspensions, and invalidate compromised tokens.

---

## Key Governance Actions

### 1. Platform Role Escalation (`PATCH /admin/platform/users/{id}/role`)
- Assigns or revokes platform-level administration privileges (`SuperAdmin`, `PlatformAdmin`, `BillingAdmin`, etc.).
- Changing a user's platform role automatically creates an `AuditLog` entry and a `SecurityEvent` record with old/new role values.
- Tenant users have `platform_role = null`.

### 2. User Suspension (`POST /admin/platform/users/{id}/suspend`)
- Sets `suspended_at = now()`.
- Automatically revokes all active Personal Access Tokens (Sanctum) and invalidates active sessions.
- Injects a suspension block inside `LoginRequest` and Sanctum token validation so suspended users cannot authenticate.
- Prevents self-suspension of the active administrator.

### 3. Session & Token Revocation (`POST /admin/platform/users/{id}/revoke-tokens`)
- Deletes all personal access tokens in the `personal_access_tokens` table associated with the user.
- Emits a SIEM event logging the count of revoked tokens and the initiating administrator.
