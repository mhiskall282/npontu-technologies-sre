# Opsora REST API — Authentication & Token Lifecycle

> **Status:** IMPLEMENTED  
> **Mechanism:** Laravel Sanctum Bearer Tokens & Web Session Cookies

Opsora supports dual authentication:
1. **API Token Authentication**: Uses cryptographically secure Bearer tokens generated via Laravel Sanctum for mobile and external API consumers.
2. **Web Session Authentication**: Uses secure HTTP-only session cookies with CSRF protection for browser access.

---

## 1. Token Lifecycle

1. **Issuance**: Tokens are generated at `POST /api/v1/auth/login`. An optional `device_name` allows operators to identify sessions.
2. **Transmission**: Clients include the token in the HTTP `Authorization: Bearer <TOKEN>` header.
3. **Revocation (Logout)**: Send a `POST /api/v1/auth/logout` with Bearer token. The server permanently revokes the token from the `personal_access_tokens` table.
4. **Security Auditing**: Failed login attempts trigger rate limiting (5 attempts per minute) and record failed authentication events in security logs.
