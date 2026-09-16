# Mobile & Multi-Client Deployment Guide

This guide describes the end-to-end deployment procedures for the Npontu Technologies SRE platform, covering both the shared Laravel backend and the native Android/iOS Flutter mobile applications.

---

## 1. Backend Production Deployment

### 1.1 Server Prerequisites
- **Operating System**: Ubuntu 22.04 LTS or 24.04 LTS
- **PHP**: PHP 8.2 or 8.4 with extensions: `php-fpm`, `php-mysql`, `php-redis`, `php-bcmath`, `php-xml`, `php-curl`, `php-mbstring`, `php-zip`, `php-intl`
- **Web Server**: Nginx with TLS 1.3 certificate (Let's Encrypt / Cloudflare)
- **Database**: MySQL 8.0+ with InnoDB storage engine
- **Cache / Queue**: Redis 7.x
- **Process Manager**: Supervisor for Laravel queue workers

### 1.2 Environment Configuration (`.env`)
```ini
APP_NAME="Npontu SRE"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://npontu-support-tracker.onrender.com

DB_CONNECTION=mysql 
DB_HOST=[IP_ADDRESS]
DB_PORT=3306
DB_DATABASE=npontu_sre_prod
DB_USERNAME=npontu_app
DB_PASSWORD="[PASSWORD]"

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

SANCTUM_STATEFUL_DOMAINS=npontu-support-tracker.onrender.com
CORS_ALLOWED_ORIGINS="https://npontu-support-tracker.onrender.com"
```

### 1.3 Deployment Script (`deploy.sh`)
```bash
#!/usr/bin/env bash
set -e

echo "Starting Npontu SRE Backend Deployment..."

# 1. Maintenance mode
php artisan down --retry=60

# 2. Pull latest release
git pull origin main

# 3. Install production PHP dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Run database migrations with rollback safety
php artisan migrate --force

# 5. Cache configurations, routes, and views
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Restart queue workers
php artisan queue:restart
sudo supervisorctl restart npontu-worker:*

# 7. Bring application back online
php artisan up

echo "Deployment completed successfully!"
```

### 1.4 Rollback Procedure
If an issue is detected post-deployment:
1. Revert to previous release tag: `git checkout <PREVIOUS_TAG>`
2. Rollback the latest migration batch: `php artisan migrate:rollback --step=1`
3. Clear and regenerate caches: `php artisan optimize:clear && php artisan optimize`
4. Restart queue workers: `sudo supervisorctl restart npontu-worker:*`

---

## 2. Flutter Android Deployment

### 2.1 Application Metadata
- **Package Name / Application ID**: `com.npontu.sre_mobile`
- **Display Name**: `Npontu SRE`
- **Minimum SDK**: 24 (Android 7.0+)
- **Target SDK**: 35 (Android 15)

### 2.2 Signing Key Generation & Keystore Management
Generate a production keystore (do not commit to Git):
```bash
keytool -genkey -v -keystore npontu-sre-release.jks \
  -keyalg RSA -keysize 2048 -validity 10000 \
  -alias npontu-sre
```
Create `android/key.properties` (added to `.gitignore`):
```properties
storePassword=<STORE_PASSWORD>
keyPassword=<KEY_PASSWORD>
keyAlias=npontu-sre
storeFile=/path/to/npontu-sre-release.jks
```

### 2.3 Building Android App Bundle (AAB)
Run the build command specifying the production API endpoint:
```bash
flutter build appbundle --release \
  --dart-define=ENVIRONMENT=production \
  --dart-define=API_URL=https://sre.npontu.com/api/v1
```
The output will be generated at:
`build/app/outputs/bundle/release/app-release.aab`

### 2.4 Google Play Console Release
1. Navigate to **Google Play Console** > **Npontu SRE** > **Testing** > **Internal testing**.
2. Create a new release and upload `app-release.aab`.
3. Add release notes detailing the SRE checklist and handover capabilities.
4. Invite internal SRE operators to test before promoting to Production.

---

## 3. Flutter iOS Deployment

### 3.1 Application Metadata
- **Bundle Identifier**: `com.npontu.sreMobile`
- **Display Name**: `Npontu SRE`
- **Minimum iOS Version**: 15.0
- **Capabilities**: Push Notifications, Background Fetch

### 3.2 Build Environment Requirements
- **Hardware**: macOS Sonoma with Apple Silicon or Intel
- **Tools**: Xcode 15.4+, CocoaPods, Apple Developer Account with Admin access

### 3.3 Building iOS Release & IPA
```bash
# 1. Install CocoaPods dependencies
cd ios && pod install && cd ..

# 2. Build release archive
flutter build ipa --release \
  --dart-define=ENVIRONMENT=production \
  --dart-define=API_URL=https://sre.npontu.com/api/v1 \
  --export-options-plist=ios/ExportOptions.plist
```

### 3.4 TestFlight Distribution
1. Upload the generated `.ipa` using `xcrun altool` or **Transporter**:
   ```bash
   xcrun altool --upload-app -f build/ios/ipa/npontu_sre_mobile.ipa -t ios -u "<APPLE_ID>" -p "<APP_SPECIFIC_PASSWORD>"
   ```
2. In **App Store Connect**, add the build to the internal SRE TestFlight group.
3. Verify device installations and biometric/secure token persistence.

---

## 4. Multi-Environment Configuration Strategy

The mobile application utilizes compile-time `--dart-define` environment flags mapped via `AppConfig`:

| Environment | Base API URL | Features Enabled |
|---|---|---|
| **Local Dev** | `http://10.0.2.2:8000/api/v1` (Android Emulator) / `http://localhost:8000/api/v1` (iOS Simulator) | Verbose HTTP logging, test accounts |
| **Staging** | `https://staging-sre.npontu.com/api/v1` | Redacted logging, full API suite |
| **Production** | `https://sre.npontu.com/api/v1` | Silent logging, strict certificate validation |
