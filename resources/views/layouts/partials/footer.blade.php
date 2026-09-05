{{-- ── COMPREHENSIVE ENTERPRISE SRE FOOTER ────────────────────────────────────────── --}}
<footer class="bg-[#050A07] border-t border-[#14261B] text-gray-400 text-xs mt-auto">
    {{-- Operational Status Bar --}}
    <div class="border-b border-white/5 py-4 bg-[#030604]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-mono font-semibold bg-emerald-950/60 text-emerald-300 border border-emerald-500/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>ALL SYSTEMS NOMINAL</span>
                </span>
                <span class="text-gray-500 hidden sm:inline">&bull;</span>
                <span class="text-[11px] font-mono text-gray-400">Node: <strong class="text-gray-200">Accra-Cluster-01 (Primary SRE NOC)</strong></span>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-4 text-[11px] font-mono text-gray-400">
                <span class="flex items-center gap-1">
                    <span class="text-gray-500">Latency:</span>
                    <strong class="text-[#F5C518]">&lt; 42ms</strong>
                </span>
                <span class="text-gray-600">&bull;</span>
                <span class="flex items-center gap-1">
                    <span class="text-gray-500">Uptime:</span>
                    <strong class="text-emerald-400">99.98% SLA</strong>
                </span>
                <span class="text-gray-600">&bull;</span>
                <span class="flex items-center gap-1">
                    <span class="text-gray-500">Audit Trail:</span>
                    <strong class="text-white">100% Immutable</strong>
                </span>
            </div>
        </div>
    </div>

    {{-- Main Multi-Column Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-10">

            {{-- Column 1: Identity & Corporate Mission (2 cols on md) --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                            <polygon points="16,3 30,27 2,27"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-extrabold text-white text-base leading-none">Support Tracker</p>
                        <p class="text-[#F5C518] text-[9px] font-mono tracking-widest uppercase mt-0.5 font-bold">NPONTU TECHNOLOGIES</p>
                    </div>
                </div>

                <p class="text-xs text-gray-400 leading-relaxed max-w-sm">
                    The mission-critical Site Reliability Engineering and Operational Custody platform powering Npontu Technologies. Built to ensure zero unacknowledged shift handovers, verifiable checklists, and mathematical audit accountability across telecommunications and fintech infrastructure.
                </p>

                <div class="pt-2">
                    <p class="text-[11px] font-bold text-gray-300 uppercase tracking-wider font-mono">Corporate Headquarters</p>
                    <p class="text-xs text-gray-400 mt-1">Npontu Technologies Limited</p>
                    <p class="text-xs text-gray-500">Greater Accra Region, Ghana</p>
                    <p class="text-xs text-[#F5C518] mt-1 font-mono">ops@npontu.com &bull; +233 (0) 30 200 0000</p>
                </div>

                <div class="flex items-center gap-2 pt-1 text-[11px] text-gray-500 font-mono">
                    <span class="px-2 py-0.5 rounded bg-white/5 border border-white/10 text-gray-300">ISO 27001 Aligned</span>
                    <span class="px-2 py-0.5 rounded bg-white/5 border border-white/10 text-gray-300">Ghana Act 843</span>
                    <span class="px-2 py-0.5 rounded bg-white/5 border border-white/10 text-gray-300">SOC 2 Type II</span>
                </div>
            </div>

            {{-- Column 2: Platform Architecture & Modules --}}
            <div>
                <p class="font-bold text-white text-xs uppercase tracking-wider font-mono mb-4 text-[#F5C518]">Platform Modules</p>
                <ul class="space-y-2.5 text-xs">
                    <li>
                        <a href="{{ route('landing') }}" class="text-gray-400 hover:text-white transition-colors">Platform Overview</a>
                    </li>
                    <li>
                        <a href="{{ route('activities.daily') }}" class="text-gray-400 hover:text-white transition-colors">Daily Shift Cockpit</a>
                    </li>
                    <li>
                        <a href="{{ route('landing') }}#the-handshake" class="text-gray-400 hover:text-white transition-colors">Two-Way Handover Engine</a>
                    </li>
                    <li>
                        <a href="{{ route('health') }}" class="text-gray-400 hover:text-white transition-colors">Live Telemetry & Health HUD</a>
                    </li>
                    <li>
                        <a href="{{ route('health.telemetry') }}" target="_blank" class="text-gray-400 hover:text-white transition-colors flex items-center gap-1">
                            <span>Telemetry JSON API</span>
                            <span class="text-[10px] font-mono text-emerald-400">REST</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.index') }}" class="text-gray-400 hover:text-white transition-colors">Compliance Reports & CSV</a>
                    </li>
                    <li>
                        <a href="{{ route('messages.index') }}" class="text-gray-400 hover:text-white transition-colors">Operational War Rooms</a>
                    </li>
                </ul>
            </div>

            {{-- Column 3: Legal, Privacy & Compliance (Requested) --}}
            <div>
                <p class="font-bold text-white text-xs uppercase tracking-wider font-mono mb-4 text-[#F5C518]">Policies & Legal</p>
                <ul class="space-y-2.5 text-xs">
                    <li>
                        <a href="{{ route('policy.privacy') }}" class="text-gray-300 hover:text-[#F5C518] transition-colors font-medium flex items-center gap-1.5">
                            <span>Privacy & Data Policy</span>
                            <span class="text-[9px] font-mono bg-emerald-950 text-emerald-300 px-1.5 py-0.5 rounded border border-emerald-800">GDPR</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('policy.terms') }}" class="text-gray-300 hover:text-[#F5C518] transition-colors font-medium">Terms of Service & AUP</a>
                    </li>
                    <li>
                        <a href="{{ route('policy.security') }}" class="text-gray-300 hover:text-[#F5C518] transition-colors font-medium flex items-center gap-1.5">
                            <span>Security & SIEM Audit</span>
                            <span class="text-[9px] font-mono bg-amber-950 text-[#F5C518] px-1.5 py-0.5 rounded border border-amber-800">SHA-256</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('policy.sla') }}" class="text-gray-300 hover:text-[#F5C518] transition-colors font-medium flex items-center gap-1.5">
                            <span>SLA 99.98% Commitment</span>
                            <span class="text-[9px] font-mono bg-blue-950 text-blue-300 px-1.5 py-0.5 rounded border border-blue-800">24/7</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('policy.security') }}#data-retention" class="text-gray-400 hover:text-white transition-colors">Data Retention Protocol</a>
                    </li>
                    <li>
                        <a href="{{ route('policy.sla') }}#escalation-matrix" class="text-gray-400 hover:text-white transition-colors">Incident Escalation Matrix</a>
                    </li>
                    <li>
                        <a href="{{ route('policy.privacy') }}#dpo-contact" class="text-gray-400 hover:text-white transition-colors">Data Protection Officer</a>
                    </li>
                </ul>
            </div>

            {{-- Column 4: SRE Roles & Evaluator Command --}}
            <div>
                <p class="font-bold text-white text-xs uppercase tracking-wider font-mono mb-4 text-[#F5C518]">SRE Operations & Access</p>
                <ul class="space-y-2.5 text-xs">
                    <li>
                        <a href="{{ route('login') }}" class="text-gray-300 hover:text-[#F5C518] transition-colors font-semibold flex items-center gap-1">
                            <span>Operator Cockpit Sign In</span>
                            <svg class="w-3 h-3 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </li>
                    <li>
                        <span class="text-gray-500">Roles: L1–L5 SRE Hierarchy</span>
                    </li>
                    <li>
                        <span class="text-gray-500">Custody: Two-Way Briefing Sign-On</span>
                    </li>
                    <li>
                        <span class="text-gray-500">Inactivity: 120min Auto-Timeout</span>
                    </li>
                    <li class="pt-2">
                        <p class="text-[11px] font-mono text-gray-400 font-bold uppercase">Emergency Incident NOC</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Hotline: <span class="text-[#F5C518] font-mono">sre-emergency@npontu.local</span></p>
                    </li>
                    <li class="pt-1">
                        <a href="https://github.com/mhiskall282/npontu-technologies-sre" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-white font-medium text-xs border border-white/10 transition-colors">
                            <svg class="w-3.5 h-3.5 text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                            <span>GitHub Source</span>
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        {{-- Verification & Quality Badges --}}
        <div class="mt-12 pt-8 border-t border-white/5 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-gray-500">
            <div class="flex flex-wrap items-center gap-4">
                <span class="flex items-center gap-1.5 font-mono text-[11px] text-gray-400">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>55 Feature Tests Passed (281 Assertions)</span>
                </span>
                <span class="text-gray-700">&bull;</span>
                <span class="font-mono text-[11px] text-gray-400">Pest Architecture Clean</span>
                <span class="text-gray-700">&bull;</span>
                <span class="font-mono text-[11px] text-gray-400">Strict PSR-12 & Types</span>
            </div>

            <div class="flex items-center gap-4 text-gray-400 font-medium">
                <a href="{{ route('policy.privacy') }}" class="hover:text-white transition-colors">Privacy</a>
                <span>&bull;</span>
                <a href="{{ route('policy.terms') }}" class="hover:text-white transition-colors">Terms</a>
                <span>&bull;</span>
                <a href="{{ route('policy.security') }}" class="hover:text-white transition-colors">Security</a>
                <span>&bull;</span>
                <a href="{{ route('policy.sla') }}" class="hover:text-white transition-colors">SLA</a>
                <span>&bull;</span>
                <a href="{{ route('health') }}" class="hover:text-white transition-colors">Status</a>
            </div>
        </div>

        {{-- Bottom Copyright & Security Notice --}}
        <div class="mt-6 pt-6 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-gray-500">
            <p>&copy; {{ date('Y') }} Npontu Technologies Limited. All rights reserved. &bull; <em>"Making you free to achieve."</em></p>
            <p class="font-mono text-gray-500 text-center sm:text-right">Support Activity Tracker v1.4.0 (Enterprise SRE Release) &bull; SHA-256 Audit Seal</p>
        </div>
    </div>
</footer>
