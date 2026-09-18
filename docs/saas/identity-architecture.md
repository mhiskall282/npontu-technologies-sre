# Opsora SaaS Transformation — Identity & Authentication Architecture

> **Status**: Approved Identity Specification (Stage 8)

---

## 1. Dual Authentication Topologies

Opsora supports two complementary authentication flows:

```
TOPOLOGY A: PLATFORM-FIRST LOGIN
1. User visits https://opsora.app/login
2. Enters credentials (Email + Password + optional MFA)
3. Authenticated -> Opsora Identity Service verifies global user account
4. User selects from their accessible Workspaces (Personal or Organizations)
5. Session context dynamically shifts to target workspace: /w/{workspace-slug}

TOPOLOGY B: WORKSPACE-FIRST LOGIN
1. User visits https://acme-prod.opsora.app/login or enters company code "ACM-410"
2. Workspace identified -> Organization security policies loaded
3. User enters credentials
4. Backend verifies:
   - Valid User credentials
   - Active WorkspaceMembership in "acme-prod"
   - Active Organization subscription status (not suspended)
5. Session granted directly into workspace cockpit
```

---

## 2. Identity Security Controls

1. **Password Hashing**: Bcrypt with minimum 12 rounds.
2. **Session Security**:
   - Database-backed sessions with cryptographically random session IDs.
   - Automatic session invalidation upon password reset or privilege revocation.
3. **API Authentication**:
   - Laravel Sanctum personal access tokens.
   - Tokens can be scoped to specific workspace capabilities or global profile management.
   - Secure storage in mobile keychain (iOS) / EncryptedSharedPreferences (Android).
4. **Future Enterprise Identity Extension Points**:
   - SAML 2.0 / Okta / Azure AD SSO integration points designed in `OrganizationSettings`.
   - SCIM 2.0 automated provisioning listener interface.
   - WebAuthn / FIDO2 Passkey authentication hooks.
