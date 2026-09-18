# API Endpoints — Operational Activities

### 1. `GET /api/v1/activities`
- **Auth**: Required (`auth:sanctum`)
- **Headers**: `X-Workspace-Id: <id>` (optional, defaults to active context)
- **Description**: Returns all operational checklist activities for the active workspace.
- **Query Params**: `status` (pending/done), `priority` (critical/high/medium/low), `recurrence` (daily/weekly/monthly).
- **Response**: `200 OK`

### 2. `POST /api/v1/activities`
- **Auth**: Required (`role: admin|lead`)
- **Description**: Create a new operational activity.
- **Request Body**:
```json
{
  "title": "USSD Core Gateway Latency Check",
  "recurrence": "daily",
  "priority": "critical",
  "assigned_to": 3,
  "sla_hours": 2,
  "is_pinned": true
}
```
- **Response**: `201 Created`

### 3. `PATCH /api/v1/activities/{id}`
- **Auth**: Required
- **Description**: Update activity status or record incident remarks.
- **Request Body**:
```json
{
  "status": "done",
  "remarks": "Gateway response latency within baseline (< 120ms).",
  "is_incident": false
}
```
- **Response**: `200 OK`
