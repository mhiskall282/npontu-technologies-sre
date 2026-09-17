# SRE Engineering & Developer Onboarding Guide

> **Platform**: Opsora SRE Operations Platform (Enterprise SRE Release)  
> **Repository**: `npontu-technologies-sre`  
> **Target Audience**: Incoming Backend Developers, Flutter Mobile Engineers, SRE Shift Supervisors, and Security Auditors.

---

## 1. Architectural Overview & System Stack

The Opsora SRE Operations Platform is engineered for 24/7 mission-critical shift operations, continuous checklist verifications, zero-loss dual handovers, and forensic security auditing.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           CLIENT SURFACES                                   │
├──────────────────────────────────────────┬──────────────────────────────────┤
│  Flutter Mobile SRE Companion (v1.1.0)   │  Laravel Blade + Livewire 3      │
│  - Riverpod State Management             │  - Real-time reactive updates    │
│  - Offline-First SharedPreferences Cache │  - Tailwind CSS v3 Brand Tokens  │
│  - 3s Active War Room Polling Engine     │  - Alpine.js Modern Modals       │
└──────────────────────────────────────────┴──────────────────────────────────┘
                                    │
                                    ▼ (HTTPS / Sanctum Bearer Token)
┌─────────────────────────────────────────────────────────────────────────────┐
│                           LARAVEL 11 LTS CORE                               │
├─────────────────────────────────────────────────────────────────────────────┤
│  • Thin Controllers (HTTP orchestration only)                               │
│  • Form Requests (Strict input validation & sanitization)                   │
│  • Policies & Granular RBAC (Authorizes every mutation)                     │
│  • Action Classes (`app/Actions/`) for single-responsibility domain logic   │
│  • Immutable Security Audit Trail (`audit_logs` morphable engine)           │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                          PERSISTENCE & STORAGE                              │
├─────────────────────────────────────────────────────────────────────────────┤
│  • MySQL 8.0+ (InnoDB, strict foreign keys, transactional integrity)        │
│  • Redis / In-Memory cache for session and live telemetry                   │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. SRE Seniority Hierarchy & Engineering Grades

Every operator account belongs to an explicit **SRE Engineering Grade** (`L1`–`L5`). This grade dictates operational responsibilities, incident escalation authority, and shift supervisory roles.

| Grade | Title | Operational Scope & Clearances |
|---|---|---|
| **L1** | **Associate Support Operator** | Routine checkoffs, telemetry monitoring, baseline checklist validation. |
| **L2** | **Support Engineer (SRE)** | Core checklist execution, SLA window verifications, war room collaboration. |
| **L3** | **Senior SRE Specialist** | Deep infrastructure triage, P1/P2 root-cause investigation, high-tier checkoffs. |
| **L4** | **Team Lead & Shift Supervisor** | Oversees live shifts, conducts two-way handovers, assigns tasks, coordinates war rooms. |
| **L5** | **Principal Architect & Enterprise Lead** | Enterprise reliability governance, system architecture, disaster recovery commander. |

In the mobile app and web platform, tapping or clicking on any operator's name or avatar displays their **Full SRE Profile Modal**, showing their current grade, grade description, pod assignment, hotline, and granted system clearances.

---

## 3. Role-Based Access Control (RBAC) & UI Gating Rules

### 3.1 Strict UI Gating Rule
> **MANDATORY**: If an authenticated user does **NOT** possess the clearance or role required to perform an action, the corresponding button, floating action button, or navigation link **MUST BE COMPLETELY REMOVED FROM THE DOM / WIDGET TREE**. Never show disabled buttons or dead links that fail with 403 Forbidden.

### 3.2 Granular Permissions Catalog

```php
public const ALL_PRIVILEGES = [
    'manage_activities' => 'Create, edit, and configure operational activity checks',
    'assign_tasks'      => 'Delegate checks to team members individually or in bulk',
    'sign_handovers'    => 'Draft and digitally sign off SRE shift handover briefings',
    'accept_handovers'  => 'Formally acknowledge and accept incoming shift handovers',
    'escalate_incidents'=> 'Flag operational checks and attach incident tracking tickets',
    'export_reports'    => 'Access system reporting screens and export operational CSVs',
    'manage_users'      => 'Provision accounts and configure security privileges',
    'view_audit_logs'   => 'Inspect immutable security audit logs and state diffs',
    'create_channels'   => 'Create group communication channels and incident war rooms',
];
```

### 3.3 UI Implementation Matrix

| Component | Web (`resources/views/`) | Mobile (`npontu_sre_mobile/lib/`) | Gate Check |
|---|---|---|---|
| **Initiate Handover FAB** | Daily Board Handover Button | `HandoversScreen.floatingActionButton` | `user.canSignHandovers` |
| **Accept Handover Button** | Daily Board Accept Action | `_buildHandoverCard` Accept Action | `user.canAcceptHandovers && incomingUserId matches` |
| **New Activity Button** | `activities/index.blade.php` | `ActivitiesScreen.floatingActionButton` | `user.canManageActivities` |
| **Edit/Delete Activity** | `activities/show.blade.php` | `ActivityDetailScreen` AppBar Actions | `user.canManageChecklists` |
| **Compliance Reports Nav** | `sidebar-nav.blade.php` | `AppDrawer` Reports Tile | `user.canExportReports \|\| isAdmin \|\| isLead` |
| **Security Audit Trail** | `sidebar-nav.blade.php` | `AppDrawer` Audit Tile | `user.canViewAuditLogs \|\| isAdmin` |

---

## 4. Mobile SRE Companion Architecture

### 4.1 Offline-First Caching (`CacheService`)
The mobile application uses `SharedPreferences` managed through `CacheService`:
1. **Zero-Latency Launch**: `DashboardController`, `ActivitiesController`, and `HandoversController` immediately read from cache on screen mount (`0ms` cold-start).
2. **Non-Blocking Background Fetch**: A background HTTP call revalidates the cache. If offline, the user sees an amber status banner: `Offline Mode — Showing Cached Shift Data`.
3. **Manual Sync Controls**: Accessible in Settings under **Offline & Local Cache** (**Force Full Sync** and **Clear Cache**).

### 4.2 Active Chat Polling & Keyboard Inset Handling
1. **Active 3s Polling**: When entering `ChatScreen`, `startActivePolling()` starts a 3-second timer refreshing messages. It cleanly pauses on exit via `stopActivePolling()`.
2. **Keyboard Auto-Scroll**: `ChatScreen` attaches a `FocusNode` listener that animates the scroll controller to `maxScrollExtent` after 250ms when the virtual keyboard rises.
3. **Safe Area Insets**: The message input bar is wrapped in `SafeArea(bottom: true)` with `resizeToAvoidBottomInset: true` on the `Scaffold`, preventing floating navigation bar or keyboard overlaps.

### 4.3 Dropdown Button Assertion Safeguard
In Flutter, `DropdownButtonFormField<T>` throws an assertion error if the current `value` does not exist in `items`. When editing activities where `assignedTo` is an engineer not on page 1 of loaded members, `ActivityFormScreen` automatically injects the current assignee into `items`, preventing runtime crashes.

### 4.4 Animated Onboarding Walkthrough
1. **Interactive Carousel**: Located in `features/onboarding/onboarding_screen.dart`, featuring 4 slides (Real-Time Shift Execution, Zero-Loss Dual Handovers, Live War Rooms, Offline-First Resilience).
2. **State Persistence**: `CacheService.hasSeenOnboarding()` tracks if the user has completed or skipped onboarding.
3. **Replay Ability**: Users can replay the tour anytime from **Settings → About & Introduction → Platform Tour & Features**.

---

## 5. Security & Authentication Protocols

1. **Transactional Login Alerts**: Every mobile or web login dispatches:
   - A `SecurityLoginNotification` email with IP address, device user agent, and timestamp.
   - An in-app `OperationalNotification` record so the notification bell alerts the operator.
2. **Sanctum Tokens**: Bearer tokens are stored securely in `FlutterSecureStorage` with hardware biometric support.
3. **Profile Updates**: `PUT /api/v1/me` validates departments strictly against `User::DEPARTMENTS`.

---

## 6. Pre-Commit Verification & Quality Gates

Every developer must run and verify the following quality gates before pushing to `main`:

```bash
# 1. Backend PSR-12 Code Style Verification
./vendor/bin/pint --test

# 2. Backend Automated Test Suite (108 Tests, 532 Assertions)
php artisan test

# 3. Mobile Code Formatting Compliance
cd npontu_sre_mobile
dart format --output=none --set-exit-if-changed .

# 4. Mobile Static Analysis (Zero Issues)
flutter analyze

# 5. Mobile Automated Widget & Unit Tests (22 Tests)
flutter test
```
