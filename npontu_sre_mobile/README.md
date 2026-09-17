# Npontu SRE Mobile — Operations Cockpit

A production-grade Flutter application for Npontu Technologies SRE engineers. Provides a real-time operational cockpit covering shift checklists, incident escalations, shift handovers, operational messaging, system health diagnostics, compliance reports, and team management.

---

## Quick Start

### Prerequisites

| Tool | Version |
|---|---|
| Flutter SDK | 3.24+ (stable channel) |
| Dart SDK | 3.5+ |
| Java JDK | 17 (Temurin recommended) |
| Android SDK | API 35 (compile), API 21 (min) |
| Xcode (macOS) | 15.4+ |

```bash
# Verify your environment
flutter doctor -v
```

### Install Dependencies

```bash
cd npontu_sre_mobile
flutter pub get
```

### Run the App

```bash
# Android emulator (connects to local backend at 10.0.2.2)
flutter run -d emulator-5554 \
  --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1

# iOS simulator (macOS only)
flutter run -d "iPhone 15 Pro" \
  --dart-define=API_BASE_URL=http://localhost:8000/api/v1

# Physical device (use your LAN IP)
flutter run --dart-define=API_BASE_URL=http://192.168.1.x:8000/api/v1

# Production backend
flutter run --dart-define=API_BASE_URL=https://npontu-support-tracker.onrender.com/api/v1
```

---

## Building Release Artifacts

### Split APKs (recommended for direct install — ~35–55 MB per ABI)

```bash
flutter build apk \
  --release \
  --split-per-abi \
  --obfuscate \
  --split-debug-info=build/debug-info \
  --dart-define=API_BASE_URL=https://npontu-support-tracker.onrender.com/api/v1
```

Output: `build/app/outputs/flutter-apk/`
- `app-arm64-v8a-release.apk` — Modern phones (primary)
- `app-armeabi-v7a-release.apk` — Older 32-bit phones
- `app-x86_64-release.apk` — Emulators

> **Why split?** The default fat APK bundles all ABI native libraries into one file, resulting in ~150–160 MB. Splitting produces one APK per device architecture at ~35–55 MB each.

### App Bundle (Google Play — ~15–25 MB download)

```bash
flutter build appbundle \
  --release \
  --obfuscate \
  --split-debug-info=build/debug-info \
  --dart-define=API_BASE_URL=https://npontu-support-tracker.onrender.com/api/v1
```

Output: `build/app/outputs/bundle/release/app-release.aab`

---

## APK Size Optimisations

The following techniques are applied to keep the APK small:

| Technique | File | Impact |
|---|---|---|
| `--split-per-abi` | CI / build command | ~60% size reduction (removes unused ABIs) |
| `isMinifyEnabled = true` | `build.gradle.kts` | ~25% DEX reduction via R8 |
| `isShrinkResources = true` | `build.gradle.kts` | Strips unused XML/image resources |
| `--obfuscate` | Build command | Shortens symbol names |
| `useLegacyPackaging = false` | `build.gradle.kts` | Extracts .so files at install time |
| ProGuard rules | `proguard-rules.pro` | Protects plugin classes from over-stripping |

---

## Project Structure

```
npontu_sre_mobile/
├── android/
│   └── app/
│       ├── build.gradle.kts     # ABI splits, R8, resource shrinking
│       └── proguard-rules.pro   # Plugin-specific ProGuard rules
├── ios/
│   └── Runner/Info.plist        # Camera, location, Face ID usage descriptions
├── lib/
│   ├── main.dart                # Entrypoint — NotificationService init + ProviderScope
│   ├── app.dart                 # MaterialApp.router with dynamic theme
│   ├── core/
│   │   ├── config/              # AppConfig (env-injected API base URL)
│   │   ├── constants/           # AppConstants (version, routes, keys)
│   │   ├── errors/              # ApiException hierarchy
│   │   ├── network/             # Dio ApiClient with Sanctum interceptor
│   │   ├── routing/             # GoRouter with auth guards
│   │   ├── services/            # NotificationService, PermissionService
│   │   ├── storage/             # SecureStorageService (Keychain / Keystore)
│   │   ├── theme/               # NpontuTheme + NpontuColors
│   │   └── utils/               # Responsive breakpoints
│   ├── features/
│   │   ├── auth/                # Login, token lifecycle, user context
│   │   ├── dashboard/           # Operations Cockpit (adaptive: phone/tablet)
│   │   ├── activities/          # Shift checklist, check-off, priority tiers
│   │   ├── handovers/           # 2-way shift sign-off
│   │   ├── messaging/           # Operational chat, war rooms, DMs
│   │   ├── health/              # SRE diagnostics, DB/Redis health probes
│   │   ├── notifications/       # Notification centre (badge, swipe, mark read)
│   │   ├── reports/             # Compliance metrics, date-range export
│   │   ├── settings/            # Theme, notification prefs, biometric, permissions
│   │   ├── team/                # Operator directory, grades, roles
│   │   └── audit/               # Immutable audit log with JSON diffs
│   └── shared/
│       ├── models/              # Strongly-typed domain models
│       └── widgets/             # AppDrawer, StatusBadge, PriorityBadge, etc.
└── test/
    ├── features/
    │   └── notifications/       # NotificationModel + badge count tests
    ├── models_test.dart
    └── live_render_integration_test.dart
```

---

## App Permissions

### Android
| Permission | Purpose |
|---|---|
| `INTERNET` | API communication |
| `POST_NOTIFICATIONS` | Incident + handover push alerts (Android 13+) |
| `ACCESS_FINE_LOCATION` | On-site engineer check-in |
| `CAMERA` | QR scanning, incident photo evidence |
| `USE_BIOMETRIC` | Secure sign-in |
| `VIBRATE`, `SCHEDULE_EXACT_ALARM` | Critical alert delivery |

### iOS (Info.plist)
| Key | Purpose |
|---|---|
| `NSCameraUsageDescription` | Equipment QR scanning |
| `NSLocationWhenInUseUsageDescription` | On-call check-in validation |
| `NSLocationAlwaysAndWhenInUseUsageDescription` | Background shift geofencing |
| `NSFaceIDUsageDescription` | Biometric authentication |
| `NSPhotoLibraryUsageDescription` | Incident photo attachments |

---

## Test Accounts (seeded)

| Role | Email | Password |
|---|---|---|
| Admin | `admin@npontu.com` | `password` |
| Shift Lead | `lead@npontu.com` | `password` |
| SRE Engineer | `engineer@npontu.com` | `password` |
| SRE Agent | `agent@npontu.com` | `password` |

---

## Quality Gates

Run these before every commit:

```bash
# 1. Format
dart format --output=none --set-exit-if-changed .

# 2. Static analysis (zero errors required)
flutter analyze

# 3. Test suite
flutter test
```

All three gates are enforced by the GitHub Actions CI workflow.
