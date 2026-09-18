# API Endpoints — Workspaces & Organizations

### 1. `GET /api/v1/workspaces`
- **Auth**: Required (`auth:sanctum`)
- **Description**: Returns all workspaces accessible to the authenticated operator.
- **Response**: `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "8f2b1c4e-5a6b-4c3d-9e8f-1a2b3c4d5e6f",
      "name": "Primary SRE Operations",
      "slug": "primary-sre",
      "role": "admin",
      "is_personal": false,
      "organization": {
        "id": 1,
        "name": "Opsora SRE",
        "company_code": "OPS-DEFAULT"
      }
    }
  ]
}
```

### 2. `POST /api/v1/workspaces`
- **Auth**: Required (`auth:sanctum`)
- **Description**: Provision a new workspace.
- **Request Body**:
```json
{
  "name": "Payment Gateway Operations",
  "subdomain": "payment-ops",
  "retention_days": 90
}
```
- **Response**: `201 Created`

### 3. `POST /api/v1/workspaces/join`
- **Auth**: Required (`auth:sanctum`)
- **Description**: Fast onboarding via unique company code.
- **Request Body**:
```json
{
  "company_code": "OPS-STARK9"
}
```
- **Response**: `200 OK`
