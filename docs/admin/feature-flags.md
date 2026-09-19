# Feature Flags & Targeted Rollouts

> **Module:** `/admin/platform/features`  
> **Model:** `App\Models\FeatureFlag` (`feature_flags` table)  
> **Action:** `ToggleFeatureFlagAction`

## Overview

The Feature Flags engine provides dynamic capability toggles across the platform without requiring code redeployments. Flags support global activation, plan tier targeting, and tenant organization targeting.

---

## Schema & Targeting Rules

- **`key`**: Unique machine identifier (e.g., `sre_dual_handover_enforcement`, `white_label_branding`).
- **`is_enabled`**: Master boolean switch.
- **`target_tiers`**: JSON array of commercial tiers permitted to consume the feature (`['enterprise']`, `['team', 'enterprise']`).
- **`target_org_ids`**: JSON array of specific tenant organization IDs for closed beta testing.

## Domain Evaluation API

```php
// Check if flag is enabled for an organization
FeatureFlag::isEnabledFor('sovereign_data_residency', $organization);
```

When an administrator toggles a feature flag (`PATCH /admin/platform/features/{id}/toggle`), `ToggleFeatureFlagAction` executes the state update and writes an immutable `AuditLog` entry documenting the change.
