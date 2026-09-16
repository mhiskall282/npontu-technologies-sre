# Mobile Engineering & Development Guide

This guide details the local setup, architectural conventions, state management, and developer workflows for building and testing the **Npontu Technologies SRE Mobile App** (`npontu_sre_mobile`).

---

## 1. Local Environment Setup

### 1.1 Prerequisites
- **Flutter SDK**: Version 3.24+ (Channel stable)
- **Dart SDK**: Version 3.5+
- **Platform Tooling**:
  - **Android**: Android Studio Meerkat / Jellyfish, Android SDK 35, Command-line tools, Android Emulator.
  - **iOS (macOS only)**: Xcode 15.4+, CocoaPods (`sudo gem install cocoapods`), iOS Simulator.
- **Backend API**: Running Laravel 11 SRE Operations backend (`php artisan serve --host=0.0.0.0 --port=8000`).

### 1.2 Verifying Installation
```bash
flutter doctor -v
```

---

## 2. Running the Mobile Application

### 2.1 Connecting to Local Laravel Backend
Mobile emulators cannot connect directly to `http://localhost` without special alias addresses:
- **Android Emulator**: Uses `http://10.0.2.2:8000/api/v1`
- **iOS Simulator**: Uses `http://localhost:8000/api/v1`
- **Physical Device**: Uses your workstation's LAN IP (e.g., `http://192.168.1.150:8000/api/v1`)

Run the app with the target API URL:
```bash
# Android Emulator
flutter run -d emulator-5554 --dart-define=API_URL=http://10.0.2.2:8000/api/v1

# iOS Simulator
flutter run -d "iPhone 15 Pro" --dart-define=API_URL=http://localhost:8000/api/v1
```

### 2.2 Seed Test Accounts
The Laravel database seeder provisions standard testing accounts:
- **Admin**: `admin@npontu.com` / `password` (L4 Principal / Full Privileges)
- **Shift Lead**: `lead@npontu.com` / `password` (L3 Senior / Shift Management)
- **SRE Engineer**: `engineer@npontu.com` / `password` (L2 Engineer / Checklists)
- **SRE Agent**: `agent@npontu.com` / `password` (L1 Support / Basic Execution)

---

## 3. Architecture & Code Structure

The project strictly follows a **Feature-First Clean Architecture**:

```
npontu_sre_mobile/
├── android/                   # Native Android host configuration
├── ios/                       # Native iOS host configuration
├── lib/
│   ├── main.dart              # Entrypoint with ProviderScope initialization
│   ├── app.dart               # MaterialApp.router with Npontu theme definitions
│   │
│   ├── core/                  # Core cross-cutting infrastructure
│   │   ├── config/            # AppConfig with environment endpoints
│   │   ├── constants/         # API routes, storage keys, time formats
│   │   ├── errors/            # ApiException, NetworkException, ServerException
│   │   ├── network/           # Centralized Dio ApiClient with auth interceptors
│   │   ├── routing/           # GoRouter with authentication state guards
│   │   ├── storage/           # Keychain / Keystore secure storage
│   │   └── theme/             # Material 3 theme & Npontu brand tokens
│   │
│   ├── features/              # Feature modules
│   │   ├── auth/              # Login, token storage, user context
│   │   ├── dashboard/         # Cockpit overview, SLA metrics, shift tracker
│   │   ├── activities/        # Operational checklist, check-off modal, audit trail
│   │   ├── handovers/         # 2-Way shift handover briefings & sign-offs
│   │   ├── messaging/         # Ops chat channels, war rooms, direct messages
│   │   ├── health/            # SRE diagnostics, MySQL & Redis probes
│   │   ├── reports/           # Compliance metrics, date filters, event logs
│   │   ├── team/              # Operator directory, role filters, grades
│   │   └── audit/             # Immutable audit log with JSON state diffs
│   │
│   └── shared/                # Reusable widgets and domain models
│       ├── models/            # Strongly-typed models matching OpenAPI 3.0 schema
│       └── widgets/           # StatusBadge, PriorityBadge, AppDrawer, EmptyState
│
└── test/                      # Automated unit, widget, and integration test suite
```

---

## 4. Engineering Disciplines & Best Practices

1. **State Management**:
   - Use **Riverpod** (`StateNotifierProvider` / `StateNotifier`) for business logic and UI state.
   - Keep screens purely declarative; never place raw HTTP calls inside Flutter widgets.
2. **Network Resilience**:
   - All network requests pass through `ApiClient` which automatically attaches Bearer tokens, correlation IDs, and standard error handling.
   - Idempotent GET requests may be retried; mutating POST/PUT requests require user intervention on network timeouts.
3. **Brand Consistency**:
   - Never use arbitrary hex colors in UI files. Always reference `NpontuColors.green` (`#1B6B3A`), `NpontuColors.gold` (`#F5C518`), and `NpontuColors.danger` (`#E63946`).
4. **Security Discipline**:
   - Never store tokens in `SharedPreferences`. Always use `SecureStorageService`.
   - Never log passwords or authorization tokens in debug consoles.

---

## 5. Testing & Quality Gates

Run all checks before submitting changes:
```bash
# 1. Format check
dart format --output=none --set-exit-if-changed .

# 2. Static analysis
flutter analyze

# 3. Unit and widget test suite
flutter test
```
