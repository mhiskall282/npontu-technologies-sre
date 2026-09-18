# Opsora Mobile — Flutter Architecture & Multi-Workspace Engine

> **Status:** IMPLEMENTED  
> **Flutter SDK:** 3.24+  
> **Dart SDK:** 3.5+  
> **Location:** `npontu_sre_mobile/`

The Opsora mobile application is built using Flutter for cross-platform Android, iOS, and Windows desktop execution.

---

## 1. Feature Architecture & Directory Layout

```text
npontu_sre_mobile/lib/
├── core/
│   ├── api/
│   │   ├── api_client.dart          # Dio client with X-Workspace-Id interceptor
│   │   └── api_endpoints.dart       # REST endpoint constants
│   ├── storage/
│   │   └── secure_storage.dart      # FlutterSecureStorage wrapper
│   └── theme/
│       └── app_theme.dart           # Opsora brand color tokens & typography
├── data/
│   └── models/
│       ├── activity_model.dart      # Operational activity model & serialization
│       ├── user_model.dart          # Operator model
│       └── workspace_model.dart     # Multi-tenant workspace model
└── features/
    ├── auth/                        # LoginScreen & session state
    ├── dashboard/                   # DashboardScreen & telemetry summary
    ├── activities/                  # Activity list & ActivityFormScreen
    ├── handovers/                   # Shift custody transfer screen
    └── workspaces/                  # WorkspaceController & WorkspaceSwitcherSheet
```

---

## 2. Multi-Workspace Mobile Experience
- **Workspace Switcher**: Operators tap the workspace badge in the top app bar to open `WorkspaceSwitcherSheet`, which queries `GET /api/v1/workspaces`.
- **Fast Join via Code**: Operators can input a company code directly from mobile to join an organization and switch context in 1 tap.
- **Header Injection**: All network calls automatically inject `X-Workspace-Id: <ID>` based on the active selection stored in `SecureStorageService`.
- **Automated Verification**: Verified by 25 passing unit and widget tests (`flutter test`).
