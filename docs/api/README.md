# Opsora REST API — Architecture & Standards Guide

> **Status:** IMPLEMENTED  
> **API Version:** v1  
> **Base URL:** `https://opsora.production/api/v1`

The Opsora API provides programmatic access to technical operations, shift checklists, two-way handovers, multi-workspace routing, and compliance telemetry.

---

## 1. Request & Response Standards

### Required Headers
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <API_TOKEN>
X-Workspace-Id: <WORKSPACE_ID_OR_UUID>
```

### Standard JSON Envelopes
Successful responses return data wrapped in `data`:
```json
{
  "data": {
    "id": 14,
    "title": "USSD Gateway Latency Probe",
    "status": "done"
  }
}
```

### Standard Error Responses
Error responses follow consistent HTTP status conventions:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "company_code": [
      "No active organization matches this company code."
    ]
  }
}
```

| HTTP Status | Meaning |
|---|---|
| `200 OK` | Request succeeded |
| `201 Created` | Resource created successfully |
| `401 Unauthorized` | Invalid or expired Bearer token |
| `403 Forbidden` | Insufficient role or workspace membership suspended |
| `404 Not Found` | Resource does not exist in the active workspace |
| `422 Unprocessable Entity` | Validation error (details in `errors` field) |
| `429 Too Many Requests` | Rate limit exceeded (standard: 60 req/min) |
