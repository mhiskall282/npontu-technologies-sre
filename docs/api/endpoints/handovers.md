# API Endpoints — Two-Way Shift Handovers

### 1. `POST /api/v1/handovers`
- **Auth**: Required (`role: admin|lead`)
- **Description**: Record outgoing shift briefing sign-off and initiate custody transfer.
- **Request Body**:
```json
{
  "shift_date": "2026-09-18",
  "shift_type": "evening",
  "summary": "Core database replication lag verified normal. All checks resolved.",
  "open_issues": "None. Standby NOC engineer alerted."
}
```
- **Response**: `201 Created`
```json
{
  "status": "success",
  "message": "Shift handover briefing recorded. Awaiting oncoming lead signature.",
  "handover_id": 92
}
```

### 2. `POST /api/v1/handovers/{id}/accept`
- **Auth**: Required (`privilege: accept_handovers`)
- **Description**: Oncoming lead acknowledges checklist items and formally signs on.
- **Request Body**:
```json
{
  "acceptance_remarks": "Reviewed open tickets with outgoing lead. Operational custody accepted."
}
```
- **Response**: `200 OK`
```json
{
  "status": "success",
  "message": "Shift handover custody accepted and forensically sealed."
}
```
