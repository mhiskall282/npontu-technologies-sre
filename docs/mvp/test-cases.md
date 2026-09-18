# Opsora SRE Platform — MVP Test Cases

This catalog contains execution-ready test cases for validating the **Opsora SRE Platform** across web, API, and mobile platforms.

---

## Suite 1: Authentication, Workspaces & Tenant Isolation

### `TC-AUTH-01`: Standard Email/Password Login
- **Component**: Web Authentication (`/login`)
- **Preconditions**: Seeded user `kofi.asante@npontu.com` exists.
- **Steps**:
  1. Navigate to `/login`.
  2. Input email `kofi.asante@npontu.com` and password `password`.
  3. Submit the form.
- **Expected Result**: Authenticated session created; redirected to Cockpit Dashboard (`/`); user badge displays "Kofi Asante (Engineer)".

### `TC-AUTH-02`: Invalid Credentials Rejection & Rate Limiting
- **Component**: Web Authentication (`/login`)
- **Preconditions**: None.
- **Steps**:
  1. Navigate to `/login`.
  2. Input email `kofi.asante@npontu.com` and password `wrong_password`.
  3. Submit 5 times consecutively.
- **Expected Result**: Error alert "These credentials do not match our records."; on 5th attempt, throttle response displays "Too many login attempts. Please try again in 60 seconds."

### `TC-TENANT-01`: Multi-Tenant Data Isolation Enforcement
- **Component**: Workspace / Multi-Tenancy Middleware
- **Preconditions**:
  - Tenant A: John Okyere (`john.okyere@npontu.com`), Workspace 1.
  - Tenant B: Alice Vance (`alice@tenantb.test`), Workspace 2. Activity ID `15` belongs to Workspace 2.
- **Steps**:
  1. Log in as John Okyere (Tenant A).
  2. Attempt to directly access Tenant B's activity URL: `http://localhost:8000/activities/15`.
- **Expected Result**: HTTP `403 Forbidden` or `404 Not Found`. Tenant B's activity title, description, and status are never rendered.

### `TC-TENANT-02`: Cross-Tenant API Header Spoofing Prevention
- **Component**: REST API v1 (`/api/v1/activities`)
- **Preconditions**: John Okyere has valid Bearer token for Tenant A.
- **Steps**:
  1. Send `GET /api/v1/activities` with headers:
     - `Authorization: Bearer <john_token>`
     - `X-Workspace-Id: 2` (Tenant B Workspace)
- **Expected Result**: HTTP `403 Forbidden` with response payload: `{"success": false, "message": "Unauthorized access to requested workspace."}`.

---

## Suite 2: Shift Activities & Operational Checklists

### `TC-ACT-01`: Activity Creation with Priority and Category
- **Component**: Livewire Activities Module (`/activities/create`)
- **Preconditions**: Logged in as Kofi Asante.
- **Steps**:
  1. Navigate to Activities and click "Record New Activity".
  2. Set Title: `Verify Main Payment Gateway Webhook Latency`.
  3. Set Category: `Payment Gateway / FinTech`.
  4. Set Priority: `High (P2)`.
  5. Set Initial Status: `Pending`.
  6. Click "Create Activity".
- **Expected Result**: Activity created successfully; flash alert displayed; activity appears in personal checklist; audit log entry recorded.

### `TC-ACT-02`: Status Transition to Done Requires Mandatory Remark
- **Component**: Activities Status Update Action
- **Preconditions**: Activity in `Pending` state exists.
- **Steps**:
  1. Click "Mark as Done" on the pending activity.
  2. Leave the "Resolution Remarks" field empty.
  3. Click "Confirm Status Change".
- **Expected Result**: Form validation fails with message: "A resolution remark is required when marking an activity as completed or blocked."
- **Follow-up**:
  4. Enter remark: "Verified all webhook ACKs responding within 120ms."
  5. Click "Confirm Status Change".
- **Expected Result**: Status changes to `Done`; badge turns green; `resolved_at` timestamp stamped; audit entry created with old/new status diff.

---

## Suite 3: Shift Handover Protocol

### `TC-HAND-01`: Outgoing Engineer Handover Draft & Sign-Off
- **Component**: Shift Handovers (`/handovers`)
- **Preconditions**: Shift ending for Kofi Asante.
- **Steps**:
  1. Navigate to `/handovers/create`.
  2. Select Shift: `Day Shift (08:00 - 16:00)`.
  3. Complete Shift Summary: "All FinTech payment queues cleared. Two P3 tickets rolled over."
  4. Verify system automatically tags uncompleted checklist items.
  5. Click "Sign Off & Dispatch Handover".
- **Expected Result**: Handover record saved with status `Pending Counter-Signature`; digital signature snapshot recorded; notification sent to incoming shift lead.

### `TC-HAND-02`: Incoming Lead Handover Counter-Signature
- **Component**: Shift Handover Review
- **Preconditions**: Handover pending counter-signature; logged in as Abena Owusu (`lead`).
- **Steps**:
  1. Open pending handover record.
  2. Review checklist roll-overs and incident notes.
  3. Click "Acknowledge & Counter-Sign Handover".
- **Expected Result**: Handover marked `Completed / Signed On`; counter-signature timestamp recorded; shift transfer finalized in audit trail.

---

## Suite 4: System Diagnostics & Health Telemetry

### `TC-DIAG-01`: Real-time Ping & Probe Execution
- **Component**: SRE Diagnostics (`/health`)
- **Preconditions**: Backend operational.
- **Steps**:
  1. Navigate to `/health`.
  2. Click "Execute Diagnostic Probes".
- **Expected Result**: System probes MySQL connection pool, API response latency, disk storage usage, and queue worker status. Results render in green badges with latency in milliseconds.

---

## Suite 5: Flutter Mobile Application (`npontu_sre_mobile`)

### `TC-MOB-01`: Native Startup Bootstrap & Splash
- **Component**: Flutter Mobile Startup (`SplashScreen`)
- **Steps**:
  1. Launch mobile app from clean state (no stored token).
  2. Observe splash animation and loading steps.
- **Expected Result**: Branded Opsora logo pulses smoothly; status updates: "Hydrating offline telemetry nodes..." -> "Verifying security credentials..."; transitions cleanly to `/onboarding` or `/login` without white screen flash or error banner.

### `TC-MOB-02`: Mobile Shift Checklist & Offline Resilience
- **Component**: Mobile Activities Screen
- **Steps**:
  1. Log in with Kofi Asante's credentials.
  2. View Cockpit Dashboard and tap "Shift Checklist".
  3. Toggle device to Airplane mode (offline).
  4. Pull-to-refresh or tap an activity.
- **Expected Result**: App displays amber offline banner: "Operating in offline telemetry mode. Displaying cached operational data."; cached activities remain readable without crashing.
