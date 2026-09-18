# Opsora SRE Platform — Pilot Customer Onboarding Guide

Welcome to the **Opsora SRE Platform Pilot Program**! This guide assists engineering teams, DevOps managers, and SRE leads in establishing their organization, setting up their initial workspace, onboarding shift engineers, and executing operational handovers.

---

## 🧭 Pilot Journey Overview

```
[ Step 1: Sign Up ]
       ↓
[ Step 2: Create Organization / Workspace ]
       ↓
[ Step 3: Invite Shift Engineers (or Share Company Code) ]
       ↓
[ Step 4: Configure Operational Checklist Items ]
       ↓
[ Step 5: Execute Day Shift & Transfer via Handover ]
       ↓
[ Step 6: Connect Mobile SRE App ]
```

---

## Step 1: Account Registration

1. Navigate to the Opsora Web Cockpit: `http://localhost:8000/register` (or your pilot staging URL).
2. Enter your Name, Corporate Email, and secure Password.
3. Upon registration, Opsora automatically initializes your **Personal Workspace**, providing an immediate sandbox environment.

---

## Step 2: Establish Your Organization

To collaborate with your team, create an Organization workspace:
1. Open the Workspace Switcher in the top navigation bar.
2. Select **"Create New Organization"**.
3. Enter your Organization Name (e.g. `Acme Cloud SRE`) and choose your slug.
4. Your unique **Company Code** (e.g. `ACM-8291`) will be generated. Keep this code secure; you can distribute it to engineers for one-click onboarding.

---

## Step 3: Invite Team Members & Assign Roles

1. Navigate to **Organization Settings -> Team Members**.
2. Invite your engineers via email or provide them with your **Company Code**.
3. Assign appropriate operational roles:
   - **`admin`**: SRE Managers / DevOps Leads who manage billing, settings, and audits.
   - **`lead`**: Incident Commanders and Shift Supervisors who approve handovers.
   - **`engineer`**: On-call SREs and Operations Engineers who execute daily checklists.

---

## Step 4: Execute Shift Checklists

1. Go to **Shift Operations -> Daily Activities**.
2. Click **"New Activity"** to schedule routine operational probes:
   - Automated DB replica lag checks.
   - Payment gateway latency verification.
   - SSL certificate expiration checks.
   - Storage utilization monitoring.
3. When completing an item, click **"Mark as Done"** and input the required **Resolution Remark** (e.g. "Replica lag at 0.2s, within SLA").

---

## Step 5: Complete Your First Shift Handover

At the conclusion of your shift:
1. Navigate to **Shift Handovers -> Create Handover**.
2. Select your shift window (e.g. *Day Shift: 08:00 - 16:00*).
3. The platform automatically aggregates all unresolved and blocked tasks.
4. Enter your Outgoing Shift Notes and click **"Sign Off Handover"**.
5. The incoming Shift Lead opens the handover record, inspects the status, and clicks **"Acknowledge & Counter-Sign"**. The operational handover is immutably archived.

---

## Step 6: Install the Mobile App (`npontu_sre_mobile`)

Stay connected while away from your workstation:
1. Launch the Opsora SRE Mobile App on your Android or iOS device.
2. Enter your credentials.
3. Enable **Push Alerts** to receive instant P1/P2 incident notifications.
4. Enable **Biometric Sign-in** for fast fingerprint/FaceID cockpit access.

---

## 💬 Pilot Feedback & Dedicated Support

We value your feedback during this pilot evaluation:
- **Slack/Discord**: Dedicated `#opsora-pilot-eval` channel.
- **Email**: `pilot-support@opsora.io`
- **Bug Reporting**: Submit reports directly using the guidelines in [`bug-reporting.md`](./bug-reporting.md).
