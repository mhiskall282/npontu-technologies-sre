# Npontu SRE Operations Platform — Mobile API Reference
**Version**: 1.0 (RESTful)  
**Base URL**: `http://localhost:8000/api/v1` (Local) / `https://api-sre.npontu.com/api/v1` (Production)  
**Format**: JSON  
**Auth Scheme**: HTTP Bearer Token (Laravel Sanctum)  
**Status**: Production Ready  

---

## 1. Authentication & Security Headers

All protected endpoints require the `Authorization` header containing the Sanctum bearer token issued upon login:

```http
Authorization: Bearer <sanctum_token>
Accept: application/json
Content-Type: application/json
```

---

## 2. API Response Standard

### Success Format (`200 OK`, `201 Created`)
```json
{
  "success": true,
  "data": { ... },
  "meta": { ... },
  "message": "Request completed successfully"
}
```

### Error Format (`400`, `401`, `403`, `404`, `422`, `500`)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Please provide a valid email address."]
  }
}
```

---

## 3. Core Endpoints Summary

### 3.1 Authentication
- `POST /api/v1/auth/login`: Authenticate email and password, return Bearer token and user profile.
- `GET /api/v1/me`: Fetch authenticated user profile, grade, and unread message counter.
- `POST /api/v1/auth/logout`: Revoke current device access token.
- `POST /api/v1/auth/revoke-sessions`: Invalidate all tokens across all devices.

### 3.2 SRE Operations Dashboard
- `GET /api/v1/dashboard?date=YYYY-MM-DD`:
  Returns current shift period (morning/afternoon/night), check counts (total, completed, pending, completion rate), priority breakdown (P1-P4), active incident list, personal queue summary, and system health status.

### 3.3 Activities & Shift Checklist
- `GET /api/v1/activities`:
  List active checks. Supports queries: `?date=YYYY-MM-DD`, `?status=pending|done`, `?priority=critical|high|medium|low`, `?assigned=me|pool|<user_id>`, `?search=<term>`.
  Pinned checks float to top, followed by priority descending.
- `POST /api/v1/activities`:
  Provision a new operational check. Requires `manage_activities` privilege.
- `GET /api/v1/activities/{id}`:
  Detailed check definition and append-only update history.
- `PUT /api/v1/activities/{id}`:
  Update check definition. Requires `manage_activities` privilege.
- `DELETE /api/v1/activities/{id}`:
  Soft-delete check definition. Restricted to `admin`.
- `POST /api/v1/activities/{id}/status`:
  Record checkoff status update (`pending` or `done`). Server captures bio snapshot (operator name, role, IP) and creates immutable compliance audit log.
- `POST /api/v1/activities/bulk-assign`:
  Bulk delegate check definitions to an engineer or unassigned pool.

### 3.4 Shift Handovers
- `GET /api/v1/handovers`:
  Paginated handover briefings list with compliance metrics (acceptance rate, incident count).
- `POST /api/v1/handovers`:
  Outgoing lead authors and digitally signs off handover briefing. Automatically freezes check counts.
- `GET /api/v1/handovers/{id}`:
  Get handover details, incoming/outgoing lead metadata, and acceptance remarks.
- `POST /api/v1/handovers/{id}/accept`:
  Incoming lead formally acknowledges and accepts shift responsibility (Sign-on).

### 3.5 Operational Communications & War Rooms
- `GET /api/v1/conversations`:
  User conversations, team channels, and war rooms with live unread message counts.
- `POST /api/v1/conversations`:
  Create direct chat or group channel. Requires `create_channels` privilege.
- `GET /api/v1/conversations/{id}/messages`:
  Paginated message stream. Automatically updates participant `last_read_at`.
- `POST /api/v1/conversations/{id}/messages`:
  Send message with optional base64 compressed attachment (screenshot, log snippet).
- `POST /api/v1/conversations/{id}/read`:
  Mark all messages in conversation as read.

### 3.6 System Health & Telemetry
- `GET /api/v1/health`:
  Public JSON health probe for uptime monitors.
- `GET /api/v1/health/telemetry`:
  Real-time streaming SRE metrics (database query latency, memory footprint, cache latency).
- `GET /api/v1/health/diagnostics`:
  Deep subsystem diagnostics (Lead/Admin only).

### 3.7 Reports & Exports
- `GET /api/v1/reports`:
  Date-range activity checkoff query with database-aggregated chart metrics.
- `GET /api/v1/reports/handovers`:
  Handover compliance report.
- `GET /api/v1/reports/timelines`:
  Operator working hours and shift activity volume.
- `GET /api/v1/reports/export`:
  Unpaginated dataset for CSV generation.

### 3.8 Team Directory
- `GET /api/v1/team`:
  Roster of engineers, roles, grades (L1–L5), and contact information.

### 3.9 Security Audit Trail
- `GET /api/v1/audit-logs`:
  Immutable security audit trail entries with before/after diffs. Requires `view_audit_logs` privilege.
