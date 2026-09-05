@extends('policies.layout')

@section('title', 'Information Security & Forensic Audit Standard')
@section('meta_description', 'Information Security Architecture, Cryptographic Audit Trail Standards, and SIEM Logging for Npontu Support Tracker')
@section('breadcrumb_current', 'Security & SIEM Standard')
@section('page_heading', 'Information Security & Cryptographic Audit Standard')

@section('content')
<div class="space-y-8 text-gray-300 text-sm leading-relaxed">

    {{-- Policy Summary Header --}}
    <div class="p-4 rounded-xl bg-black/40 border border-emerald-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-semibold bg-emerald-950 text-emerald-300 border border-emerald-700/50">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Security Architecture Standard &bull; Ref: NPT-SRE-SEC-03</span>
            </span>
            <p class="text-xs text-gray-400 mt-1.5">Compliance: ISO 27001 (A.8.15) &bull; PCI-DSS v4.0 (Req 10) &bull; Zero-Trust SRE</p>
        </div>
        <div class="text-xs font-mono text-[#F5C518]">
            <span>100% Immutable Audit Coverage</span>
        </div>
    </div>

    {{-- Section 1: Core Security Architecture --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">1</span>
            <span>Zero-Trust Infrastructure & Security Architecture</span>
        </h2>
        <p>
            The Support Activity Tracker is engineered according to Zero-Trust architecture principles. No operator, script, or automated job is granted implicit operational trust regardless of network perimeter or IP origination.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                <p class="text-xs font-bold text-emerald-300 font-mono uppercase">Encryption In Transit</p>
                <p class="text-xs text-gray-400 mt-1">Strict TLS 1.3 with forward secrecy. HTTP Strict Transport Security (HSTS) enforced across all subdomains with 31536000-second cache lifetimes.</p>
            </div>
            <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                <p class="text-xs font-bold text-[#F5C518] font-mono uppercase">Encryption At Rest</p>
                <p class="text-xs text-gray-400 mt-1">MySQL InnoDB tablespaces encrypted via AES-256 block ciphers. Salted bcrypt password hashing with minimum work factor of 12 rounds.</p>
            </div>
        </div>
    </div>

    {{-- Section 2: Immutable Forensic Audit Trail --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">2</span>
            <span>Polymorphic Forensic Audit Engine</span>
        </h2>
        <p>
            To guarantee mathematical accountability during incident retrospectives, <strong>every mutating state change triggers an atomic audit write</strong> inside the same database transaction:
        </p>
        <div class="bg-black/60 border border-white/10 rounded-xl p-4 mt-3 font-mono text-xs">
            <p class="text-gray-400">// Audit Trail Payload Structure</p>
            <p class="text-emerald-300 mt-1">{</p>
            <p class="text-gray-300 ml-4">"actor_id": 2,</p>
            <p class="text-gray-300 ml-4">"actor_name": "Abena Owusu", <span class="text-gray-500">// Denormalized identity snapshot</span></p>
            <p class="text-gray-300 ml-4">"subject_type": "App\\Models\\Activity",</p>
            <p class="text-gray-300 ml-4">"subject_id": 48,</p>
            <p class="text-gray-300 ml-4">"event": "status_changed",</p>
            <p class="text-gray-300 ml-4">"old_values": {"status": "pending", "remark": null},</p>
            <p class="text-gray-300 ml-4">"new_values": {"status": "done", "remark": "Verified payment reconciliation queue"},</p>
            <p class="text-gray-300 ml-4">"ip_address": "197.251.134.12",</p>
            <p class="text-gray-300 ml-4">"created_at": "2026-09-05T04:00:00Z"</p>
            <p class="text-emerald-300">}</p>
        </div>
        <p class="text-xs text-gray-400 mt-2">
            Audit log entries are immutable. Database users utilized by the application have strictly <code class="text-emerald-300">INSERT</code> and <code class="text-emerald-300">SELECT</code> privileges on the <code class="text-emerald-300">audit_logs</code> table; <code class="text-rose-300">UPDATE</code> and <code class="text-rose-300">DELETE</code> statements are rejected at the MySQL engine level.
        </p>
    </div>

    {{-- Section 3: Session Security & 419 Interception --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">3</span>
            <span>Session Inactivity Safeguards & 419 Interception</span>
        </h2>
        <ul class="list-disc list-inside space-y-1.5 text-gray-400">
            <li><strong>Automated 120-Minute Inactivity Expire</strong>: Active operator sessions automatically terminate after 2 hours of inactivity to prevent unattended cockpit takeover.</li>
            <li><strong>Seamless 419 Interceptor</strong>: If a session expires during a long shift observation, Livewire requests gracefully trap HTTP 419 tokens and redirect the operator to the branded authentication recovery screen (<code class="text-emerald-300">/login?expired=1</code>) without raw browser error popups.</li>
            <li><strong>CSRF Token Binding</strong>: Every HTTP submission requires a valid, cryptographically random, per-session CSRF token.</li>
        </ul>
    </div>

    {{-- Section 4: Vulnerability Disclosure --}}
    <div class="p-5 rounded-xl bg-white/5 border border-white/10">
        <h3 class="text-base font-bold text-white">Responsible Vulnerability Disclosure</h3>
        <p class="text-xs text-gray-400 mt-1">
            If you discover a potential vulnerability in Npontu's SRE systems or telemetry endpoints, submit your findings to the Security Operations Center. We acknowledge reports within 4 hours and coordinate patch deployment:
        </p>
        <p class="text-xs font-mono text-[#F5C518] mt-2 font-bold">security@npontu.local &bull; PGP Fingerprint: 4F92 B102 D78A 5901</p>
    </div>

</div>
@endsection
