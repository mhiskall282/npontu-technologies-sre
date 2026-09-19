# Immutable Audit Trail

> **Module:** `/admin/platform/audit`  
> **Service:** `App\Services\AuditService`  
> **Model:** `App\Models\AuditLog` (`audit_logs` table)

## Overview

In accordance with strict SRE compliance standards and assignment requirements, every user-facing and platform-level mutation creates an immutable audit log entry. Audit logs are write-only and cannot be altered or purged by ordinary administrators.

---

## Log Record Schema

Each audit log entry captures:

- **`actor_id`**: Foreign key to `users` table.
- **`actor_name`**: Denormalized string snapshot of the actor's name at the instant of mutation (protects against subsequent user renaming).
- **`subject_type` & `subject_id`**: Morphable relationship pointing to the mutated resource (`User`, `Organization`, `Workspace`, `Subscription`, `FeatureFlag`, etc.).
- **`event`**: Event verb (`created`, `updated`, `status_changed`, `deleted`, `suspended`, `reactivated`).
- **`old_values`**: JSON snapshot of pre-mutation attribute values.
- **`new_values`**: JSON snapshot of post-mutation attribute values.
- **`ip_address`**: Client IP address of the initiating request.
- **`created_at`**: Immutable timestamp.

---

## Search & Filtering

The Platform Audit Trail screen (`/admin/platform/audit`) provides:
- Filtering by Event Verb (`created`, `updated`, `suspended`, `reactivated`, etc.).
- Filtering by Subject Type (`Organization`, `User`, `Plan`, `Subscription`, `FeatureFlag`, `Activity`, `ShiftHandover`).
- Free-text search matching actor name or IP address.
- Full JSON diff modal inspectable directly in the UI.
