# Tenant Organization Administration

> **Module:** `/admin/platform/organizations`  
> **Actions:** `SuspendOrganizationAction`, `ReactivateOrganizationAction`

## Overview

The Organization Management module provides comprehensive lifecycle administration for all tenant companies, enterprise customers, and operational squads using Opsora.

---

## Capabilities

### 1. Catalog & Search
- Paginated listing of all tenant organizations.
- Search by organization name, URL slug, or unique company code (`company_code`).
- Filter by status (`active`, `pending`, `suspended`).
- View counts of provisioned workspaces and assigned engineers.

### 2. Tabbed Organization Detail Profile
Each organization has a profile screen at `/admin/platform/organizations/{id}` with structured tabs:
- **Overview**: Tenant metadata, cloud region (`af-south`, `eu-west`, `us-east`), deployment model (`shared_saas`, `dedicated_managed`, `customer_hosted`), and primary company code.
- **Workspaces**: All operational team workspaces provisioned within the tenant's security boundary.
- **Engineers & Members**: All users attached to the organization with their assigned roles (`admin`, `lead`, `member`).
- **Subscription & Commercial Tier**: Active SaaS plan, current billing interval, and renewal period dates.
- **Audit History**: Historical audit trail entries scoped to this specific tenant organization.

### 3. Account Governance (Suspend & Reactivate)
- **Suspension (`POST /admin/platform/organizations/{id}/suspend`)**:
  - Immediately blocks all tenant operators from logging in or using the REST API.
  - Automatically logs a `security_events` record.
  - Records an immutable `AuditLog` entry with the provided suspension justification.
- **Reactivation (`POST /admin/platform/organizations/{id}/reactivate`)**:
  - Restores active status and unblocks member access.
