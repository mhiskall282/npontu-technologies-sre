# Platform Health & Subsystem Diagnostics

> **Module:** `/admin/platform/health`  
> **Service:** `App\Services\PlatformMetricsService`

## Overview

The Platform Health module continuously audits the health and operational availability of all critical backend subsystems. Health data is derived from actual subsystem round-trip queries rather than static status constants.

---

## Probed Subsystems

### 1. Primary Database (`database`)
- Executes a live `SELECT 1` query.
- Measures round-trip query latency in milliseconds.
- Detects replica lag and connection pooling saturation.

### 2. Cache & Memory Store (`cache`)
- Performs an atomic write-read-delete cycle against the cache driver (Redis/file/database).
- Measures operation latency in milliseconds.

### 3. Queue Workers (`queue`)
- Verifies connection to the configured queue driver (`database`, `redis`, or `sync`).
- Inspects queue depth and reports failed jobs from the `failed_jobs` table.

### 4. Storage Subsystem (`storage`)
- Checks read/write permissions on the local disk root and public storage directory.
- Verifies symlink health for public assets.

### 5. API & Telemetry Pipeline (`api`)
- Probes `/api/v1/health` endpoint latency and verifies HTTP 200 availability.
