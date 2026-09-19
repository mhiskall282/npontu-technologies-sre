# Enterprise Architecture & Topologies

> **Module:** `/admin/platform/settings` & `/admin/platform/organizations`

## Overview

Opsora supports three distinct multi-tenant deployment topologies to accommodate diverse regulatory, sovereignty, and data governance requirements:

---

## Supported Deployment Topologies

### 1. Multi-Tenant Shared SaaS (`shared_saas`)
- Default multi-tenant model.
- Tenants share a common database cluster with logical isolation enforced by `TenantScope` and foreign key constraints (`organization_id`, `workspace_id`).
- Cost-effective, automated patching, zero infrastructure overhead for customers.

### 2. Dedicated Cloud VPC (`dedicated_managed`)
- Fully managed by Opsora, but isolated within dedicated cloud VPCs and tenant-specific database instances.
- Backed by custom SLAs (up to 99.99%) and customer-provided KMS encryption keys.

### 3. Customer-Hosted & Sovereign Hybrid (`customer_hosted`)
- The control plane coordinates with worker nodes deployed inside the customer's on-premises Kubernetes or private cloud environments.
- Satisfies strict sovereign banking and telecommunications data localization regulations.
