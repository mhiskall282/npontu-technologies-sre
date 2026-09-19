# Centralized Entitlement Architecture

> **Architecture Rule:** In accordance with prompt section #15, feature gating is centralized in the domain layer and enforced uniformly across Web, REST API, background workers, and Mobile clients.

## Entitlement Flow

```text
Plan Catalog (saas_plans.features)
        │
        ▼
Subscription (saas_subscriptions)
        │
        ▼
Tenant Organization (organizations)
        │
        ▼
Active Workspace (workspaces)
        │
        ▼
Unified Entitlement Evaluation
        │
        ├──► Web Blade / Livewire Directives
        ├──► REST API Middleware (Form Requests & Policies)
        ├──► Background Jobs & Scheduled Cron Tasks
        └──► Flutter Mobile Application (via /me and /workspaces payload)
```

---

## Evaluation Helper API

The `Plan` model exposes a typed entitlement resolution method:

```php
public function hasFeature(string $featureKey): bool
{
    return (bool) ($this->features[$featureKey] ?? false);
}
```

The active organization exposes:

```php
public function canAccessFeature(string $featureKey): bool
{
    $subscription = $this->subscription();
    if (! $subscription || ! $subscription->isActive()) {
        return false;
    }

    return $subscription->plan?->hasFeature($featureKey) ?? false;
}
```

This ensures that hiding buttons on the UI is never the sole access control mechanism. Every API endpoint inspects the active tenant's plan entitlements before granting privileged execution.
