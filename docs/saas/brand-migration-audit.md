# Opsora SaaS Transformation — Brand Migration Audit

> **Document Status**: Approved Baseline & Canonical Audit  
> **Target Brand**: Opsora  
> **Legacy Brand**: Npontu Technologies / Npontu Technologies SRE  
> **Scope**: All source code, templates, views, configuration files, mobile assets, documentation, and test suites.

---

## 1. Classification Categories

Every reference to "Npontu" in the codebase has been identified and classified into one of five strictly governed operational categories:

1. **Category 1 — Customer-Facing & Safe to Rename**: User interfaces, page titles, footer text, marketing copy, notification labels, email greetings, and public metadata that can safely transition to "Opsora" without breaking underlying APIs, database structures, or deployment pipelines.
2. **Category 2 — Internal Legacy Reference**: Class names, design tokens (e.g. `NpontuColors`, `NpontuTheme`), helper constants, and CSS namespaces where renaming immediately carries high regression risk. Maintained with backward-compatible aliases or gradual deprecation.
3. **Category 3 — Migration-Sensitive Reference**: Database column values, default email domains (e.g. `@npontu.local`), seeded test usernames, session keys, and route names whose alteration requires deliberate database migration and testing.
4. **Category 4 — Deployment & Infrastructure Reference**: GitHub repository URLs (`mhiskall282/npontu-technologies-sre`), Render deployment identifiers, Android package names (`com.npontu.sre.npontu_sre_mobile`), and bundle IDs where premature renaming would break CI/CD pipelines or cloud infrastructure.
5. **Category 5 — Historical & Canonical Records**: Immutable audit trail snapshots, historical activity log notes, canonical brief documents (`docs/requirements.md`), and compliance certificates which must legally and factually remain untouched.

---

## 2. Comprehensive Inventory & Action Plan

| File Path | Existing Reference | Category | Proposed Action | Risk Level | Reversible? |
|---|---|---|---|---|---|
| `resources/views/layouts/app.blade.php:382` | `© {{ date('Y') }} Npontu Technologies` | Cat 1 | Update to `© {{ date('Y') }} Opsora SRE Operations` | Low | Yes |
| `resources/views/layouts/partials/footer.blade.php:169` | `github.com/mhiskall282/npontu-technologies-sre` | Cat 4 | Retain repository link (canonical GitHub URL) | Low | Yes |
| `resources/views/messages/email_reply.blade.php:6` | `Instant Reply — SRE Operations Comms — Npontu` | Cat 1 | Rename title to `Instant Reply — Opsora SRE Operations` | Low | Yes |
| `resources/views/messages/email_reply.blade.php:153` | `© {{ date('Y') }} Npontu Technologies` | Cat 1 | Rename to `© {{ date('Y') }} Opsora Technologies` | Low | Yes |
| `resources/views/messages/email_reply_success.blade.php:66` | `© {{ date('Y') }} Npontu Technologies` | Cat 1 | Rename to `© {{ date('Y') }} Opsora Technologies` | Low | Yes |
| `resources/views/policies/layout.blade.php:135` | `uptime SLA commitments for Npontu SRE systems` | Cat 1 | Rename to `uptime SLA commitments for Opsora SRE systems` | Low | Yes |
| `resources/views/policies/layout.blade.php:142` | `Npontu Legal` | Cat 1 | Rename to `Opsora Legal & Governance` | Low | Yes |
| `resources/views/policies/privacy.blade.php:161` | `The Npontu SRE Mobile Companion` | Cat 1 | Rename to `The Opsora SRE Mobile Cockpit` | Low | Yes |
| `resources/views/policies/privacy.blade.php:170` | `never transmitted to Npontu servers` | Cat 1 | Rename to `never transmitted to Opsora servers` | Low | Yes |
| `resources/views/policies/privacy.blade.php:176` | `authorized Npontu enterprise endpoints` | Cat 1 | Rename to `authorized Opsora enterprise endpoints` | Low | Yes |
| `resources/views/policies/sla.blade.php:32` | `Npontu Technologies guarantees a minimum monthly platform uptime...` | Cat 1 | Rename to `Opsora guarantees a minimum monthly platform uptime...` | Low | Yes |
| `resources/views/policies/terms.blade.php:4` | `meta_description: ...for Npontu Technologies Support Activity Tracker` | Cat 1 | Rename to `...for Opsora Support Activity & Operations Cockpit` | Low | Yes |
| `resources/views/policies/terms.blade.php:32` | `maintained by Npontu Technologies Limited` | Cat 1 | Update to `maintained by Opsora (formerly Npontu Technologies)` | Low | Yes |
| `resources/views/policies/terms.blade.php:46` | `The Npontu Handover Engine enforces...` | Cat 1 | Rename to `The Opsora Handover Engine enforces...` | Low | Yes |
| `resources/views/policies/terms.blade.php:94` | `under Npontu Technologies IT Governance policies` | Cat 1 | Rename to `under Opsora IT Governance policies` | Low | Yes |
| `resources/views/reports/print.blade.php:6` | `<title>Activity Report — Npontu Technologies</title>` | Cat 1 | Rename to `<title>Activity Report — Opsora SRE</title>` | Low | Yes |
| `resources/views/reports/print.blade.php:275` | `<div class="brand-name">Npontu Technologies</div>` | Cat 1 | Rename to `<div class="brand-name">Opsora SRE Operations</div>` | Low | Yes |
| `resources/views/reports/print.blade.php:441` | `© {{ date('Y') }} Npontu Technologies — Support Activity Tracker` | Cat 1 | Rename to `© {{ date('Y') }} Opsora — SRE Activity Tracker` | Low | Yes |
| `resources/views/landing.blade.php:709` | `github.com/mhiskall282/npontu-technologies-sre/actions` | Cat 4 | Retain exact GitHub repository action link | Low | Yes |
| `resources/views/auth/forgot-password.blade.php` | Legacy brand comments | Cat 1 | Ensure Opsora brand header and tokens | Low | Yes |
| `resources/views/auth/reset-password.blade.php` | Legacy brand comments | Cat 1 | Ensure Opsora brand header and tokens | Low | Yes |
| `resources/views/docs/index.blade.php` | Explanations referencing Npontu legacy context | Cat 1/5 | Present Opsora as primary platform, note Npontu legacy origin | Low | Yes |
| `app/Http/Controllers/LandingController.php` | Controller metadata | Cat 1 | Return Opsora brand title/meta | Low | Yes |
| `app/Http/Controllers/PolicyController.php` | Controller metadata | Cat 1 | Return Opsora brand title/meta | Low | Yes |
| `app/Http/Controllers/ReportController.php` | Export filenames `npontu-shift-handovers-...` | Cat 3 | Add config-driven filename prefix `opsora-` with backward compat | Medium | Yes |
| `app/Services/SystemHealthService.php` | System probe names | Cat 1 | Display Opsora System Health | Low | Yes |
| `routes/api.php:19` | `API Routes — Npontu SRE Operations Platform (Version 1)` | Cat 1 | Update comment to `API Routes — Opsora SRE Platform (V1)` | Low | Yes |
| `database/seeders/UserSeeder.php` | Default local domain `@npontu.local` | Cat 3 | Retain for existing local test suite; add Opsora tenant seeds | Medium | Yes |
| `database/seeders/ActivitySeeder.php` | Demo activity titles | Cat 1 | Generalize check titles | Low | Yes |
| `npontu_sre_mobile/lib/core/theme/npontu_theme.dart` | `NpontuColors`, `NpontuTheme` | Cat 2 | Alias `OpsoraColors = NpontuColors`, `OpsoraTheme = NpontuTheme` | Low | Yes |
| `npontu_sre_mobile/lib/core/constants/app_constants.dart` | `appName = 'Opsora SRE'` | Cat 1 | Already set to Opsora SRE | Low | Yes |
| `npontu_sre_mobile/android/app/build.gradle.kts` | `applicationId = "com.npontu.sre.npontu_sre_mobile"` | Cat 4 | Retain existing Android application ID to prevent package divergence | High | Yes |
| `npontu_sre_mobile/ios/Runner/Info.plist` | `CFBundleDisplayName = "Opsora SRE"` | Cat 1 | Confirmed Opsora SRE | Low | Yes |
| `npontu_sre_mobile/ios/Runner.xcodeproj/project.pbxproj` | Bundle identifier `com.npontu.sre.npontuSreMobile` | Cat 4 | Retain bundle ID for build stability | High | Yes |
| `.agents/AGENTS.md` | Core operating rules | Cat 5 | Retain canonical rules; record Opsora SaaS expansion | None | Yes |
| `docs/requirements.md` | Canonical original brief | Cat 5 | Retain unmodified as required by Section 7 & 9 of AGENTS.md | None | No |

---

## 3. Brand Preservation Guarantees

1. **Color Palette Preserved**:
   - Primary: `#1B6B3A` (Npontu/Opsora Forest Green)
   - Secondary / Accent: `#F5C518` (Opsora Gold/Amber)
   - Danger / Escalation: `#E63946` (SRE Critical Red)
   - Neutral Dark: `#0D1F15` (Deep Terminal Green/Black)
   - Neutral Light: `#F4F7F5` (Clean Sage / Cloud White)
2. **Layouts & Architecture Preserved**:
   - Blade + Livewire 3 modular layout is preserved without alteration.
   - Flutter Riverpod + GoRouter structure is preserved and extended.
3. **No Breaking Changes to Deployed APIs**:
   - Existing `/api/v1/*` contracts remain 100% backward compatible.
