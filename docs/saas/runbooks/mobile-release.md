# Opsora SaaS — Mobile Application Release Runbook

## Overview
This runbook covers the build, testing, signing, and distribution process for the Opsora Multi-Workspace Mobile Application (Flutter for Android & iOS).

---

## 1. Quality Gates Before Release

Run all automated tests in `npontu_sre_mobile/`:
```bash
cd npontu_sre_mobile
flutter test
```
All 25 tests must pass (including widget tests, model serialization, secure storage, and live Render integration tests).

---

## 2. Workspace & API Endpoint Configuration

Verify environment endpoints in `lib/core/config/app_config.dart`:
- **Production API URL**: `https://opsora.production/api/v1`
- **Render Production Health Probe**: `https://npontu-technologies-sre.onrender.com/api/health`
- **Header Propagation**: Ensure `X-Workspace-Id` interceptor is active in `ApiClient`.

---

## 3. Android Release Build & Bundle

### Generate Signed Android App Bundle (AAB):
```bash
cd npontu_sre_mobile
flutter build appbundle --release --obfuscate --split-debug-info=./build/app/outputs/symbols
```
Artifact output:
`build/app/outputs/bundle/release/app-release.aab`

### Testing on Physical Android Device (APK):
```bash
flutter build apk --release
flutter install
```

---

## 4. iOS Release Build & Archive

### Generate iOS Archive for App Store Connect:
```bash
flutter build ipa --release --export-options-plist=ios/ExportOptions.plist
```

---

## 5. Multi-Workspace Feature Verification on Device

Before releasing to Google Play / Apple App Store:
1. **Login Flow**: Sign in with operator credentials.
2. **Workspace Discovery**: Tap workspace chip in app bar to open `WorkspaceSwitcherSheet`.
3. **Workspace Switching**: Tap a secondary workspace. Verify activities reload instantly for that workspace.
4. **Company Code Join**: Enter a test company code. Verify prompt confirmation and automatic activation of the new organization workspace.
5. **Persistence Check**: Kill the app process and relaunch. Verify that the last active workspace ID is restored from `flutter_secure_storage`.
