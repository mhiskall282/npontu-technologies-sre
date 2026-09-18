# Opsora SaaS Transformation — Billing & Entitlement Domain Design

> **Status**: Approved Commercial Architecture (Stage 10)  
> **Rule**: Decouple Entitlements from Payment Gateways; Keep Payment Collection Dormant Until Commercial Sign-Off

---

## 1. Commercial Pricing Tiers

Opsora establishes four standard commercial subscription tiers alongside custom enterprise contracts and offline licenses:

| Tier | Target Persona | Operators Included | Retention Window | Core Capabilities | Deployment Model |
|---|---|---|---|---|---|
| **Free Developer** | Individual SREs & Freelancers | 1 Operator (Personal Workspace) | 14 Days | Basic Checklists, P1-P4 Tiers, Personal Log | Shared SaaS |
| **Starter Team** | Small Dev Teams & Startups | Up to 5 Operators | 60 Days | Shift Handovers, War Rooms, Email Alerts | Shared SaaS |
| **Professional SRE** | Scaling Tech Companies | Up to 25 Operators | 1 Year | Bulk Delegation, SLA Targets, CSV/PDF Exports | Shared SaaS |
| **Enterprise Sovereign** | FinTech, Banks, Large Corps | Unlimited Operators | 7 Years (Compliance) | Dedicated Pod / Self-Hosted, SIEM Audit Stream, Custom SLA, White-Label | Dedicated Managed or Customer-Hosted |

---

## 2. Entitlement Enforcement Architecture

Domain logic never checks payment provider APIs directly. It queries the `EntitlementService`:

```php
if (! EntitlementService::can($workspace, 'audit_export_enabled')) {
    throw new EntitlementLimitExceededException('Audit exports require a Professional or Enterprise plan.');
}
```

### Supported Entitlement Keys
- `max_operators`: Integer quota on active `WorkspaceMembership` count.
- `data_retention_days`: Integer window (14, 60, 365, or 2555 days).
- `shift_handovers_enabled`: Boolean flag.
- `war_rooms_enabled`: Boolean flag.
- `audit_export_enabled`: Boolean flag.
- `sla_escalation_rules`: Boolean flag.
- `custom_domain_enabled`: Boolean flag.
- `dedicated_deployment_allowed`: Boolean flag.

---

## 3. License Key Architecture (Offline & Self-Hosted)

For customer-hosted and air-gapped enterprise deployments, Opsora supports cryptographically signed license keys:

```
HEADER: {"alg": "RS256", "typ": "OPSORA-LICENSE"}
PAYLOAD: {
  "license_id": "LIC-8824-2026",
  "organization_uuid": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
  "tier": "enterprise",
  "operators": 100,
  "valid_from": "2026-01-01T00:00:00Z",
  "valid_until": "2027-01-01T00:00:00Z",
  "deployment_model": "customer_hosted",
  "signature": "..."
}
```
- Verified offline using Opsora's public RSA key.
- Grace period: 14 days warning banner before feature restriction on expired licenses.
