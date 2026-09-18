# Opsora SaaS Transformation — Tenant Boundary Map

> **Status**: Approved Security Specification (Stage 1 & 4)

---

## 1. Boundary Enforcement Layers

Data separation is enforced across six independent architectural boundaries:

```
[ INCOMING TRAFFIC ]
        │
        ▼
1. ROUTING BOUNDARY
   ├── Subdomain Resolver (`acme.opsora.app` -> Workspace ID)
   ├── Path Resolver (`opsora.app/w/acme` -> Workspace ID)
   └── Company Code Resolver (`ACM-410` -> Workspace ID)
        │
        ▼
2. MIDDLEWARE BOUNDARY (`ValidateTenantContext`)
   ├── Authenticates User Session or Sanctum Token
   ├── Queries `WorkspaceMembership` for User in Resolved Workspace
   ├── Verifies Workspace Status (`active`, not `suspended`)
   └── Binds Singleton `TenantContext` to Laravel Service Container
        │
        ▼
3. ELOQUENT ORM BOUNDARY (`TenantScope`)
   ├── Automatically appends `WHERE workspace_id = {current_workspace_id}`
   ├── Automatically sets `workspace_id = {current_workspace_id}` on Model creation
   └── Throws `ModelNotFoundException` (HTTP 404) if accessing other tenant's ID
        │
        ▼
4. SERVICE & ACTION BOUNDARY
   ├── All Domain Actions require explicit `Workspace` instance or context
   ├── Cross-tenant mutations rejected with `UnauthorizedException` (HTTP 403)
   └── `AuditService` records `workspace_id` on every mutation event
        │
        ▼
5. ASYNCHRONOUS JOBS & NOTIFICATIONS
   ├── Background jobs serialize explicit `workspace_id`
   ├── Job execution bootstraps `TenantContext` before running logic
   └── Notifications dispatched only to verified workspace participants
        │
        ▼
6. CACHE & STORAGE BOUNDARY
   ├── Cache Keys: `opsora:w:{workspace_id}:{resource}:{key}`
   ├── File Storage: `tenants/{organization_id}/workspaces/{workspace_id}/...`
   └── Real-time WebSockets: `private-workspace.{workspace_id}.channel.{id}`
```

---

## 2. Cross-Tenant Leakage Prevention Rules

1. **No Client-Supplied Tenant IDs**:
   - `workspace_id` is NEVER read from `request()->input('workspace_id')`.
   - It is derived strictly from verified route binding and authenticated membership.
2. **Preventing Broken Object Level Authorization (BOLA / IDOR)**:
   - Requesting `GET /api/v1/activities/99` where activity `99` belongs to Workspace B while authenticated in Workspace A returns HTTP 404 (`ModelNotFoundException`), completely hiding the entity's existence.
3. **Export Shielding**:
   - `ReportingService` queries require `workspace_id` in all SQL `JOIN` and `WHERE` clauses.
   - Streamed CSV and PDF generation executes inside the scoped context.
4. **Real-time Channel Isolation**:
   - Livewire and WebSocket broadcast channels verify that the user's active session has a valid `WorkspaceMembership` before granting channel subscription.
