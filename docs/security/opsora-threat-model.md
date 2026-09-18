# Opsora SaaS Platform — Comprehensive Security Threat Model

> **Status**: Approved Security Threat Assessment (Stage 16)  
> **Framework**: STRIDE / OWASP Top 10 Multi-Tenant Cloud Architecture

---

## 1. System Threat Landscape

```
+-------------------------------------------------------------------------------+
| THREAT SURFACE                                                                |
+-------------------+-----------------------------------------------------------+
| Web Clients       | Cross-Site Scripting (XSS), Session Hijacking, CSRF       |
| Mobile Clients    | Insecure Token Storage, Reverse Engineering, MitM         |
| Public Gateways   | DDoS, Brute Force, Malicious Organization Registration    |
| Tenant Boundaries | IDOR, Cross-Tenant Query Leaks, Unauthorized Workspace Hop|
| API Surface       | Broken Object Level Authorization (BOLA), Rate-Limit Abuse|
| Control Plane     | Privileged Insider Misuse, Unauthorized Data Snooping     |
| Deployments       | Container Escape, Customer-Hosted Credential Exposure     |
+-------------------+-----------------------------------------------------------+
```

---

## 2. Threat Catalog & Defense Mechanisms

### T-01: Cross-Tenant Data Leakage via Direct Object Reference (BOLA / IDOR)
- **Threat**: Authenticated operator in Tenant A requests `/api/v1/activities/{id}` belonging to Tenant B.
- **Impact**: Severe breach of confidentiality and regulatory violation.
- **Mitigation**:
  - Global `TenantScope` query filter automatically applied on all Eloquent queries.
  - Form Requests validate that `{activity}` belongs to `app(TenantContext::class)->id`.
  - Automated Pest isolation tests assert HTTP 404 response.

### T-02: Workspace Subdomain & Domain Hijacking
- **Threat**: Attacker claims a reserved or prestigious subdomain (e.g. `apple.opsora.app`, `admin.opsora.app`).
- **Mitigation**:
  - Strict blacklisted subdomain registry (`admin`, `api`, `app`, `auth`, `billing`, `opsora`, `support`, `status`).
  - Custom domain verification requires TXT record cryptographic challenge before activation.

### T-03: Malicious Automated Organization Registration (Spam / Bot Farms)
- **Threat**: Automated bots flood platform with fake organization applications.
- **Mitigation**:
  - Rate limiting on `/register` and `/organizations/apply` (max 5 requests per IP per hour).
  - Configurable domain verification rules: free consumer email providers (gmail, yahoo) automatically route to manual review queue; enterprise domains with clean MX records qualify for fast-track.

### T-04: Mobile Bearer Token Compromise
- **Threat**: Stolen device or malicious app inspects local storage.
- **Mitigation**:
  - Sanctum bearer tokens stored strictly in hardware-backed platform storage (iOS Keychain / Android KeyStore with EncryptedSharedPreferences).
  - 1-click "Revoke All Active Sessions" available on web and mobile settings.
  - IP and user-agent binding logged upon authentication.

### T-05: Privileged Administrator Overreach
- **Threat**: Platform employee snoops on confidential SRE incident war rooms.
- **Mitigation**:
  - Control-plane administrators have no direct access to customer operational messages.
  - Emergency debug access requires documented reason, emits immutable `audit_logs` record, and generates notification to tenant owner.

### T-06: Billing & Entitlement Bypass
- **Threat**: Customer crafts API requests to execute features (e.g. bulk delegation) not included in their subscription tier.
- **Mitigation**:
  - Server-side authorization checks `EntitlementService::can($workspace, $feature)` before executing domain actions. Client-side UI controls are decorative only.
