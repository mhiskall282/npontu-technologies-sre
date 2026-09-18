@extends('policies.layout')

@section('title', 'Acceptable Use & Terms of Service')
@section('meta_description', 'Operational Terms of Service and SRE Acceptable Use Policy for Npontu Technologies Support Activity Tracker')
@section('breadcrumb_current', 'Terms of Service')
@section('page_heading', 'SRE Operations Acceptable Use & Terms of Service')

@section('content')
<div class="space-y-8 text-gray-300 text-sm leading-relaxed">

    {{-- Policy Summary Header --}}
    <div class="p-4 rounded-xl bg-black/40 border border-emerald-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-semibold bg-emerald-950 text-emerald-300 border border-emerald-700/50">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Operational SRE Standard &bull; Legal Ref: NPT-SRE-TERMS-02</span>
            </span>
            <p class="text-xs text-gray-400 mt-1.5">Effective Date: January 1, 2026 &bull; Authorized SRE Personnel & Platform Evaluators</p>
        </div>
        <div class="text-xs font-mono text-gray-400">
            <span>Jurisdiction: Ghana Law (Act 772 / Act 843)</span>
        </div>
    </div>

    {{-- Section 1: Acceptance & Permitted Use --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">1</span>
            <span>Permitted Use and Platform Authorization</span>
        </h2>
        <p>
            The Support Activity Tracker is a proprietary enterprise application created and maintained by <strong>Npontu Technologies Limited</strong>. Access is restricted exclusively to authenticated site reliability engineers, support operations staff, infrastructure supervisors, and designated platform evaluators.
        </p>
        <p class="mt-2 text-gray-400">
            By signing in to the console or inspecting telemetry streams, operators agree to conduct shift responsibilities with diligence, precision, and fidelity to live production state.
        </p>
    </div>

    {{-- Section 2: Shift Handover Non-Repudiation --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">2</span>
            <span>Two-Way Operational Custody Non-Repudiation</span>
        </h2>
        <p>
            The Npontu Handover Engine enforces mathematical and operational custody transfer between shifts:
        </p>
        <ul class="list-disc list-inside mt-2 space-y-2 text-gray-400">
            <li><strong>Mandatory Outgoing Sign-Off</strong>: The outgoing shift lead must confirm that all assigned checks are resolved, blocked items documented with descriptive remarks, and ongoing incidents clearly tagged.</li>
            <li><strong>Mandatory Incoming Sign-On</strong>: The incoming shift lead must independently review the handover briefing, verify critical subsystem telemetry, and record formal acceptance before operational responsibility shifts.</li>
            <li><strong>Prohibition of Pre-Signing</strong>: Under no circumstances may an engineer sign off on behalf of another operator or record sign-on prior to physically assuming shift command. All custody actions are cryptographically sealed with the operator's verified user ID and IP address.</li>
        </ul>
    </div>

    {{-- Section 3: Operational Roles & Granular Privileges --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">3</span>
            <span>Role-Based Access Control & Privilege Boundaries</span>
        </h2>
        <p>
            Users are bound by their assigned technical grade and granular permission set:
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                <p class="text-xs font-bold text-[#F5C518] font-mono">L1 & L2: Support Engineers</p>
                <p class="text-xs text-gray-400 mt-1">Authorized for status updates, remarks, blocker escalations, and operational chat communications.</p>
            </div>
            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                <p class="text-xs font-bold text-emerald-300 font-mono">L3: Shift Leads & Senior SRE</p>
                <p class="text-xs text-gray-400 mt-1">Authorized for task assignment, checklist authoring, formal shift sign-off, and handover acceptance.</p>
            </div>
            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                <p class="text-xs font-bold text-white font-mono">L4 & L5: Principal Leads</p>
                <p class="text-xs text-gray-400 mt-1">Authorized for user provisioning, forensic audit inspection, permission assignment, and emergency bypass.</p>
            </div>
        </div>
    </div>

    {{-- Section 4: Acceptable Use & Prohibited Conduct --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">4</span>
            <span>Prohibited Conduct and Incident Sanctions</span>
        </h2>
        <p>Operators are strictly prohibited from:</p>
        <ul class="list-disc list-inside mt-2 space-y-1.5 text-gray-400">
            <li>Submitting falsified or auto-generated checklist status marks without physical verification of target services.</li>
            <li>Sharing session credentials or bypassing two-factor authentication safeguards.</li>
            <li>Using operational war rooms for non-work communications or circulating sensitive customer cardholder data (PCI-DSS violation).</li>
            <li>Tampering with or attempting to manipulate the underlying MySQL InnoDB audit log sequences.</li>
        </ul>
        <p class="mt-2 text-xs text-rose-300 bg-rose-950/40 border border-rose-800/40 p-3 rounded-lg">
            Violations will trigger immediate account suspension, forensic SIEM log extraction, and disciplinary review under Npontu Technologies IT Governance policies.
        </p>
    </div>

    {{-- Section 5: Governing Law --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">5</span>
            <span>Governing Law and Dispute Resolution</span>
        </h2>
        <p>
            These Terms of Service are governed by the laws of the Republic of Ghana, including the Electronic Transactions Act (Act 772) and Data Protection Act (Act 843). Any dispute shall be resolved through arbitration in Accra, Ghana.
        </p>
    </div>

</div>
@endsection
