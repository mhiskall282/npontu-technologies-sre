# Opsora SaaS Transformation — Deployment & Infrastructure Architecture

> **Status**: Approved Infrastructure Specification (Stage 6)  
> **Deployment Models Supported**: Shared Multi-Tenant SaaS, Dedicated Managed Pods, Customer-Funded Managed, and Customer-Hosted

---

## 1. Supported Deployment Models

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      OPSORA PLATFORM CONTROL PLANE                      │
│ - Identity, Account Management & Global Authentication                  │
│ - Organization Registry, Application Reviews & Approval Engine          │
│ - Subscription, Entitlement & License Key Verification                  │
│ - Centralized Deployment Registry & Health Orchestration                │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
           ┌─────────────────────────┼─────────────────────────┐
           ▼                         ▼                         ▼
┌───────────────────────┐ ┌───────────────────────┐ ┌───────────────────────┐
│ 1. SHARED SAAS PODS   │ │ 2. DEDICATED MANAGED  │ │ 3. CUSTOMER HOSTED    │
│ - Multi-tenant DB     │ │ - Single-tenant DB    │ │ - On-Prem / VPC       │
│ - Shared app workers  │ │ - Dedicated worker    │ │ - Outbound Sync Agent │
│ - Instant self-serve  │ │ - Managed by Opsora   │ │ - Offline License Key │
└───────────────────────┘ └───────────────────────┘ └───────────────────────┘
```

### 1.1 Shared Multi-Tenant SaaS (Default)
- **Target**: Freelancers, small teams, startups, and evaluation pilots.
- **Compute**: Shared autoscaling container pool on Render / AWS ECS.
- **Database**: High-availability MySQL cluster with logical `workspace_id` tenant partitioning.
- **Onboarding**: Instant automated provisioning upon approval.

### 1.2 Dedicated Managed Pods (Enterprise Managed)
- **Target**: FinTechs, banks, healthcare, and enterprise SRE organizations with regulatory isolation mandates.
- **Compute**: Dedicated, isolated container cluster provisioned in customer's preferred cloud region.
- **Database**: Dedicated single-tenant database instance (MySQL or PostgreSQL).
- **Maintenance**: Fully managed, patched, backed up, and upgraded by the Opsora platform engineering team.

### 1.3 Customer-Funded Managed Deployments
- **Target**: Large institutions requiring infrastructure to run in their own AWS / Azure / GCP accounts, but operated and monitored by Opsora via cross-account IAM roles or Terraform Cloud.

### 1.4 Customer-Hosted Deployments (Air-Gapped / Private VPC)
- **Target**: National defense, intelligence, or strict on-premise infrastructure teams.
- **Delivery**: Signed OCI container images with cryptographic one-time or annual `LicenseKey` offline verification.
- **Telemetry**: Optional unidirectional outbound heartbeat to Opsora Control Plane (no inbound ports required).

---

## 2. Centralized Deployment Registry Schema

The Control Plane tracks all active execution planes via the `deployments` entity:

- `id`: BigInt (PK)
- `uuid`: UUID
- `organization_id`: FK -> `organizations.id`
- `model`: Enum (`shared_saas`, `dedicated_managed`, `customer_funded`, `customer_hosted`)
- `status`: Enum (`requested`, `under_review`, `provisioning`, `active`, `maintenance`, `suspended`, `decommissioned`)
- `hosting_provider`: String (`render`, `aws`, `azure`, `gcp`, `on_premise`)
- `region_code`: String (`us-east-1`, `eu-west-1`, `af-south-1`)
- `app_version`: String (Semantic version, e.g. `v2.4.0`)
- `endpoint_url`: String (Base URL of execution environment)
- `database_host`: String (Encrypted reference)
- `license_id`: FK -> `license_keys.id` (Nullable)
- `last_heartbeat_at`: Timestamp (Health telemetry snapshot)
- `timestamps`
