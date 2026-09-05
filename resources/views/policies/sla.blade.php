@extends('policies.layout')

@section('title', 'Service Level Agreement (SLA 99.98%) & Incident Escalation')
@section('meta_description', '99.98% SLA Uptime Commitment, Incident Response Times, and Escalation Matrix for Npontu Support Tracker')
@section('breadcrumb_current', 'SLA & Escalation')
@section('page_heading', 'Service Level Agreement (SLA 99.98%) & Incident Escalation')

@section('content')
<div class="space-y-8 text-gray-300 text-sm leading-relaxed">

    {{-- Policy Summary Header --}}
    <div class="p-4 rounded-xl bg-black/40 border border-emerald-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-semibold bg-emerald-950 text-emerald-300 border border-emerald-700/50">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Production Commitment &bull; Ref: NPT-SRE-SLA-04</span>
            </span>
            <p class="text-xs text-gray-400 mt-1.5">Availability Target: 99.98% Continuous Uptime &bull; Monitored 24/7/365</p>
        </div>
        <a href="{{ route('health') }}" class="inline-flex items-center gap-1.5 text-xs font-mono text-[#F5C518] hover:underline">
            <span>View Live Telemetry HUD &rarr;</span>
        </a>
    </div>

    {{-- Section 1: Availability SLA --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">1</span>
            <span>99.98% Operational Availability Commitment</span>
        </h2>
        <p>
            Npontu Technologies guarantees a minimum monthly platform uptime of <strong>99.98%</strong> for the Support Activity Tracker and associated health probe APIs. This corresponds to an allowed unscheduled downtime error budget of less than <strong>8 minutes and 45 seconds per month</strong>.
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 text-center">
            <div class="p-3.5 rounded-xl bg-black/30 border border-white/10">
                <p class="text-2xl font-black text-[#F5C518] font-mono">99.98%</p>
                <p class="text-[11px] text-gray-400 mt-1">Monthly Uptime SLA</p>
            </div>
            <div class="p-3.5 rounded-xl bg-black/30 border border-white/10">
                <p class="text-2xl font-black text-emerald-400 font-mono">&lt; 100ms</p>
                <p class="text-[11px] text-gray-400 mt-1">Probe API Latency</p>
            </div>
            <div class="p-3.5 rounded-xl bg-black/30 border border-white/10">
                <p class="text-2xl font-black text-white font-mono">8 Subsystems</p>
                <p class="text-[11px] text-gray-400 mt-1">Continuous Monitoring</p>
            </div>
            <div class="p-3.5 rounded-xl bg-black/30 border border-white/10">
                <p class="text-2xl font-black text-[#F5C518] font-mono">24/7/365</p>
                <p class="text-[11px] text-gray-400 mt-1">NOC Shift Rotations</p>
            </div>
        </div>
    </div>

    {{-- Section 2: Incident Severity & Response Times --}}
    <div id="escalation-matrix">
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">2</span>
            <span>Incident Severity & Mean Time to Resolution (MTTR) Targets</span>
        </h2>
        <div class="overflow-x-auto mt-3">
            <table class="w-full text-left text-xs border border-white/10 rounded-lg overflow-hidden">
                <thead class="bg-black/60 text-gray-300 font-mono">
                    <tr>
                        <th class="p-3 border-b border-white/10">Severity Level</th>
                        <th class="p-3 border-b border-white/10">Definition & Impact</th>
                        <th class="p-3 border-b border-white/10">Response Time</th>
                        <th class="p-3 border-b border-white/10">Resolution Target</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-gray-400">
                    <tr>
                        <td class="p-3 font-bold text-rose-400 font-mono">P1 - Critical</td>
                        <td class="p-3">Platform unreachable, shift handover engine down, or automated probes failing across clusters.</td>
                        <td class="p-3 font-mono text-emerald-400">&le; 5 minutes</td>
                        <td class="p-3 font-mono text-[#F5C518]">&le; 30 minutes</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-bold text-amber-400 font-mono">P2 - High</td>
                        <td class="p-3">Degraded telemetry latency (> 300ms) or operational chat war room broadcast delayed.</td>
                        <td class="p-3 font-mono text-emerald-400">&le; 15 minutes</td>
                        <td class="p-3 font-mono text-[#F5C518]">&le; 2 hours</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-bold text-blue-400 font-mono">P3 - Medium</td>
                        <td class="p-3">Non-blocking UI anomaly, delayed CSV compliance export, or single report generation slowdown.</td>
                        <td class="p-3 font-mono text-emerald-400">&le; 1 hour</td>
                        <td class="p-3 font-mono text-[#F5C518]">&le; 8 hours</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-bold text-gray-400 font-mono">P4 - Low</td>
                        <td class="p-3">Cosmetic suggestion, documentation typo, or non-urgent metric styling update.</td>
                        <td class="p-3 font-mono text-emerald-400">&le; 4 hours</td>
                        <td class="p-3 font-mono text-[#F5C518]">&le; 48 hours</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 3: Escalation Hierarchy --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-900/50 border border-emerald-700/50 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">3</span>
            <span>Emergency Escalation Hierarchy Tree</span>
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-3">
            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 text-center">
                <span class="w-6 h-6 rounded-full bg-white/10 text-white flex items-center justify-center text-xs font-bold mx-auto mb-2 font-mono">1</span>
                <p class="text-xs font-bold text-white">Tier 1: On-Call SRE</p>
                <p class="text-[11px] text-gray-400 mt-1">Initial triage, checklist verification, blocker logging</p>
            </div>
            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 text-center">
                <span class="w-6 h-6 rounded-full bg-white/10 text-white flex items-center justify-center text-xs font-bold mx-auto mb-2 font-mono">2</span>
                <p class="text-xs font-bold text-emerald-300">Tier 2: Shift Lead (L3)</p>
                <p class="text-[11px] text-gray-400 mt-1">Incident war room creation, `@all` alert, vendor paging</p>
            </div>
            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 text-center">
                <span class="w-6 h-6 rounded-full bg-white/10 text-white flex items-center justify-center text-xs font-bold mx-auto mb-2 font-mono">3</span>
                <p class="text-xs font-bold text-[#F5C518]">Tier 3: Principal SRE (L4)</p>
                <p class="text-[11px] text-gray-400 mt-1">Architectural failover, cluster fail-safe activation</p>
            </div>
            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 text-center">
                <span class="w-6 h-6 rounded-full bg-white/10 text-white flex items-center justify-center text-xs font-bold mx-auto mb-2 font-mono">4</span>
                <p class="text-xs font-bold text-rose-300">Tier 4: Director / CTO</p>
                <p class="text-[11px] text-gray-400 mt-1">Executive briefing, regulatory disclosure (if required)</p>
            </div>
        </div>
    </div>

    {{-- Section 4: Scheduled Maintenance Windows --}}
    <div class="p-5 rounded-xl bg-white/5 border border-white/10">
        <h3 class="text-base font-bold text-white">Scheduled Maintenance Windows</h3>
        <p class="text-xs text-gray-400 mt-1">
            Routine platform maintenance is scheduled during minimum-traffic windows: <strong>Sundays between 01:00 UTC and 03:00 UTC</strong>. Teams are notified at least 72 hours in advance via the in-app announcement banner and corporate operational channels.
        </p>
    </div>

</div>
@endsection
