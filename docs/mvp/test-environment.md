# MVP Test Environment Setup & Personas

This guide details how to set up the **Opsora SRE** testing environment, seed the required testing databases, and use the predefined test personas to evaluate end-to-end multi-tenant workflows.

---

## 1. Local Testing Environment Setup

### 1.1 Prerequisites
- **PHP**: 8.2+ with `pdo_mysql`, `bcmath`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`.
- **Composer**: 2.6+
- **MySQL**: 8.0+ running locally on port `3306` with database `npontu_tracker`.
- **Node.js**: 18+ (for asset builds if needed).
- **Flutter SDK**: 3.24+ (for mobile client testing).

### 1.2 Backend Initialization
```bash
# Clone and enter directory
cd npontu-technologies-sre

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Execute clean migrations and seed pilot data
php artisan migrate:fresh --seed

# (Optional) Verify all backend Pest tests pass
php artisan test
```

### 1.3 Mobile App Initialization (`npontu_sre_mobile`)
```bash
cd npontu_sre_mobile

# Fetch Flutter packages
flutter pub get

# Run test suite
flutter test

# Run Flutter web or mobile
flutter run -d chrome
# or for Android emulator:
flutter run -d emulator-5554
```

---

## 2. Seeded Test Personas & Workspaces

The database seeder (`DatabaseSeeder.php`) automatically generates two distinct organizations to test strict multi-tenant isolation.

### Tenant A: "Opsora SRE (Legacy Npontu)"
- **Organization ID**: `1`
- **Company Code**: `OPS-DEFAULT`
- **Primary Workspace ID**: `1` ("Primary SRE Operations")
- **Slug**: `opsora-sre`

#### Personas in Tenant A:
| Name | Email | Password | Role | Permissions |
| :--- | :--- | :--- | :--- | :--- |
| **John Okyere** | `john.okyere@npontu.com` | `password` | `admin` | Full administrative control, user invitation, role management, audit log access, workspace settings. |
| **Abena Owusu** | `abena.owusu@npontu.com` | `password` | `lead` | Shift handover sign-off/sign-on, incident triage, activity assignment, report generation. |
| **Kofi Asante** | `kofi.asante@npontu.com` | `password` | `engineer` | L1 operational checklist execution, status updates, remark notes, outgoing handover draft. |

---

### Tenant B: "Tenant B Operations (Pilot Evaluator)"
- **Organization ID**: `2`
- **Company Code**: `TENANT-B-TEST`
- **Primary Workspace ID**: `2` ("Tenant B Core Workspace")
- **Slug**: `tenant-b-ops`

#### Personas in Tenant B:
| Name | Email | Password | Role | Permissions |
| :--- | :--- | :--- | :--- | :--- |
| **Alice Vance** | `alice@tenantb.test` | `password` | `admin` | Organization admin for Tenant B. |
| **Bob Martinez** | `bob@tenantb.test` | `password` | `engineer` | Operations engineer for Tenant B. |

---

## 3. Tenant Isolation Verification Matrix

Testers must verify that cross-tenant access is strictly denied across all channels:

```
+-------------------+           REST API / Web UI             +-------------------+
|     Tenant A      | --------------------------------------> |     Tenant B      |
| (John, Abena, Kofi)                                         |   (Alice, Bob)    |
+-------------------+                                         +-------------------+
          |                                                             |
          v                                                             v
[Workspace 1 Data]                                            [Workspace 2 Data]
- Activities #1-#10                                           - Activities #11-#20
- Handovers #1-#3                                             - Handovers #4-#6
- Incidents #1-#2                                             - Incidents #3-#4
- Audit Logs (T1)                                             - Audit Logs (T2)
```

### Direct IDOR Probe Rules:
1. When logged in as **John (Tenant A)**, attempting to view `/activities/{id}` belonging to Tenant B must return **403 Forbidden** or **404 Not Found**.
2. Making API calls with header `X-Workspace-Id: 2` using John's Bearer token must return **403 Forbidden: Unauthorized workspace access**.
3. Livewire components must never display Tenant B's activity items or search results in Tenant A's cockpit.
