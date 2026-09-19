# Multi-Tenant Workspace Oversight

> **Module:** `/admin/platform/workspaces`  
> **Route:** `/admin/platform/workspaces` & `/admin/platform/workspaces/{id}`

## Overview

Workspaces serve as the operational isolation boundaries within Opsora. All checkoffs, shifts, conversations, and reports are strictly scoped to a single workspace.

---

## Workspace Topologies

1. **Organization Team Workspaces**: Belong to an `organization_id`. Multiple team members can collaborate, and ownership is held by the organization tenant.
2. **Personal Sandboxes (`is_personal = true`)**: Standalone developer workspaces created for solo engineers to test runbooks and simulate incident tracking without organization affiliation.

## Administrative Features

- **Global Catalog**: List and search across all operational workspaces regardless of tenant boundary.
- **Member Inspection**: View all assigned engineers, operators, and leads attached to each workspace.
- **Activity Log Summary**: Review recent operational activities recorded inside the workspace boundary.
- **Strict Read Oversight**: Administrators can inspect telemetry and verify tenant isolation without interfering with ongoing customer operations.
