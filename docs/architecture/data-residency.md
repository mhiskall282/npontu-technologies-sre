# Opsora Data Residency & Regional Boundaries

> **Status:** IMPLEMENTED  
> **Last Verified:** 2026-09-18

Opsora enables organizations to nominate geographic data residency zones during registration, ensuring compliance with data sovereignty regulations (such as Ghana Data Protection Act 843, GDPR, and POPIA).

---

## 1. Supported Data Residency Zones

| Region Code | Display Name | Primary Datacenter | Regulatory Alignment |
|---|---|---|---|
| `af-south` | Africa Primary Gateway | Johannesburg / Accra Edge Node | Ghana Act 843, South Africa POPIA |
| `eu-west` | Europe West | Frankfurt / Dublin Cloud Region | EU GDPR, ISO 27001 |
| `us-east` | North America East | Virginia / Ohio Cloud Region | SOC 2 Type II, HIPAA compliant |

---

## 2. Enforcement & Storage Partitioning
1. **Database Allocation**: Dedicated and customer-funded deployments pin MySQL primary nodes and read-replicas within the nominated regional boundary.
2. **Object Storage Partitioning**: Attachments (images and PDFs in `conversations`) are stored in regional S3/GCS buckets matching the tenant's nominated zone.
3. **Backup Isolation**: Automated database snapshots are encrypted with AES-256 and replicated only within the nominated geographic zone.
