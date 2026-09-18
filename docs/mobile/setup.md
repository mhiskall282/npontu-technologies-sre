# Opsora Mobile — Setup & Emulator Guide

> **Status:** IMPLEMENTED  
> **Targets:** Windows Desktop, Android Emulator, iOS Simulator

---

## 1. Local PC & Emulator Execution

### Target 1: Windows Desktop Native Runner
The fastest method for local testing on your PC:
```bash
cd npontu_sre_mobile
flutter run -d windows
```

### Target 2: Android Studio Emulator (Pixel 8 / API 34)
When running inside the Android virtual machine, use Android's loopback alias `10.0.2.2:8000` to reach the Laravel backend running on your host machine:
```bash
flutter run -d emulator-5554 --dart-define=BASE_URL=http://10.0.2.2:8000/api/v1
```

---

## 2. Quality Verification Commands

Run the three mandatory quality commands before submitting mobile pull requests:
```bash
# 1. Automated Widget & Unit Tests (25 tests)
flutter test

# 2. Static Analyzer (0 errors / warnings)
flutter analyze

# 3. Dart Formatting Gate
dart format --set-exit-if-changed .
```
