# Platform Control Plane — Administrative Cockpit

> **Location:** `/admin/platform`  
> **Controller:** `App\Http\Controllers\Platform\PlatformDashboardController`  
> **Service:** `App\Services\PlatformMetricsService`

## Overview

The Administrative Cockpit provides a unified, real-time command center for platform administrators. In adherence to rule #53 ("No Fake Dashboard Data"), every metric and status widget is calculated from live database queries and actual infrastructure probes.

---

## Layout & Components

### 1. Header & Live Health Bar
- **Role Badge**: Dynamically displays the authenticated administrator's active platform role (`Root Super Administrator`, `Billing Administrator`, etc.).
- **Subsystem Latency Indicators**: Live ping times for the primary database (ms) and Redis/file cache (ms), along with queue worker status.
- **SLA Commitment**: Displays current uptime target (99.98%).

### 2. Platform Growth & Usage KPIs
- **Tenant Organizations**: Total registered organizations with active count breakdown.
- **Platform Users**: Total registered operators, engineers, and administrators.
- **Workspaces**: Multi-tenant team workspaces and isolated personal sandboxes.
- **Active SRE Incidents**: Current unresolved incidents requiring platform escalation.

### 3. Commercial Revenue & Subscription Distribution
- **MRR (Monthly Recurring Revenue)**: Calculated as the sum of monthly prices for all active paid subscriptions.
- **ARR (Annual Run Rate)**: Annualized MRR calculation (`MRR * 12`).
- **Subscription Distribution**: Breakdown across Developer Sandbox (Free), Team Operations, and Enterprise Sovereign tiers.

### 4. Operational Activity Stream
- **Recent Platform Events**: Real-time display of recent security events and audit log entries.
- **Tenant Context**: Shows which organization or workspace an event originated from.
