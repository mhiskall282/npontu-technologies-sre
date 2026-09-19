# Commercial Subscriptions & Billing Ledger

> **Module:** `/admin/platform/subscriptions`  
> **Route:** `/admin/platform/subscriptions` & `/admin/platform/subscriptions/{id}/status`

## Overview

The Subscriptions module manages commercial tenant relationships, recurring billing periods, and entitlement activation states.

---

## Subscription Lifecycle

A tenant subscription passes through the following statuses:

1. **`trialing`**: Free evaluation period (default: 14 to 30 days depending on plan).
2. **`active`**: Commercial recurring subscription in good standing.
3. **`past_due`**: Payment failure recorded; grace period active.
4. **`paused`**: Administratively held by Billing Administrator.
5. **`canceled`**: Subscription terminated; tenant access throttled to Developer Sandbox limits.

## Billing State Management

Administrators with `platform.billing.manage` can update subscription status directly from the control plane:
- Changes are executed via `App\Actions\Platform\UpdateSubscriptionAction`.
- The action snapshots the prior status, saves the update, and logs a compliance `AuditLog` entry.
- When an administrator updates a subscription to `canceled`, the tenant's entitlements immediately restrict feature access on both the Web UI and the Mobile REST API.
