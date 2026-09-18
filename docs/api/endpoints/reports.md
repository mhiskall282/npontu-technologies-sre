# API Endpoints — Reports & Health Telemetry

### 1. `GET /api/v1/reports`
- **Auth**: Required (`privilege: export_reports` or `role: admin|lead`)
- **Description**: Query activity metrics within a date range with status breakdown.
- **Query Params**:
  - `start_date` (YYYY-MM-DD)
  - `end_date` (YYYY-MM-DD)
  - `status` (all, done, pending)
- **Response**: `200 OK`

### 2. `GET /api/health`
- **Auth**: Public
- **Description**: Lightweight health probe for Render, AWS ALBs, Prometheus, and synthetic monitors.
- **Response**: `200 OK`
```json
{
  "status": "ok",
  "timestamp": "2026-09-18T23:06:00Z",
  "database": "connected",
  "database_latency_ms": 1.4,
  "telemetry_stream": "nominal",
  "uptime": "99.98%"
}
```

### 3. `GET /health/telemetry`
- **Auth**: Public
- **Description**: Returns live system telemetry metrics stream for dashboard graphs.
