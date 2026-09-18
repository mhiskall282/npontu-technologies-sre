# Opsora Platform — Workspaces & Context Switching

> **Status:** IMPLEMENTED  
> **Last Verified:** 2026-09-18

A **Workspace** is the fundamental operational container in Opsora. All SRE activities, shift handovers, checklists, channels, and audit trails belong to exactly one workspace.

---

## 1. Workspace Types

1. **Organization Workspaces**: Shared operational environments belonging to an organization (e.g., "Primary SRE Operations", "Payment Gateway Ops").
2. **Personal Workspaces (`is_personal = true`)**: Private sandbox environments automatically provisioned for individual engineers and freelancers.

---

## 2. Context Switching Engine

- **Web Switching**: Handled via `POST /workspaces/switch`. Validates that the user holds an active membership, updates the session variable `opsora_workspace_id`, and redirects with flash status.
- **API Switching**: Handled by passing `X-Workspace-Id: <ID_OR_UUID>` in HTTP headers.
- **Membership Protection**: Users cannot switch into or query workspaces they do not hold active membership in (enforced with HTTP `403 Forbidden`).
