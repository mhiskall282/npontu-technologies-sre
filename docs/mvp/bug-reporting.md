# Opsora SRE Platform — Bug Reporting & Triage Protocol

This document establishes the defect classification taxonomy, bug submission template, escalation paths, and remediation Service Level Agreements (SLAs) for the **Opsora SRE MVP** release.

---

## 1. Defect Severity Matrix

| Severity Level | Definition | Business Impact | Remediation SLA |
| :--- | :--- | :--- | :--- |
| **P1 — Blocker / Critical** | Tenant data leak, authentication bypass, data loss/corruption, system crash, or unhandled 500 error preventing core shift operations. | Complete operational outage or catastrophic security risk. | **< 4 Hours** |
| **P2 — Major** | Core workflow impaired (e.g. shift handover sign-off fails, activity status update fails, push alerts fail), but a viable workaround exists. | Serious impairment to shift engineers. | **< 24 Hours** |
| **P3 — Minor** | Secondary feature broken (e.g. search filter inaccuracy, layout clipping on certain screen widths, non-critical telemetry delay). | Inconvenience to users; no data loss. | **Next Sprint / 3 Days** |
| **P4 — Trivial / Cosmetic** | Typo, styling misalignment, icon mismatch, or minor visual inconsistency. | Minimal cosmetic defect. | **Backlog / 1 Week** |

---

## 2. Bug Submission Template

When logging defects in GitHub Issues or Jira during MVP pilot testing, testers must use this standard format:

```markdown
### 1. Summary
[Concise description of the failure, including module and platform]
Example: [Web/Activities] Resolution remark validation bypassed when updating via API

### 2. Environment
- **Platform**: Web (Chrome 128) / Android 14 / iOS 17.5 / REST API
- **App Version**: v1.0.0-MVP (Build 1)
- **Tenant / Workspace**: Tenant A ("Opsora SRE") / Workspace 1
- **User Role**: `engineer` (Kofi Asante)

### 3. Steps to Reproduce
1. Navigate to `/activities/create`
2. Enter title "Test Activity" and select priority "P1"
3. Click "Create Activity"
4. Change status to "Done" without typing a remark
5. Submit form

### 4. Expected Behavior
Validation error banner should display: "A resolution remark is required when marking an activity as completed." The status should remain "Pending".

### 5. Actual Behavior
Activity status changed to "Done" with empty remark, leaving audit trail without resolution rationale.

### 6. Screenshots / Logs
[Attach screenshot, HAR file, or terminal log output]

### 7. Severity
- [ ] P1 (Blocker)
- [x] P2 (Major)
- [ ] P3 (Minor)
- [ ] P4 (Trivial)
```

---

## 3. Bug Lifecycle & State Flow

```
[ New Bug Filed ]
       |
       v
[ Triaged by QA Lead ] ----> Rejected (Not a Bug / Duplicate)
       |
       v (Assigned Severity)
[ In Progress by Engineer ]
       |
       v
[ Code Fix Committed ]
       |
       v
[ Automated Tests Passed (Pest / Flutter) ]
       |
       v
[ Verified in QA Sandbox by Reporter ]
       |
       v
[ Closed / Released in Patch ]
```

---

## 4. Immediate Escalation Protocols

If a tester discovers a **tenant isolation breach** (e.g. Tenant A seeing Tenant B's incidents or activities) or a **credential compromise**:
1. Immediately halt further testing on that module.
2. Flag issue as `P1-SECURITY-BLOCKER`.
3. Ping the Principal SRE Architect directly via emergency channel.
4. Do not post customer data or sensitive tokens in public discussion boards.
