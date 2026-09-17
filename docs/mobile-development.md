# Mobile Engineering & Development Guide

This guide details the local setup, architectural conventions, state management, and developer workflows for building and testing the **Npontu Technologies SRE Mobile App** (`npontu_sre_mobile`).

---

## 1. Local Environment Setup

### 1.1 Prerequisites

| Tool | Minimum Version | Notes |
|---|---|---|
| Flutter SDK | 3.24+ stable | `flutter upgrade` to update |
| Dart SDK | 3.5+ | Bundled with Flutter |
| Java JDK | 17 (Temurin) | Set `JAVA_HOME` |
| Android SDK | API 35 (compile), API 21 (min) | Via Android Studio |
| Xcode (macOS only) | 15.4+ | iOS/iPadOS builds |
| CocoaPods (macOS only) | Latest | `sudo gem install cocoapods` |

```bash
flutter doctor -v
```

### 1.2 Install Dependencies

```bash
cd npontu_sre_mobile
flutter pub get
```

---

## 2. Running the Application

### 2.1 Connecting to the Backend

| Target | API Base URL |
|---|---|
| Android Emulator | `http://10.0.2.2:8000/api/v1` |
| Windows Desktop (PC) | `http://127.0.0.1:8000/api/v1` |
| iOS Simulator | `http://localhost:8000/api/v1` |
| Physical Device | `http://<YOUR_LAN_IP>:8000/api/v1` |
| Production | `https://npontu-support-tracker.onrender.com/api/v1` |

```bash
# Windows Native Desktop (Fastest local testing on PC — no emulator needed)
flutter run -d windows \
  --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v1

# Android Emulator
flutter run -d emulator-5554 \
  --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1

# iOS Simulator
flutter run -d "iPhone 15 Pro" \
  --dart-define=API_BASE_URL=http://localhost:8000/api/v1
```

### 2.2 Test Accounts (seeded by DatabaseSeeder)

| Role | Email | Password | Grade |
|---|---|---|---|
| Admin | `admin@npontu.com` | `password` | L4 Principal |
| Shift Lead | `lead@npontu.com` | `password` | L3 Senior |
| SRE Engineer | `engineer@npontu.com` | `password` | L2 Engineer |
| SRE Agent | `agent@npontu.com` | `password` | L1 Support |

---

### 2.3 Android Studio & Emulator Setup for Testing

To run and test the mobile application locally in an Android Virtual Device (AVD):

1. **Install Android Studio**:
   - Download and install [Android Studio](https://developer.android.com/studio) (Giraffe or newer).
   - In Android Studio Setup Wizard, ensure **Android SDK Platform**, **Android SDK Command-line Tools**, and **Android Emulator** are installed.

2. **Create an Android Virtual Device (AVD)**:
   - Open Android Studio &rarr; Tools &rarr; **Device Manager** &rarr; **Create Device**.
   - Select **Pixel 7** or **Pixel 8** (standard 1080x2400 screen resolution).
   - System Image: Select **API 34 (UpsideDownCake)** or **API 35** with **Google APIs (x86_64)**.
   - Click **Finish** to create the emulator.

3. **Start the Emulator**:
   ```bash
   # List installed AVDs
   emulator -list-avds

   # Launch your AVD (example: Pixel_7_API_34)
   emulator -avd Pixel_7_API_34 -netdelay none -netspeed full
   ```

4. **Verify ADB Connection**:
   ```bash
   adb devices
   # Output: emulator-5554   device
   ```

5. **Install and Test Universal Release APK**:
   ```bash
   # Build Universal APK (v1.1.0+2)
   cd npontu_sre_mobile
   flutter build apk --release --dart-define=API_BASE_URL=https://npontu-support-tracker.onrender.com/api/v1

   # Install directly onto the running emulator
   adb install -r build/app/outputs/flutter-apk/app-release.apk
   ```

6. **Hot Reload / Debug Testing**:
   ```bash
   flutter run -d emulator-5554 --dart-define=API_BASE_URL=https://npontu-support-tracker.onrender.com/api/v1
   ```

## 3. Architecture & Code Structure

The project follows **Feature-First Clean Architecture** with Riverpod for state management.

```
npontu_sre_mobile/
├── android/
│   └── app/
│       ├── build.gradle.kts     # ABI splits, R8 minification, resource shrinking
│       └── proguard-rules.pro   # Plugin-safe ProGuard rules
├── ios/
│   └── Runner/Info.plist        # Permission usage descriptions + iPad orientations
├── lib/
│   ├── main.dart                # Entrypoint — NotificationService init, ProviderScope
│   ├── app.dart                 # MaterialApp.router + reactive theme from SettingsController
│   │
│   ├── core/
│   │   ├── config/              # AppConfig with env-injected API_BASE_URL
│   │   ├── constants/           # AppConstants (version, buildNumber, routes, keys)
│   │   ├── errors/              # ApiException hierarchy (401, 403, 404, 422, 5xx)
│   │   ├── network/             # Dio ApiClient with Sanctum Bearer interceptor
│   │   ├── routing/             # GoRouter with auth redirect guards
│   │   ├── services/
│   │   │   ├── notification_service.dart  # OS push + 30s API polling
│   │   │   └── permission_service.dart    # Runtime permission gating with rationale dialogs
│   │   ├── storage/             # SecureStorageService (Keychain/Keystore)
│   │   ├── theme/               # NpontuTheme (light + dark) + NpontuColors brand tokens
│   │   └── utils/
│   │       └── responsive.dart  # Breakpoints: phone < 600dp, tablet 600-1024dp
│   │
│   ├── features/
│   │   ├── auth/                # Login, Sanctum token lifecycle, UserModel
│   │   ├── dashboard/           # Operations Cockpit — adaptive phone/tablet layout
│   │   ├── activities/          # Shift checklist, P1-P4 priorities, check-off
│   │   ├── handovers/           # 2-way shift briefings and sign-offs
│   │   ├── messaging/           # Ops chat channels, war rooms, direct messages
│   │   ├── health/              # SRE diagnostics, DB/Redis health probes
│   │   ├── notifications/       # Notification centre — badge, date groups, swipe dismiss
│   │   ├── reports/             # Compliance metrics, date-range queries
│   │   ├── settings/            # Theme, notification prefs, biometric, permissions
│   │   ├── team/                # Operator directory, role and grade filters
│   │   └── audit/               # Immutable audit log with before/after JSON diffs
│   │
│   └── shared/
│       ├── models/              # Strongly-typed domain models (UserModel, ActivityModel…)
│       └── widgets/             # AppDrawer (with notification badge), StatusBadge, etc.
│
└── test/
    ├── features/notifications/  # Unit tests: model parsing, badge count provider
    ├── models_test.dart
    └── live_render_integration_test.dart
```

---

## 4. Engineering Disciplines & Best Practices

### State Management
- Use **Riverpod** (`AsyncNotifierProvider`, `NotifierProvider`, `StateNotifierProvider`).
- Controllers handle HTTP and business logic; screens are purely declarative.
- Use optimistic updates in AsyncNotifiers with automatic revert on API error.

### Network
- All requests pass through `ApiClient` which attaches the Bearer token.
- Idempotent GETs may be retried; mutating requests require user confirmation on timeout.
- API error mapping: 401 → redirect to login; 422 → inline field errors; 5xx → snackbar.

### Notifications
- `NotificationService` is initialised in `main()` before `runApp()`.
- The 30-second polling loop runs inside the app's Riverpod scope.
- `notificationBadgeCountProvider` drives all badge UI (AppBar + AppDrawer).

### Permissions
- **Never** call `Permission.request()` directly in widgets. Use `PermissionService`.
- Always show a rationale before the OS dialog.
- Permanently-denied permissions open App Settings via `openAppSettings()`.

### Responsive Design
- Use `Responsive.isTabletOrDesktop(context)` (≥ 600 dp) to switch layouts.
- Dashboard: two-column `Row` on tablets, single `ListView` on phones.
- AppDrawer uses `NavigationRail` on tablets ≥ 600 dp (via `AdaptiveScaffold`).

### Brand Consistency
- Never use arbitrary hex colours. Always reference `NpontuColors.*`.
- Primary: `NpontuColors.green` (`#1B6B3A`)
- Accent: `NpontuColors.gold` (`#F5C518`)
- Alert: `NpontuColors.danger` (`#E63946`)

### Security
- Tokens: `SecureStorageService` only. Never `SharedPreferences`.
- Do not log passwords or authorization tokens.
- All screens that require auth are guarded by the GoRouter `redirect`.

---

## 5. Building Release Artifacts

### Option A: Split APKs (direct sideload / internal distribution)

```bash
flutter build apk \
  --release \
  --split-per-abi \
  --obfuscate \
  --split-debug-info=build/debug-info \
  --dart-define=API_BASE_URL=https://npontu-support-tracker.onrender.com/api/v1
```

Output (`build/app/outputs/flutter-apk/`):

| File | ABI | Size |
|---|---|---|
| `app-arm64-v8a-release.apk` | Modern Android (2016+) | ~35–50 MB |
| `app-armeabi-v7a-release.apk` | Older 32-bit devices | ~30–45 MB |
| `app-x86_64-release.apk` | Emulators | ~40–55 MB |

> **Fat APK comparison**: Without `--split-per-abi`, a single fat APK containing all ABIs is ~150–160 MB. Splitting is the single biggest size reduction.

### Option B: App Bundle (Google Play — recommended for production)

```bash
flutter build appbundle \
  --release \
  --obfuscate \
  --split-debug-info=build/debug-info \
  --dart-define=API_BASE_URL=https://npontu-support-tracker.onrender.com/api/v1
```

Play Store automatically splits the bundle by ABI, density, and language. Users download only what their device needs (~15–25 MB).

### APK Size Optimisations Applied

| Technique | Where | Approximate Saving |
|---|---|---|
| `--split-per-abi` | Build command | ~60% (removes bundled unused ABIs) |
| `isMinifyEnabled = true` (R8) | `build.gradle.kts` | ~20–30% DEX shrink |
| `isShrinkResources = true` | `build.gradle.kts` | ~5–10% resource strip |
| `--obfuscate` | Build command | ~3–5% symbol compression |
| `useLegacyPackaging = false` | `build.gradle.kts` | Efficient .so extraction |

---

## 6. Testing & Quality Gates

Run all checks before submitting changes:

```bash
# 1. Format (zero-diff check)
dart format --output=none --set-exit-if-changed .

# 2. Static analysis (zero errors, zero warnings)
flutter analyze

# 3. Full test suite
flutter test
```

All three gates are enforced by the CI workflow at `.github/workflows/flutter-ci.yml`.

---

## 7. CI/CD Pipeline & Version Control

The Flutter CI workflow (`.github/workflows/flutter-ci.yml`) runs automatically on every push or pull request to `main` that touches `npontu_sre_mobile/`, and can also be triggered manually via `workflow_dispatch`:

1. **Format check** — `dart format --set-exit-if-changed`
2. **Static analysis** — `flutter analyze`
3. **Unit & Widget tests** — `flutter test --coverage`
4. **Debug APK** — `--split-per-abi --target-platform android-arm64` (fast CI feedback)
5. **Universal Release APK** — Full fat APK `app-release.apk` running on all CPU architectures & emulators → artifact `npontu-sre-universal-release-apk`
6. **Release split APKs** — `--split-per-abi --obfuscate` → artifact `npontu-sre-android-split-apks`
7. **Release AAB** — `--obfuscate` → artifact `npontu-sre-google-play-aab`
8. **Debug symbols** — Uploaded separately for crash deobfuscation

### Downloading the Correct APK from GitHub:
- **For general testing on any phone, tablet, or Android emulator (x86_64 / ARM)**: Download `npontu-sre-universal-release-apk` (`app-release.apk`). This contains all native binaries and will never fail with architecture mismatch errors.
- **For production deployments with minimum bandwidth**: Download the split APK matching your device's exact architecture (e.g., `app-arm64-v8a-release.apk` for modern phones).

---

## 8. App Permissions Reference

### Android (`android/app/src/main/AndroidManifest.xml`)

| Permission | Reason |
|---|---|
| `INTERNET` | API communication |
| `ACCESS_NETWORK_STATE` | Connectivity checks |
| `POST_NOTIFICATIONS` | Push alerts (Android 13+) |
| `ACCESS_FINE_LOCATION` | On-site check-in tagging |
| `ACCESS_COARSE_LOCATION` | Fallback location |
| `CAMERA` | QR scanning, incident photos |
| `READ_MEDIA_IMAGES` | Photo attachments (Android 13+) |
| `USE_BIOMETRIC` | Secure sign-in |
| `USE_FINGERPRINT` | Legacy fingerprint (< Android 9) |
| `VIBRATE` | Critical alert vibration |
| `SCHEDULE_EXACT_ALARM` | Precise notification delivery |
| `RECEIVE_BOOT_COMPLETED` | Re-register notification channels on reboot |

### iOS (`ios/Runner/Info.plist`)

| Key | Reason |
|---|---|
| `NSCameraUsageDescription` | Equipment QR / incident photos |
| `NSPhotoLibraryUsageDescription` | Photo attachments |
| `NSLocationWhenInUseUsageDescription` | Check-in geolocation |
| `NSLocationAlwaysAndWhenInUseUsageDescription` | Background shift geofencing |
| `NSFaceIDUsageDescription` | Biometric authentication |
