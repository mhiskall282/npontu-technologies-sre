# Opsora Troubleshooting & Diagnostic Runbook

> **Status:** IMPLEMENTED  
> **Last Verified:** 2026-09-18

This guide details common operational issues, diagnostics, and proven remediations.

---

## 1. Known Diagnostic Scenarios

### Scenario 1: Cross-Tenant Access 403 Forbidden
- **Symptom**: User receives HTTP 403 when querying activities or switching workspace.
- **Cause**: The user account does not have an active record in `workspace_memberships` for the requested workspace, or the workspace is marked `suspended`.
- **Diagnosis**:
  ```bash
  php artisan tinker --execute="\App\Models\WorkspaceMembership::where('user_id', 3)->where('workspace_id', 2)->first();"
  ```
- **Remediation**: Use `POST /api/v1/workspaces/join` with valid company code or invite the user.

### Scenario 2: Mobile Emulator Connection Timeout
- **Symptom**: Mobile app fails to load data when running on Android Studio emulator.
- **Cause**: App is requesting `http://localhost:8000` instead of Android loopback `http://10.0.2.2:8000`.
- **Remediation**: Launch the emulator passing `--dart-define=BASE_URL=http://10.0.2.2:8000/api/v1`.

### Scenario 3: Missing Workspace on Legacy Records
- **Symptom**: Historical operational records created before the SaaS migration have `workspace_id = NULL`.
- **Diagnosis**:
  ```bash
  php artisan opsora:migrate-legacy --dry-run
  ```
- **Remediation**: Run active migration:
  ```bash
  php artisan opsora:migrate-legacy --target-workspace=1
  ```
