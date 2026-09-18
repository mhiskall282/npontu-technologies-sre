@extends('policies.layout')

@section('title', 'Operational Data & Privacy Policy')
@section('meta_description', 'Operational Privacy and Data Governance Policy for Opsora SRE Operations Platform')
@section('breadcrumb_current', 'Privacy Policy')
@section('page_heading', 'Operational Data & Privacy Policy')

@section('content')
<div class="space-y-8 text-gray-300 text-sm leading-relaxed">

    {{-- Policy Summary Header --}}
    <div class="p-4 rounded-xl bg-black/40 border border-emerald-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-semibold bg-emerald-950 text-emerald-300 border border-emerald-700/50">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Active Production Policy &bull; Legal Ref: OPSORA-SRE-POL-01</span>
            </span>
            <p class="text-xs text-gray-400 mt-1.5">Effective Date: January 1, 2026 &bull; Last Reviewed: September 17, 2026</p>
        </div>
        <a href="#dpo-contact" class="inline-flex items-center gap-1.5 text-xs font-mono text-[#F5C518] hover:underline">
            <span>Contact Data Protection Officer &rarr;</span>
        </a>
    </div>

    {{-- Section 1: Executive Scope --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">1</span>
            <span>Scope and Architectural Purpose</span>
        </h2>
        <p>
            This Operational Data & Privacy Policy governs the collection, processing, storage, and forensic archiving of operational telemetry, telemetry health probes, user activity records, shift handover briefs, and real-time operational communications executed within the <strong>Opsora SRE</strong> platform ("Opsora SRE", "we", or "the Platform").
        </p>
        <p class="mt-2 text-gray-400">
            This platform is strictly designated for internal site reliability engineering, telemetry monitoring, shift custody transfer, and infrastructure oversight across enterprise telecommunications and payment gateway nodes.
        </p>
    </div>

    {{-- Section 2: Information We Collect --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">2</span>
            <span>Categories of Operational Data Collected</span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                <p class="text-xs font-bold text-[#F5C518] uppercase tracking-wider font-mono">A. Operator Identity & Authentication</p>
                <p class="text-xs text-gray-400 mt-1">
                    Full name, authorized operator email address (<code class="text-emerald-300">email@email.com</code> / enterprise SRE domain), SRE technical grade (L1 through L5), assigned operational department, session tokens, and bcrypt-hashed password credentials.
                </p>
            </div>
            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                <p class="text-xs font-bold text-[#F5C518] uppercase tracking-wider font-mono">B. Immutable Audit Trail Telemetry</p>
                <p class="text-xs text-gray-400 mt-1">
                    Actor identity snapshots, client IPv4/IPv6 addresses, HTTP User-Agent strings, polymorphic model state mutations, before-and-after JSON attribute diffs, and cryptographic event timestamps.
                </p>
            </div>
            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                <p class="text-xs font-bold text-[#F5C518] uppercase tracking-wider font-mono">C. Shift Handover & Custody Logs</p>
                <p class="text-xs text-gray-400 mt-1">
                    Outgoing shift briefings, checklist completion states, blocker remarks, incident ticket references, and incoming lead sign-on verification stamps.
                </p>
            </div>
            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                <p class="text-xs font-bold text-[#F5C518] uppercase tracking-wider font-mono">D. System Health Probes & Monitoring</p>
                <p class="text-xs text-gray-400 mt-1">
                    Database latency heartbeats, cache eviction metrics, queue throughput, storage volume usage, and API response timings (< 100ms targets).
                </p>
            </div>
        </div>
    </div>

    {{-- Section 3: Legal Basis & Regulatory Compliance --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">3</span>
            <span>Legal Basis & Regulatory Compliance</span>
        </h2>
        <p>
            Data processing on this platform is executed pursuant to:
        </p>
        <ul class="list-disc list-inside mt-2 space-y-1.5 text-gray-400">
            <li><strong>Ghana Data Protection Act, 2012 (Act 843)</strong>: Compliance with principles of lawful data processing, data minimization, and protection against unauthorized alteration.</li>
            <li><strong>ISO/IEC 27001:2022 Security Standards</strong>: Mandatory operational logging (Control A.8.15), separation of duties, and access privilege controls.</li>
            <li><strong>PCI-DSS v4.0 Requirement 10</strong>: Comprehensive audit logs tracking all access to system components and automated integrity verification.</li>
        </ul>
    </div>

    {{-- Section 4: Data Retention & Purging --}}
    <div id="data-retention">
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">4</span>
            <span>Data Retention and Forensic Archival</span>
        </h2>
        <p>
            Because this application provides mission-critical infrastructure oversight, audit records are subject to strict non-repudiation mandates:
        </p>
        <div class="overflow-x-auto mt-3">
            <table class="w-full text-left text-xs border border-white/10 rounded-lg overflow-hidden">
                <thead class="bg-black/60 text-gray-300 font-mono">
                    <tr>
                        <th class="p-3 border-b border-white/10">Data Classification</th>
                        <th class="p-3 border-b border-white/10">Retention Window</th>
                        <th class="p-3 border-b border-white/10">Encryption At Rest</th>
                        <th class="p-3 border-b border-white/10">Purge Policy</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-gray-400">
                    <tr>
                        <td class="p-3 font-semibold text-white">Immutable Audit Logs</td>
                        <td class="p-3 font-mono text-emerald-400">7 Years (Statutory)</td>
                        <td class="p-3 font-mono">AES-256</td>
                        <td class="p-3">WORM (Write Once, Read Many) - No manual deletions</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-semibold text-white">Shift Handover Sign-Offs</td>
                        <td class="p-3 font-mono text-emerald-400">5 Years</td>
                        <td class="p-3 font-mono">AES-256</td>
                        <td class="p-3">Archived into compliance cold storage</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-semibold text-white">Operational Chat War Rooms</td>
                        <td class="p-3 font-mono text-emerald-400">2 Years</td>
                        <td class="p-3 font-mono">AES-256</td>
                        <td class="p-3">Automated annual archival rotation</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-semibold text-white">Ephemeral Health Probes</td>
                        <td class="p-3 font-mono text-emerald-400">90 Days</td>
                        <td class="p-3 font-mono">AES-256</td>
                        <td class="p-3">Rolled up into daily percentile summaries</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 5: Cookies & Client-Side Telemetry --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">5</span>
            <span>Cookies, Session Tokens & Client Storage</span>
        </h2>
        <p>
            Opsora SRE utilizes strictly necessary security tokens only. <strong>We do not use advertising, marketing, or third-party behavioral tracking cookies.</strong>
        </p>
        <ul class="list-disc list-inside mt-2 space-y-1 text-gray-400">
            <li><code class="text-emerald-300">XSRF-TOKEN</code>: Cryptographic token defending against Cross-Site Request Forgery attacks.</li>
            <li><code class="text-emerald-300">opsora_sre_session</code>: Encrypted session identifier expiring after 120 minutes of inactivity.</li>
            <li><code class="text-emerald-300">local_storage / clock</code>: Ephemeral UTC synchronization cache to ensure synchronized handover clocks across all engineering timezones.</li>
        </ul>
    </div>

    {{-- Section 6: Mobile Application & App Store / Play Store Compliance --}}
    <div id="mobile-privacy">
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">6</span>
            <span>Mobile Companion Application &amp; App Store / Play Store Disclosures</span>
        </h2>
        <p>
            The <strong>Npontu SRE Mobile Companion</strong> (available for Android and iOS) adheres strictly to Google Play User Data Policies and Apple App Store Review Guidelines (specifically Guideline 5.1.1 on Data Collection and Storage):
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                <p class="text-xs font-bold text-[#F5C518] uppercase tracking-wider font-mono">Mobile OS Permissions Requested</p>
                <ul class="mt-2 space-y-1.5 text-xs text-gray-400">
                    <li><strong class="text-white">Push Notifications:</strong> Used exclusively for real-time P1/P2 incident escalations and shift handover transfer requests. No promotional or marketing notifications are ever sent.</li>
                    <li><strong class="text-white">Local Encrypted Storage:</strong> Encrypted shared preferences cache operational checklists, handover history, and active war rooms for offline resilience.</li>
                    <li><strong class="text-white">Camera / Photo Library:</strong> Strictly optional; used only if an operator chooses to attach an error screenshot or topology diagram to an operational chat or activity log.</li>
                    <li><strong class="text-white">Biometrics (FaceID / Fingerprint):</strong> Handled locally on-device by OS Secure Enclave / Keystore; biometric vectors are never transmitted to Npontu servers.</li>
                </ul>
            </div>
            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                <p class="text-xs font-bold text-[#F5C518] uppercase tracking-wider font-mono">Zero Third-Party Trackers or Data Brokers</p>
                <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                    The mobile application does NOT include Google AdMob, Meta Audience Network, Firebase Analytics, or any third-party behavioral tracking SDKs. No Advertising ID (IDFA / AAID) is ever accessed or collected. All telemetry connects directly to authorized Npontu enterprise endpoints via TLS 1.3 encryption with certificate pinning capabilities.
                </p>
            </div>
        </div>
    </div>

    {{-- Section 7: Account Deletion & User Rights (Apple Guideline 5.1.1(v) & GDPR) --}}
    <div id="account-deletion" class="p-5 rounded-xl bg-black/40 border border-emerald-900/50">
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">7</span>
            <span>Account Deletion &amp; Data Subject Rights</span>
        </h2>
        <p class="text-xs text-gray-300 leading-relaxed">
            In compliance with <strong>Apple App Store Guideline 5.1.1(v)</strong>, <strong>Google Play User Data Policy</strong>, and the <strong>Ghana Data Protection Act (Act 843)</strong>, any registered operator or authorized user possesses the right to request the deletion or deactivation of their account and the extraction of their personal information:
        </p>
        <div class="mt-3 space-y-2 text-xs text-gray-400">
            <p>
                <strong class="text-white">How to initiate account deletion:</strong> Users can request account deletion directly within the mobile application under <em>Settings &rarr; Legal, Privacy &amp; Compliance &rarr; Account Deletion</em>, or by submitting an email to our Data Protection Office at <a href="mailto:hello@johnokyere.xyz" class="text-[#F5C518] hover:underline font-mono">hello@johnokyere.xyz</a> with the subject line <code>"Account Deletion Request - [Employee ID/Email]"</code>.
            </p>
            <p>
                <strong class="text-white">Processing &amp; SLA:</strong> Deletion requests are acknowledged within 48 hours and processed within 30 days. Personal profile identifiers, direct message channels, and active authentication credentials will be permanently erased or cryptographically pseudonymized.
            </p>
            <p class="text-gray-500 italic">
                * Note on Statutory Compliance: In accordance with PCI-DSS v4.0 Requirement 10 and ISO 27001 Control A.8.15, historic immutable audit trail logs and formal shift handover custody records associated with past system mutations are retained in immutable cold storage for the statutory retention period (7 years) to satisfy regulatory non-repudiation requirements.
            </p>
        </div>
    </div>

    {{-- Section 8: Contact & DPO --}}
    <div id="dpo-contact" class="p-5 rounded-xl bg-gradient-to-r from-emerald-950/40 via-black/40 to-transparent border border-emerald-800/40">
        <h3 class="text-base font-bold text-white">Governance & Data Protection Inquiries</h3>
        <p class="text-xs text-gray-400 mt-1">
            For inquiries regarding compliance, forensic audit data extraction, or account deletion requests, contact the Opsora SRE Data Protection Office and SRE Security Lead:
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3 font-mono text-xs">
            <div>
                <p class="text-gray-500">Data Protection Officer:</p>
                <p class="text-[#F5C518] font-bold"><a href="mailto:hello@johnokyere.xyz" class="hover:underline">hello@johnokyere.xyz</a></p>
            </div>
            <div>
                <p class="text-gray-500">SRE Security & Compliance Lead:</p>
                <p class="text-emerald-400 font-bold"><a href="mailto:hello@johnokyere.xyz" class="hover:underline">hello@johnokyere.xyz</a></p>
            </div>
        </div>
    </div>

</div>
@endsection
