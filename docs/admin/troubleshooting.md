# Platform Administrator Troubleshooting & Runbooks

> **Audience:** Site Reliability Engineers, Platform Operations Lead

## Common Issues & Resolutions

### 1. HTTP 403 Forbidden When Accessing `/admin`
- **Root Cause**: Authenticated user lacks an assigned `platform_role`.
- **Diagnostic Command**:
  ```bash
  php artisan tinker --execute="echo App\Models\User::where('email', 'your-email@domain.com')->value('platform_role');"
  ```
- **Resolution**: Assign a valid platform role:
  ```bash
  php artisan tinker --execute="App\Models\User::where('email', 'your-email@domain.com')->first()->update(['platform_role' => App\Enums\PlatformRole::SuperAdmin->value]);"
  ```

### 2. User Accounts Not Able to Log In (Administrative Suspension)
- **Root Cause**: Account was flagged and suspended by a Security Administrator (`suspended_at` is set).
- **Diagnostic Command**:
  ```bash
  php artisan tinker --execute="echo App\Models\User::where('email', 'user@domain.com')->value('suspended_at');"
  ```
- **Resolution**: Reactivate via UI (`POST /admin/platform/users/{id}/reactivate`) or run:
  ```bash
  php artisan tinker --execute="App\Models\User::where('email', 'user@domain.com')->first()->update(['suspended_at' => null]);"
  ```

### 3. Missing Default Plans or Feature Flags
- **Resolution**: Re-run the platform seeder idempotently:
  ```bash
  php artisan db:seed --class=PlatformControlPlaneSeeder
  ```

### 4. Rebuilding Cache After Configuration Update
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```
