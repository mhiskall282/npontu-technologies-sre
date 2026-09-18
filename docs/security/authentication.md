# Opsora Security — Authentication & Authorization Standard

> **Status:** IMPLEMENTED  
> **Last Verified:** 2026-09-18

---

## 1. Authentication Defenses
- **Password Hashing**: Enforced using PHP `password_hash()` with `bcrypt` algorithm.
- **Session Security**: Cookies set with `SameSite=Lax`, `HttpOnly`, and `Secure` attributes.
- **Inactivity Timeout**: Sessions automatically terminate after 120 minutes of inactivity to protect unattended cockpit screens.
- **Brute Force Defense**: Rate limited to 5 attempts per minute per IP address.

---

## 2. Authorization Permission Matrix

| Granular Privilege | Super Admin (`admin`) | Shift Lead (`lead`) | Support Engineer (`engineer`) |
|---|---|---|---|
| `manage_activities` | Yes | Yes | Own assigned only |
| `assign_tasks` | Yes | Yes | No |
| `sign_handovers` | Yes | Yes | No |
| `accept_handovers` | Yes | Yes | No |
| `escalate_incidents` | Yes | Yes | Yes |
| `export_reports` | Yes | Yes | No |
| `manage_users` | Yes | No | No |
| `view_audit_logs` | Yes | Yes | No |
| `create_channels` | Yes | Yes | Configurable |
| `review_org_applications`| Yes | No | No |
