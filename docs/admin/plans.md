# Commercial SaaS Plan Catalog

> **Module:** `/admin/platform/plans`  
> **Model:** `App\Models\Plan` (`saas_plans` table)

## Overview

Pricing and plan entitlements in Opsora are fully dynamic and managed in the database layer. No plan prices, user limits, or feature matrices are hardcoded in views.

---

## Seeded Plan Tiers

### 1. Developer Sandbox (`tier = free`)
- **Price:** $0.00 / month
- **Max Workspaces:** 1
- **Max Users:** 3
- **Included Features:** Daily Activity Board, Basic Audit Logs.

### 2. Team Operations (`tier = team`)
- **Price:** $49.00 / month
- **Max Workspaces:** 5
- **Max Users:** 15
- **Included Features:** Daily Activity Board, Basic Audit Logs, Export Reports, Incident War Rooms, Dual-Signoff Shift Handovers.

### 3. Enterprise Sovereign (`tier = enterprise`)
- **Price:** $299.00 / month
- **Max Workspaces:** Unlimited
- **Max Users:** Unlimited
- **Included Features:** All Team features plus Sovereign Data Residency, White-Label Customization, Custom SLA commitments, and Dedicated Cloud VPC deployment options.

## Administrative Operations

Authorized administrators (`platform.plans.manage`) can:
- Provision new commercial tiers (`POST /admin/platform/plans`).
- Adjust pricing, trial duration, or feature JSON payloads (`PUT /admin/platform/plans/{id}`).
- Deactivate obsolete plans while grandfathering existing tenant subscriptions.
