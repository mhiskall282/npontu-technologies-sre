# Opsora SaaS — Production Rollback Runbook

## Overview
This runbook details immediate emergency rollback procedures in the event of deployment failure, database migration incompatibility, or severe service degradation.

---

## 1. Trigger Conditions
Initiate rollback immediately if:
- Automated health probes (`/api/health`) return non-200 responses or latency > 2000ms.
- High error rate (> 1% of total HTTP requests returning 500 status codes).
- Cross-tenant data isolation failure or critical authorization breach reported.
- Fatal runtime exceptions in background workers or WebSocket channels.

---

## 2. Fast Rollback Execution Sequence

### Phase 1: Enable Safe Maintenance Mode
Prevent users from triggering further mutations:
```bash
php artisan down --secret="opsora-emergency-fix-token" --render="errors.503"
```

### Phase 2: Roll Back Application Code
Revert to the last known stable Git commit hash:
```bash
git log -n 5 --oneline
# Identify stable SHA (e.g. abc1234)
git checkout <STABLE_COMMIT_HASH>
```

### Phase 3: Roll Back Database Migrations
If the failure is caused by a recent migration:
```bash
# Preview status
php artisan migrate:status

# Roll back the single most recent batch
php artisan migrate:rollback --step=1 --force
```

If tenant records were mapped incorrectly during legacy migration:
```bash
# Roll back workspace unmapping
php artisan opsora:migrate-legacy --rollback --target-workspace=1
```

### Phase 4: Re-install Dependencies & Rebuild Caches
```bash
composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

### Phase 5: Verification Smoke Tests
1. Test Health endpoint:
   ```bash
   curl -I https://opsora.production/api/health
   ```
2. Test Login flow and Dashboard load:
   ```bash
   php artisan test tests/Feature/AuthenticationTest.php
   ```

### Phase 6: Restore Traffic
```bash
php artisan up
```

### Phase 7: Post-Incident Review
1. Gather application error logs from `storage/logs/laravel.log`.
2. Inspect `audit_logs` table for relevant administrative actions.
3. Open an SRE incident report and schedule a blameless post-mortem.
