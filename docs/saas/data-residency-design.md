# Opsora SaaS Transformation — Data Residency & Regional Architecture

> **Status**: Approved Regional Governance Model (Stage 7)

---

## 1. Supported Regional Zones

Opsora establishes explicit data residency boundaries. A customer's operational data (incident records, shift handovers, chat messages, and audit trails) remains pinned to their selected jurisdictional region:

| Region Code | Display Name | Primary Cloud | Physical Location | Legal / Compliance Framework | Status |
|---|---|---|---|---|---|
| `us-east` | US East (N. Virginia) | AWS / Render | United States | SOC 2 Type II, HIPAA-ready | Active (Primary SaaS) |
| `eu-west` | Europe West (Frankfurt/Ireland) | AWS / Hetzner | European Union | GDPR, EU Data Protection | Active (Regional Pod) |
| `af-south` | Africa South (Johannesburg/Accra) | AWS / Local Colo | Ghana & South Africa | Data Protection Act (Ghana / POPIA) | Active (Local Hub) |
| `ap-southeast` | Asia Pacific (Singapore) | AWS | Singapore | PDPA, MAS TRM Guidelines | Planned |

---

## 2. Regional Data Pinning Architecture

1. **Storage Isolation**:
   - Backup snapshots for `eu-west` are stored strictly in European S3 buckets with cross-region transfer blocks.
   - Backup snapshots for `af-south` are pinned to local African storage zones.
2. **Dynamic Region Routing**:
   - `ResolveTenantContext` middleware checks the workspace's assigned region.
   - If a customer requests a dedicated regional endpoint, the DNS router directs traffic directly to the regional pod without routing payload through third-party jurisdictions.
3. **Cross-Border Transfer Restrictions**:
   - Platform administrator access across regional borders is flagged and logged as a high-severity audit event.
   - Real-time communication channels do not bridge across disjoint regional workspaces without explicit federation configuration.
