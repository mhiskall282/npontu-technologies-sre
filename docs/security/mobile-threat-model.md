# Mobile Security & Threat Model

This document outlines the security architecture, threat model, and mitigation strategies for the Npontu Technologies SRE mobile application (Android & iOS) and its integration with the Laravel 11 SRE Operations backend.

---

## 1. Executive Summary

The SRE Operations Platform provides mission-critical operational oversight for Npontu Technologies infrastructure, including shift checklist management, shift handover sign-offs, incident escalation war rooms, and system diagnostic telemetry. Because mobile devices operate in untrusted environments and traverse public cellular/Wi-Fi networks, a zero-trust model is enforced across all API touchpoints.

---

## 2. STRIDE Threat Analysis & Mitigations

### 2.1 Stolen Mobile Token
- **Threat**: An attacker extracts the mobile client's bearer token from device storage or intercepting communication, gaining authenticated API access.
- **Mitigation**:
  - **Platform-Native Secure Storage**: The mobile app uses `flutter_secure_storage` storing tokens in the **iOS Keychain** (with `kSecAttrAccessibleAfterFirstUnlockThisDeviceOnly`) and Android **KeyStore** using hardware-backed AES-GCM encryption. Never stored in plaintext or `SharedPreferences`.
  - **Token Revocation Endpoint**: Operators can revoke current tokens via `POST /api/v1/auth/logout` or revoke all active device sessions via `POST /api/v1/auth/revoke-sessions`.
  - **Append-Only Audit Trail**: Every token generation and revocation event is immutably logged with actor snapshot and IP address.

### 2.2 Unauthorized API Access & Broken Object-Level Authorization (BOLA)
- **Threat**: A low-privileged operator manipulates identifiers (e.g. `activity_id`, `handover_id`) to view or mutate another team's data or perform administrative tasks.
- **Mitigation**:
  - **Server-Side Laravel Policies**: Every API route enforces strict policy checks (e.g. `ActivityPolicy`, `ShiftHandoverPolicy`). Client-supplied user IDs, roles, and permissions are never trusted.
  - **Actor Context Injection**: All mutating actions derive the authenticated user directly from `$request->user()`, never from request payload parameters.
  - **Horizontal Access Controls**: Non-admin operators cannot sign off handovers intended for other users unless explicitly assigned.

### 2.3 Account Takeover & Brute Force Attacks
- **Threat**: Automated credential stuffing or brute force against `POST /api/v1/auth/login`.
- **Mitigation**:
  - **Rate Limiting**: The login endpoint is protected by Laravel's rate limiter (`5 attempts per minute per IP/account`), returning `HTTP 429 Too Many Requests`.
  - **Constant-Time Hash Verification**: Uses `Hash::check()` with Argon2id / Bcrypt to eliminate timing attacks.
  - **Detailed Failure Auditing**: Failed authentication attempts are logged with client IP address for SIEM monitoring.

### 2.4 Insecure File Uploads & Malicious Attachments
- **Threat**: An attacker uploads executable scripts or malicious files masquerading as logs or screenshots in war rooms.
- **Mitigation**:
  - **MIME & Size Restrictions**: Attachment payloads in `POST /api/v1/conversations/{id}/messages` are strictly validated to allowed types (`image/png`, `image/jpeg`, `application/pdf`, `text/plain`) and capped at 5 MB.
  - **Base64 Validation**: Base64 payloads are verified and decoded safely in isolated memory; direct execution paths on the backend server are completely prevented.

### 2.5 Real-Time Channel & WebSocket Authorization Failures
- **Threat**: Unauthorized clients eavesdrop on private incident war rooms or broadcast channels.
- **Mitigation**:
  - **Authenticated Channel Subscriptions**: All private channels require Bearer token validation matching channel participants.
  - **Polling Fallback**: For environments without persistent WebSockets, the app utilizes authenticated polling with TLS encryption.

### 2.6 Data Leakage Through Push Notifications
- **Threat**: Push notification payloads displaying sensitive credentials, incident runbooks, or customer data on locked screens.
- **Mitigation**:
  - **Generic Notification Payloads**: Push notifications contain only operational alerts without sensitive payloads (e.g., *"Shift handover briefing ready for sign-off"*, *"New message in War Room INC-401"*).
  - **Data Fetch On App Launch**: Sensitive context is retrieved over authenticated HTTPS only after the operator unlocks the app.

### 2.7 Malicious Deep Links
- **Threat**: An attacker tricks the app into executing arbitrary commands or navigating to hostile URLs via deep linking.
- **Mitigation**:
  - **Strict Path Parameter Parsing**: The GoRouter implementation validates all route parameters (e.g. `int.tryParse(id)`), rejecting non-numeric identifiers or unrecognized paths.
  - **No In-App WebViews**: The application avoids embedded WebViews with JavaScript execution.

### 2.8 Replay of Mutating Requests
- **Threat**: Network replay of checklist sign-offs or handover acceptances causing state race conditions.
- **Mitigation**:
  - **State Machine Verification**: Handover sign-offs verify current status is `initiated` prior to transitioning to `acknowledged`. Subsequent requests fail gracefully with idempotent responses.
  - **Database Transactions**: All state mutations run inside `DB::transaction()` with pessimistic locking where appropriate.

### 2.9 Lost or Compromised Devices
- **Threat**: An employee's mobile device is lost or stolen while active.
- **Mitigation**:
  - **Remote Session Invalidation**: Administrators can invalidate all active Sanctum tokens for any user account immediately.
  - **Auto-Logout On Token Expiry**: Client intercepts `401 Unauthorized` responses and immediately clears local secure storage, redirecting to the login screen.

### 2.10 Sensitive Information Exposure in Logs
- **Threat**: Passwords, tokens, or personal information recorded in application logs or crash reports.
- **Mitigation**:
  - **Dio Logging Redaction**: The network client strips `Authorization` headers, passwords, and tokens before logging.
  - **Production Debug Disabled**: In production builds, verbose logging is stripped by the Dart compiler.

---

## 3. Residual Risk & Ongoing Mitigations

| Risk | Level | Ongoing Mitigation |
|---|---|---|
| Rooted / Jailbroken Devices | Low | Consider optional root/jailbreak detection (`flutter_jailbreak_detection`) in high-security environments. |
| Memory Scraping via Debugger | Low | Android `FLAG_SECURE` can be enabled in production manifest if required by organizational policy. |
| Third-party Supply Chain Vulnerabilities | Low | Regular automated scanning via `composer audit` and `dart pub outdated`. |
