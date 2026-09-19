# Security Center & SIEM Telemetry

> **Module:** `/admin/platform/security`  
> **Model:** `App\Models\SecurityEvent` (`security_events` table)

## Overview

The Platform Security Center provides SIEM (Security Information and Event Management) telemetry, tracking privilege escalations, authentication failures, suspicious unauthorized attempts, and administrative actions.

---

## Event Classification & Severities

The `SecurityEvent` model records structured events with three standardized severity levels:

| Severity | Color Code | Trigger Scenarios |
|---|---|---|
| **`info`** | Gray | Successful registration, user logins, token generation |
| **`warning`** | Amber | Failed logins, platform role changes, administrative token revocations, unhandled cross-tenant access attempts |
| **`critical`** | Red | Unauthorized access to `/admin`, privilege escalation attempts, brute-force lockout events |

## SIEM Ingestion API

Controllers, middlewares, and actions emit events using the factory method:

```php
SecurityEvent::record(
    eventType: 'privilege_escalation_attempt',
    severity: 'critical',
    actor: $request->user(),
    target: $targetResource,
    details: [
        'required_permission' => $permission,
        'attempted_route' => $request->path(),
    ],
    ipAddress: $request->ip()
);
```

Security events are presented in a searchable, real-time log table on `/admin/platform/security` and accessible via the platform REST API at `GET /api/v1/platform/security-events`.
