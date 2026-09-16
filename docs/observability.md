# Observability & Site Reliability Engineering Operations

This document establishes the observability architecture, telemetry standards, alert definitions, and incident response runbooks for the Npontu Technologies SRE platform across its web and native mobile clients.

---

## 1. Observability Architecture Overview

The multi-client SRE platform operates under high-availability constraints where immediate anomaly detection and auditability are required. Observability spans four pillars:
1. **Metrics**: Real-time health probes, request throughput, database latency, and SLA compliance percentages.
2. **Logs**: Structured JSON events with actor snapshot, context metadata, and correlation IDs.
3. **Traces / Correlation**: End-to-end request tracing linking mobile operations directly to database mutations and audit records.
4. **Audit Trail**: Immutable, append-only domain events (`audit_logs` and `activity_logs`) preserving state changes with JSON diffs.

```
┌────────────────────────────────────────────────────────┐
│              Multi-Client Operations                   │
│      Web Client (Livewire)  |  Mobile App (Flutter)    │
└───────────────────────────┬────────────────────────────┘
                            │ X-Correlation-ID
                            ▼
┌────────────────────────────────────────────────────────┐
│             API Gateway & Sanctum Auth                 │
│         Rate Limiter  |  CORS  |  Sanctum Auth         │
└───────────────────────────┬────────────────────────────┘
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│            Shared Laravel Domain Logic                 │
│      Actions  |  Services  |  Audit Log Listener       │
└──────────────┬──────────────────────────┬──────────────┘
               │                          │
               ▼                          ▼
┌──────────────────────────────┐ ┌──────────────────────┐
│       Telemetry & Health     │ │   Immutable Audit    │
│  - DB Latency Probe          │ │   - audit_logs       │
│  - Redis Ping & Queues       │ │   - activity_logs    │
│  - Response Time Profiler    │ │   - System Events    │
└──────────────────────────────┘ └──────────────────────┘
```

---

## 2. API Request Correlation & Tracing

All mobile requests automatically generate or forward an `X-Correlation-ID` header:
- Format: UUIDv4 (`e.g. 550e8400-e29b-41d4-a716-446655440000`)
- Generation: In Flutter, generated per HTTP request cycle by `ApiClient` interceptor if not provided.
- Backend Processing: Laravel middleware captures the correlation ID, attaches it to the request context (`Log::withContext(['correlation_id' => ...])`), and returns it in the response header.
- Troubleshooting: SRE staff can copy the correlation ID from mobile error dialogs and immediately query production logs.

---

## 3. Structured Logging Standard

All backend logs must use structured JSON format with normalized keys.

```json
{
  "timestamp": "2026-09-16T15:20:00.123Z",
  "level": "INFO",
  "message": "Activity checkoff status updated",
  "correlation_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "client": "mobile_flutter",
  "actor": {
    "id": 1,
    "name": "Kwame Mensah",
    "role": "lead",
    "grade": "l3_senior"
  },
  "context": {
    "activity_id": 42,
    "status": "done",
    "shift": "morning",
    "is_escalated": false,
    "duration_ms": 14.2
  },
  "ip_address": "192.168.1.100"
}
```

### Sensitive Data Redaction Rules
Logs and crash reports **must never** contain:
- Plaintext passwords or credentials
- Bearer tokens or Sanctum PAT plaintext
- Encryption keys or secrets
- Unredacted customer PII

---

## 4. Subsystem Health Probes & Telemetry

The platform exposes two tiers of health diagnostics:
1. **Public Liveness Probe** (`GET /api/v1/health`):
   - Unauthenticated endpoint for load balancers (AWS ALB / Kubernetes probes).
   - Returns `{ "status": "ok", "timestamp": "..." }` in < 5ms.
2. **Authenticated SRE Diagnostics** (`GET /api/v1/health/diagnostics` and `telemetry`):
   - Requires SRE Lead or Administrator permissions.
   - Evaluates:
     - **Database**: Ping, connection pool latency, slow queries count.
     - **Redis**: Ping latency, memory consumption, evicted keys.
     - **Queues**: Active workers, queue size, failed job count.
     - **Filesystem**: Disk storage usage percentage and write permissions.

---

## 5. Alerting Policies & Escalation Thresholds

| Alert Name | Severity | Condition | Notification Channel | Response Action |
|---|---|---|---|---|
| **Database Latency Spike** | P1 Critical | DB latency > 100ms for 3 consecutive minutes | PagerDuty, War Room `#incidents` | Check slow query log, scale read replicas. |
| **Shift Handover Delay** | P2 High | Shift ended > 45 mins without signed handover | Ops Channel `#shift-handover`, SMS to Lead | Contact outgoing shift lead; initiate handover. |
| **Authentication Brute Force** | P2 High | > 10 failed logins per IP within 5 mins | Slack `#security-alerts` | Verify rate limiter block; inspect IP threat score. |
| **Queue Worker Stalled** | P1 Critical | Failed jobs > 25 or queue latency > 120s | PagerDuty, Ops Channel | Restart Supervisor workers; inspect DLQ. |
| **Unacknowledged P1 Activity** | P1 Critical | P1 checklist item uncompleted at SLA window | Sound alert on Mobile app, War Room push | Notify active shift lead immediately. |

---

## 6. Incident Response & Mobile War Room Runbook

When an operational incident is detected:
1. **Declare Incident**:
   - The on-call SRE creates an incident record in the SRE cockpit.
   - Set severity: `P1 Critical` or `P2 High`.
2. **Spawn Mobile War Room**:
   - The backend automatically creates a dedicated War Room communication channel (`type: war_room`).
   - Incident responders receive high-priority push notifications with deep links directly into the war room.
3. **Collaborative Resolution**:
   - Responders post terminal outputs, log snippets, and runbook updates.
   - Real-time updates keep incoming and outgoing leads synchronized across desktop and mobile.
4. **Sign-Off & Retrospective**:
   - Status updated to `resolved`.
   - Immutable audit logs capture timestamps, resolving engineer, and resolution remarks.
