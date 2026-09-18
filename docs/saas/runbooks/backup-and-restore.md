# Opsora SaaS — Backup and Disaster Recovery Runbook

## Overview
This runbook defines database backup frequency, cold storage procedures, and step-by-step restoration protocols for multi-tenant and dedicated customer deployments.

---

## 1. Backup Topology & Frequency

| Tier | Backup Frequency | Retention Policy | Storage Destination |
|---|---|---|---|
| **Shared Multi-Tenant SaaS** | Automated every 6 hours + Continuous WAL | 30 days rolling + 12 monthly archives | Encrypted AWS S3 bucket (versioned + MFA delete) |
| **Dedicated Managed VPC** | Automated every 4 hours | 90 days rolling + annual archives | Customer-nominated regional S3/GCS bucket |
| **Customer-Hosted** | Local cron / dump script | Managed per customer SLA | Customer internal storage |

---

## 2. On-Demand Pre-Migration Database Backup

Always capture a manual snapshot before schema changes or data migrations:
```bash
# MySQL dump with single-transaction and quick flags
mysqldump -u root -p \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  npontu_tracker > "storage/backups/npontu_tracker_pre_migration_$(date +%Y%m%d_%H%M%S).sql"

# Compress backup archive
gzip "storage/backups/npontu_tracker_pre_migration_"*.sql
```

---

## 3. Database Restoration Protocol

### Step 1: Pre-Restoration Safeguards
1. Put application into maintenance mode: `php artisan down`.
2. Stop queue workers: `php artisan queue:restart`.
3. Create a point-in-time snapshot of the current corrupted state for forensic analysis.

### Step 2: Restore Database Dump
```bash
# Uncompress archive if needed
gunzip -c storage/backups/backup_archive.sql.gz | mysql -u root -p npontu_tracker
```

### Step 3: Run Data Reconciliation
After restoration, verify tenant integrity:
```bash
# Preview unmapped or orphaned records
php artisan opsora:migrate-legacy --dry-run
```

### Step 4: Validate Active Workspace Isolation
Run cross-tenant isolation test suite:
```bash
php artisan test tests/Feature/TenantIsolationTest.php
```

### Step 5: Resume Operations
```bash
php artisan config:cache
php artisan queue:restart
php artisan up
```
