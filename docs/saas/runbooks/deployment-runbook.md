# Opsora SaaS — Production Deployment Runbook

## Overview
This runbook defines standard operating procedures for rolling out updates to the Opsora Multi-Tenant SRE SaaS platform across web, backend, and worker services.

---

## 1. Pre-Deployment Verification Checklist

1. **Branch & Commit Integrity**:
   - Verify changes are committed to the release tracking branch (`feat/opsora-saas` or `main`).
   - Run `git log -n 5` to confirm expected conventional commit history.

2. **Automated Test Gate**:
   - Backend Pest tests: `php artisan test` (must pass 100%, 0 failures).
   - Code style check: `vendor/bin/pint --test`.
   - Flutter tests: `flutter test` in `npontu_sre_mobile` (must pass 100%).

3. **Database Migration Inspection**:
   - Inspect pending migrations: `php artisan migrate:status`.
   - Verify every new migration has a functional `down()` rollback method.

4. **Environment Variables Check**:
   - Verify `.env.example` contains all new variables (e.g. `OPSORA_BRAND_NAME`, `OPSORA_PRIMARY_WORKSPACE_UUID`).

---

## 2. Production Deployment Steps (Render / AWS / Managed Cloud)

### Step 1: Maintenance Notification (Optional for minor releases)
If performing zero-downtime rolling deploys, maintenance mode is not required. For database-altering schema migrations:
```bash
php artisan down --secret="opsora-sre-deploy-bypass-token" --render="errors.503"
```

### Step 2: Code Pull & Dependency Installation
```bash
git pull origin main
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build
```

### Step 3: Run Multi-Tenant Database Migrations
```bash
php artisan migrate --force
```

### Step 4: Run Post-Migration Legacy Data Reconciliation (if applicable)
```bash
php artisan opsora:migrate-legacy --batch-size=500
```

### Step 5: Cache Configuration, Routes, and Views
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Step 6: Restart Background Workers & Queue Listeners
```bash
php artisan queue:restart
```

### Step 7: Post-Deployment Smoke Probes
Execute automated system health verification:
```bash
curl -f -s -o /dev/null -w "%{http_code}" https://opsora.production/api/health
```
Expect HTTP `200 OK` with JSON telemetry payload.

### Step 8: Resume Normal Traffic
```bash
php artisan up
```
