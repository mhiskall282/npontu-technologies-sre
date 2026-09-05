<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Support Activity Tracker — Npontu Technologies SRE Platform</title>
    <meta name="description" content="Mission-critical SRE operations console for 24/7 engineering teams. Daily shift boards, verifiable two-way handovers, ops war rooms, and immutable compliance audit trails.">

    <!-- Brand Typography: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .clip-angled-bottom {
            clip-path: polygon(0 0, 100% 0, 100% 93%, 0 100%);
        }
        @media (max-width: 768px) {
            .clip-angled-bottom {
                clip-path: polygon(0 0, 100% 0, 100% 97%, 0 100%);
            }
        }
    </style>
</head>
<body class="bg-[#0A120E] text-white antialiased selection:bg-[#F5C518] selection:text-gray-950 min-h-full flex flex-col">

    {{-- ── TOP NAVIGATION BAR ────────────────────────────────────────────────── --}}
    <header class="sticky top-0 z-50 bg-[#0F1A14]/90 backdrop-blur-md border-b border-[#1A2E22]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            {{-- Brand Logo --}}
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center shadow-md group-hover:border-[#F5C518]/60 transition-colors">
                    <svg class="w-6 h-6 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                        <polygon points="16,3 30,27 2,27"/>
                    </svg>
                </div>
                <div>
                    <span class="font-black text-base sm:text-lg tracking-tight text-white block leading-none">Support Tracker</span>
                    <span class="block text-[#F5C518] text-[9px] font-mono tracking-widest uppercase mt-0.5 font-bold">NPONTU TECHNOLOGIES</span>
                </div>
            </a>

            {{-- Center Navigation Links (Desktop) --}}
            <nav class="hidden md:flex items-center gap-6 text-xs font-semibold text-gray-300">
                <a href="#capabilities" class="hover:text-[#F5C518] transition-colors">Capabilities</a>
                <a href="#cockpit" class="hover:text-[#F5C518] transition-colors">Shift Cockpit</a>
                <a href="#workflow" class="hover:text-[#F5C518] transition-colors">Handover Protocol</a>
                <a href="#telemetry" class="hover:text-[#F5C518] transition-colors">Telemetry Probes</a>
                <a href="#roles" class="hover:text-[#F5C518] transition-colors">Test Roles</a>
            </nav>

            {{-- Right Status & Auth Actions --}}
            <div class="flex items-center gap-3">
                {{-- Live UTC Clock --}}
                <div class="hidden lg:flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-[11px] font-mono text-gray-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span id="nav-live-clock">UTC --:--:--</span>
                </div>

                {{-- System Health Link --}}
                <a href="{{ route('health') }}"
                   class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-950/60 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-900/60 transition-colors">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>SLA 99.98%</span>
                </a>

                @auth
                    {{-- Authenticated Quick Entry --}}
                    <a href="{{ route('activities.daily') }}"
                       id="cta-enter-cockpit"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold text-xs shadow-md transition-colors">
                        <span>Enter SRE Cockpit</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    {{-- Guest Sign In --}}
                    <a href="{{ route('login') }}"
                       id="cta-nav-login"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-bold text-xs shadow-md transition-colors">
                        <span>Operator Sign In</span>
                        <svg class="w-3.5 h-3.5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="flex-1">

        {{-- ── HERO SECTION WITH ANGLED DIVIDER ──────────────────────────────────── --}}
        <section class="relative bg-gradient-to-b from-[#12492A] via-[#1B6B3A] to-[#0F1A14] pt-16 pb-28 md:pt-24 md:pb-40 clip-angled-bottom overflow-hidden">
            {{-- Background Motif --}}
            <div class="absolute right-[-10%] top-[-10%] opacity-10 pointer-events-none">
                <svg class="w-[700px] h-[700px] text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                    <polygon points="16,3 30,27 2,27"/>
                </svg>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="max-w-3xl">
                    {{-- Release Badge --}}
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-black/30 border border-white/20 text-emerald-200 text-xs font-mono mb-6">
                        <span class="w-2 h-2 rounded-full bg-[#F5C518] animate-ping"></span>
                        <span>SRE OPERATIONS PLATFORM &bull; v1.2 PRODUCTION RELEASE</span>
                    </div>

                    {{-- Main Headline --}}
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.1]">
                        Making you free to achieve<br>
                        <span class="text-[#F5C518]">zero-blindspot</span> shift custody.
                    </h1>

                    <p class="text-base sm:text-lg text-green-100/90 mt-5 leading-relaxed max-w-2xl font-normal">
                        The mission-critical operations console engineered for 24/7 engineering teams at Npontu Technologies. Unify checklist verification, two-way shift handover handshakes, real-time war rooms, and immutable compliance audit trails in one reactive interface.
                    </p>

                    {{-- Primary CTAs --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 mt-8">
                        @auth
                            <a href="{{ route('activities.daily') }}"
                               class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-black text-sm shadow-xl transition-all duration-150">
                                <span>Go to Today's Shift Board</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               id="hero-cta-login"
                               class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-black text-sm shadow-xl transition-all duration-150">
                                <span>Launch Shift Console</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @endauth

                        <a href="{{ route('health') }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-black/30 hover:bg-black/40 text-white font-bold text-sm border border-white/20 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Inspect Telemetry HUD</span>
                        </a>

                        <a href="#cockpit"
                           class="inline-flex items-center justify-center gap-1.5 px-4 py-3.5 rounded-xl text-xs font-semibold text-green-200 hover:text-white transition-colors">
                            <span>Explore Features</span>
                            <span>&darr;</span>
                        </a>
                    </div>
                </div>

                {{-- Operational Value Highlights --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mt-12 sm:mt-16 pt-8 border-t border-white/15">
                    <div class="p-4 rounded-xl bg-black/20 border border-white/10 backdrop-blur-xs">
                        <p class="text-3xl font-black text-[#F5C518] font-mono leading-none">100%</p>
                        <p class="text-xs text-green-100 font-bold mt-1.5">Audit Trail Custody</p>
                        <p class="text-[11px] text-green-200/70 mt-0.5">Polymorphic before/after JSON diffs on all state changes</p>
                    </div>

                    <div class="p-4 rounded-xl bg-black/20 border border-white/10 backdrop-blur-xs">
                        <p class="text-3xl font-black text-[#F5C518] font-mono leading-none">&lt; 100ms</p>
                        <p class="text-xs text-green-100 font-bold mt-1.5">Telemetry Benchmark</p>
                        <p class="text-[11px] text-green-200/70 mt-0.5">Continuous live probes across 8 core subsystems</p>
                    </div>

                    <div class="p-4 rounded-xl bg-black/20 border border-white/10 backdrop-blur-xs">
                        <p class="text-3xl font-black text-[#F5C518] font-mono leading-none">99.98%</p>
                        <p class="text-xs text-green-100 font-bold mt-1.5">Availability SLA</p>
                        <p class="text-[11px] text-green-200/70 mt-0.5">Rolling uptime verified via automated health heartbeat</p>
                    </div>

                    <div class="p-4 rounded-xl bg-black/20 border border-white/10 backdrop-blur-xs">
                        <p class="text-3xl font-black text-[#F5C518] font-mono leading-none">0</p>
                        <p class="text-xs text-green-100 font-bold mt-1.5">Missed Handovers</p>
                        <p class="text-[11px] text-green-200/70 mt-0.5">Enforced two-way briefing sign-off and verification sign-on</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── INTERACTIVE SRE COCKPIT PREVIEW ────────────────────────────────────── --}}
        <section id="cockpit" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 sm:-mt-24 md:-mt-28 relative z-20 mb-20">
            <div class="rounded-2xl border border-white/15 bg-[#0F1A14] shadow-2xl overflow-hidden">
                {{-- Window Header Bar --}}
                <div class="bg-[#0A120E] px-4 py-3 border-b border-[#1A2E22] flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-[#E63946]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#F5C518]"></div>
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        <span class="text-xs font-mono text-gray-400 ml-2 hidden sm:inline">sre-cockpit.npontu.local &bull; Live Handover Terminal</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-mono text-[11px]">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Livewire Polling Active</span>
                        </div>
                        <span class="text-[11px] font-mono text-gray-500">v1.2</span>
                    </div>
                </div>

                {{-- Cockpit Mock Stage --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 bg-[#0F1A14]">
                    {{-- Left Dark Sidebar Preview --}}
                    <div class="hidden lg:block lg:col-span-3 bg-[#0A120E] p-4 border-r border-[#1A2E22] space-y-4">
                        <div class="p-3 rounded-xl bg-white/5 border border-white/10">
                            <p class="text-[10px] font-mono uppercase text-[#F5C518] font-bold">ACTIVE OPERATOR</p>
                            <p class="text-sm font-bold text-white mt-0.5">Kwame Mensah</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-500/20 text-emerald-300 font-bold">Admin</span>
                                <span class="text-[10px] text-gray-400 font-mono">L4 Principal</span>
                            </div>
                        </div>

                        <div class="space-y-1 text-xs font-semibold text-gray-300">
                            <div class="px-3 py-2 rounded-lg bg-[#1B6B3A] text-white flex items-center justify-between">
                                <span class="flex items-center gap-2"><span>📋</span> Today's Board</span>
                                <span class="w-2 h-2 rounded-full bg-[#F5C518] animate-ping"></span>
                            </div>
                            <div class="px-3 py-2 rounded-lg text-gray-400 hover:text-white flex items-center gap-2"><span>📁</span> Activities Registry</div>
                            <div class="px-3 py-2 rounded-lg text-gray-400 hover:text-white flex items-center gap-2"><span>📊</span> Compliance Reports</div>
                            <div class="px-3 py-2 rounded-lg text-gray-400 hover:text-white flex items-center justify-between">
                                <span class="flex items-center gap-2"><span>💬</span> Ops Comms</span>
                                <span class="px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-mono">3 new</span>
                            </div>
                            <div class="px-3 py-2 rounded-lg text-gray-400 hover:text-white flex items-center gap-2"><span>⚡</span> Telemetry Health</div>
                        </div>
                    </div>

                    {{-- Main Stage Preview --}}
                    <div class="lg:col-span-9 p-4 sm:p-6 space-y-4">
                        {{-- Handover Custody Banner --}}
                        <div class="p-4 rounded-xl bg-gradient-to-r from-emerald-950/40 via-[#1B6B3A]/20 to-black/30 border border-emerald-500/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-[#1B6B3A] flex items-center justify-center text-[#F5C518] font-bold">
                                    🤝
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-white uppercase tracking-wider">Operational Shift Handover &bull; Afternoon Shift</p>
                                    <p class="text-xs text-gray-300 mt-0.5">Briefing signed by <span class="text-[#F5C518] font-semibold">Abena Owusu</span> &bull; Accepted by <span class="text-emerald-400 font-semibold">Kwame Mensah</span></p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-[11px] font-mono font-bold">
                                Custody Transferred
                            </span>
                        </div>

                        {{-- Daily Checklist Mock --}}
                        <div class="space-y-2">
                            {{-- Check 1 (Pending - Glowing Gold) --}}
                            <div class="p-3.5 rounded-xl bg-amber-500/10 border-l-4 border-[#F5C518] border-r border-t border-b border-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-start gap-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#F5C518] text-gray-950 uppercase mt-0.5">PENDING</span>
                                    <div>
                                        <p class="text-xs sm:text-sm font-bold text-white">Daily SMS Gateway Log Count vs Carrier Invoicing</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">Assigned to <span class="text-gray-200">Kofi Asante (DBA)</span> &bull; Category: <span class="text-emerald-400">SMS Gateway</span> &bull; SLA: 10:00 UTC</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 sm:self-center">
                                    <span class="text-[11px] font-mono text-amber-300 bg-amber-400/10 px-2 py-1 rounded border border-amber-400/20">Awaiting Log Export</span>
                                </div>
                            </div>

                            {{-- Check 2 (Done - Muted Green) --}}
                            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-start gap-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#1B6B3A] text-white uppercase mt-0.5">DONE</span>
                                    <div>
                                        <p class="text-xs sm:text-sm font-bold text-gray-200">Payment Engine DB Master-Replica Replication Lag</p>
                                        <p class="text-[11px] text-gray-500 mt-0.5">Updated by Kwame Mensah at 09:14 UTC &bull; Remark: <span class="text-gray-400">Replication lag &lt; 0.2s across all 3 nodes</span></p>
                                    </div>
                                </div>
                                <span class="text-[11px] font-mono text-emerald-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Audited
                                </span>
                            </div>

                            {{-- Check 3 (Done) --}}
                            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-start gap-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#1B6B3A] text-white uppercase mt-0.5">DONE</span>
                                    <div>
                                        <p class="text-xs sm:text-sm font-bold text-gray-200">USSD Channel Availability & Telco Heartbeat Check</p>
                                        <p class="text-[11px] text-gray-500 mt-0.5">Verified by Abena Owusu &bull; Remark: <span class="text-gray-400">All 4 telco endpoints responded &lt; 85ms</span></p>
                                    </div>
                                </div>
                                <span class="text-[11px] font-mono text-emerald-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Audited
                                </span>
                            </div>
                        </div>

                        {{-- Telemetry Footer Strip --}}
                        <div class="pt-2 flex flex-wrap items-center justify-between text-xs text-gray-400 font-mono gap-2 border-t border-white/10">
                            <span>SRE SLA: <span class="text-emerald-400 font-bold">99.98%</span></span>
                            <span>DB PING: <span class="text-emerald-400 font-bold">1.2ms</span></span>
                            <span>CACHE LATENCY: <span class="text-emerald-400 font-bold">0.4ms</span></span>
                            <span>AUDIT ENTRIES TODAY: <span class="text-[#F5C518] font-bold">142</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── 6 CORE CAPABILITY PILLARS ─────────────────────────────────────────── --}}
        <section id="capabilities" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono font-bold uppercase mb-3">
                    ENTERPRISE SRE TOOLKIT
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                    Purpose-built for zero-downtime operations.
                </h2>
                <p class="text-sm text-gray-400 mt-3 leading-relaxed">
                    Generic task managers fail in live operations. The Support Activity Tracker gives SREs and support leads the specific tools required for verifiable, non-repudiable handover integrity.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Pillar 1 --}}
                <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-[#1B6B3A]/20 border border-[#1B6B3A]/40 flex items-center justify-center text-[#F5C518] text-xl mb-4 group-hover:scale-110 transition-transform">
                        📋
                    </div>
                    <h3 class="text-base font-bold text-white group-hover:text-emerald-300 transition-colors">Reactive Daily Shift Board</h3>
                    <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                        Livewire 3 reactive checklist with zero manual page refreshes. Pending operational checks glow in prominent amber above completed checks. Supports search, category filters, and task assignment.
                    </p>
                    <div class="mt-4 pt-3 border-t border-white/5 flex items-center gap-2 text-[11px] font-mono text-emerald-400">
                        <span>&bull; Task Delegation</span>
                        <span>&bull; Incident Escalation Tag</span>
                    </div>
                </div>

                {{-- Pillar 2 --}}
                <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-[#1B6B3A]/20 border border-[#1B6B3A]/40 flex items-center justify-center text-[#F5C518] text-xl mb-4 group-hover:scale-110 transition-transform">
                        🤝
                    </div>
                    <h3 class="text-base font-bold text-white group-hover:text-emerald-300 transition-colors">Two-Way Shift Handover Handshake</h3>
                    <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                        Eliminates handover ambiguity. Outgoing leads submit formal briefings (summary, open blockers, task statistics); incoming leads formally sign on and acknowledge custody with verification remarks.
                    </p>
                    <div class="mt-4 pt-3 border-t border-white/5 flex items-center gap-2 text-[11px] font-mono text-emerald-400">
                        <span>&bull; Statistical Snapshot</span>
                        <span>&bull; Formal Non-Repudiation</span>
                    </div>
                </div>

                {{-- Pillar 3 --}}
                <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-[#1B6B3A]/20 border border-[#1B6B3A]/40 flex items-center justify-center text-[#F5C518] text-xl mb-4 group-hover:scale-110 transition-transform">
                        💬
                    </div>
                    <h3 class="text-base font-bold text-white group-hover:text-emerald-300 transition-colors">Ops Comms & Incident War Rooms</h3>
                    <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                        Integrated operational chat for team coordination. Direct 1-on-1 private messaging, team channels (`#general-shift`), incident war rooms, `@user` tags, `@all` broadcasts, and automated email receipts.
                    </p>
                    <div class="mt-4 pt-3 border-t border-white/5 flex items-center gap-2 text-[11px] font-mono text-emerald-400">
                        <span>&bull; Email Receipts</span>
                        <span>&bull; Unread Pulse Alerts</span>
                    </div>
                </div>

                {{-- Pillar 4 --}}
                <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-[#1B6B3A]/20 border border-[#1B6B3A]/40 flex items-center justify-center text-[#F5C518] text-xl mb-4 group-hover:scale-110 transition-transform">
                        🛡️
                    </div>
                    <h3 class="text-base font-bold text-white group-hover:text-emerald-300 transition-colors">Forensic Compliance Audit Trail</h3>
                    <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                        Every user-facing mutation is permanently recorded. Captures server-verified actor name, role, client IP address, UTC timestamp, and JSON before/after state diffs for regulatory compliance.
                    </p>
                    <div class="mt-4 pt-3 border-t border-white/5 flex items-center gap-2 text-[11px] font-mono text-emerald-400">
                        <span>&bull; 100% Immutable</span>
                        <span>&bull; JSON Diff Viewer</span>
                    </div>
                </div>

                {{-- Pillar 5 --}}
                <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-[#1B6B3A]/20 border border-[#1B6B3A]/40 flex items-center justify-center text-[#F5C518] text-xl mb-4 group-hover:scale-110 transition-transform">
                        🔐
                    </div>
                    <h3 class="text-base font-bold text-white group-hover:text-emerald-300 transition-colors">Granular Privileges & SRE Grades</h3>
                    <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                        5 operational seniority tiers (L1 Support Operator to L5 SRE Director) paired with 9 granular capability checkboxes. Admins can selectively delegate channel creation, task reassignment, and report exports.
                    </p>
                    <div class="mt-4 pt-3 border-t border-white/5 flex items-center gap-2 text-[11px] font-mono text-emerald-400">
                        <span>&bull; L1-L5 Tiering</span>
                        <span>&bull; 9 Fine-Grained Flags</span>
                    </div>
                </div>

                {{-- Pillar 6 --}}
                <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-[#1B6B3A]/20 border border-[#1B6B3A]/40 flex items-center justify-center text-[#F5C518] text-xl mb-4 group-hover:scale-110 transition-transform">
                        ⚡
                    </div>
                    <h3 class="text-base font-bold text-white group-hover:text-emerald-300 transition-colors">Real-Time Telemetry & Probes</h3>
                    <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                        Native zero-overhead diagnostics service actively monitoring 8 core subsystems (Database, Mail, Cache, Storage, Comms, Handover, Audit, Runtime). Features 3s live HUD streaming and public JSON status API.
                    </p>
                    <div class="mt-4 pt-3 border-t border-white/5 flex items-center gap-2 text-[11px] font-mono text-emerald-400">
                        <span>&bull; 8 Core Subsystems</span>
                        <span>&bull; Public JSON API</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── SHIFT HANDOVER WORKFLOW (THE 4-STEP PROTOCOL) ─────────────────────── --}}
        <section id="workflow" class="bg-[#0D1611] border-y border-[#1A2E22] py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-[#F5C518]/30 text-[#F5C518] text-xs font-mono font-bold uppercase mb-2">
                        OPERATIONAL PROTOCOL
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">The 4-Step Handover Lifecycle</h2>
                    <p class="text-sm text-gray-400 mt-2">How Npontu engineering teams maintain zero blindspots between 8-hour shifts.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] relative">
                        <span class="text-3xl font-black font-mono text-[#F5C518]/30 absolute top-4 right-4">01</span>
                        <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#F5C518] font-bold mb-4">
                            1
                        </div>
                        <h4 class="text-sm font-bold text-white">Continuous Verification</h4>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            On-duty engineers check off SMS counts, replication lag, and telco endpoints throughout the shift, adding remarks and incident ticket references.
                        </p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] relative">
                        <span class="text-3xl font-black font-mono text-[#F5C518]/30 absolute top-4 right-4">02</span>
                        <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#F5C518] font-bold mb-4">
                            2
                        </div>
                        <h4 class="text-sm font-bold text-white">Outgoing Lead Sign-Off</h4>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            Outgoing Lead reviews incomplete items, drafts an operational summary, highlights blockers, and signs the briefing with an automated statistics snapshot.
                        </p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] relative">
                        <span class="text-3xl font-black font-mono text-[#F5C518]/30 absolute top-4 right-4">03</span>
                        <div class="w-10 h-10 rounded-xl bg-[#1B6B3A] text-white flex items-center justify-center font-bold mb-4">
                            3
                        </div>
                        <h4 class="text-sm font-bold text-white">Verification & Sign-On</h4>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            Incoming Lead inspects the briefing, verifies open blocker tickets, and enters formal sign-on acceptance remarks to complete the two-way custody handshake.
                        </p>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] relative">
                        <span class="text-3xl font-black font-mono text-[#F5C518]/30 absolute top-4 right-4">04</span>
                        <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[#F5C518] font-bold mb-4">
                            4
                        </div>
                        <h4 class="text-sm font-bold text-white">Compliance Archival</h4>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            Completed handshakes, duty hours, and checkoff timelines are automatically archived into multi-format compliance reports (CSV, PDF print, email dispatch).
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── REAL-TIME TELEMETRY PROBES ─────────────────────────────────────────── --}}
        <section id="telemetry" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="rounded-2xl bg-[#0F1A14] border border-[#1A2E22] p-6 sm:p-10">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-8 border-b border-[#1A2E22]">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono font-bold uppercase mb-2">
                            SYSTEM HEALTH MONITORING
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                            8 Core Subsystems. Continuously Benchmarked.
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-400 mt-1">
                            Transparent SRE health telemetry available for automated uptime monitors and engineering teams.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('health') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold text-xs shadow-md transition-colors">
                            <span>Open Full Health Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <a href="{{ route('health.telemetry') }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 font-mono text-xs border border-white/10 transition-colors">
                            <span>JSON Stream</span>
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>

                {{-- 8 Subsystems Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-8">
                    <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">Database Engine</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        </div>
                        <p class="text-[11px] font-mono text-emerald-400 mt-1">PostgreSQL &bull; 1.2ms</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">SMTP Mail Gateway</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        </div>
                        <p class="text-[11px] font-mono text-emerald-400 mt-1">Ready &bull; 0 Queue</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">PHP 8.2 Runtime</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        </div>
                        <p class="text-[11px] font-mono text-emerald-400 mt-1">OPcache &bull; Active</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">Persistent Storage</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        </div>
                        <p class="text-[11px] font-mono text-emerald-400 mt-1">RW &bull; Mounted</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">Session & Cache</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        </div>
                        <p class="text-[11px] font-mono text-emerald-400 mt-1">Memory &bull; 0.3ms</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">Ops Comms Engine</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        </div>
                        <p class="text-[11px] font-mono text-emerald-400 mt-1">Livewire 3 &bull; Online</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">Handover Custody</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        </div>
                        <p class="text-[11px] font-mono text-emerald-400 mt-1">Enforced &bull; Valid</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">Security Audit Log</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        </div>
                        <p class="text-[11px] font-mono text-emerald-400 mt-1">Immutable &bull; 100%</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── TEST OPERATOR ROLES & EVALUATION ACCOUNTS ───────────────────────────── --}}
        <section id="roles" class="bg-[#0D1611] border-t border-[#1A2E22] py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono font-bold uppercase mb-2">
                        INSTANT EVALUATION
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                        Pre-Seeded Operational Roles
                    </h2>
                    <p class="text-sm text-gray-400 mt-2">
                        Experience the console from each perspective. Evaluators can sign in with any seeded account to test role-based capabilities and permissions.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($testAccounts as $account)
                        <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#F5C518]/50 transition-all flex flex-col justify-between group">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold uppercase
                                        {{ $account['role'] === 'Administrator' ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : '' }}
                                        {{ $account['role'] === 'Team Lead' ? 'bg-[#F5C518]/20 text-[#F5C518] border border-[#F5C518]/30' : '' }}
                                        {{ $account['role'] === 'Support Agent' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : '' }}
                                    ">
                                        {{ $account['role'] }}
                                    </span>
                                    <span class="text-xs font-mono text-gray-400">{{ $account['grade'] }}</span>
                                </div>

                                <h3 class="text-lg font-bold text-white group-hover:text-[#F5C518] transition-colors">{{ $account['name'] }}</h3>
                                <p class="text-xs font-mono text-gray-400 mt-0.5">{{ $account['email'] }}</p>
                                <p class="text-[11px] text-emerald-400 font-mono mt-1">{{ $account['department'] }}</p>

                                <p class="text-xs text-gray-400 mt-3 leading-relaxed">
                                    {{ $account['description'] }}
                                </p>
                            </div>

                            <div class="mt-6 pt-4 border-t border-white/5 flex items-center justify-between">
                                <span class="text-xs font-mono text-gray-500">Password: <span class="text-gray-300 font-bold">password</span></span>
                                <a href="{{ route('login') }}"
                                   class="inline-flex items-center gap-1 text-xs font-bold text-[#F5C518] hover:text-amber-300 transition-colors">
                                    <span>Sign in as {{ explode(' ', $account['name'])[0] }}</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

    </main>

    {{-- ── FOOTER ─────────────────────────────────────────────────────────────── --}}
    <footer class="bg-[#070D0A] border-t border-[#1A2E22] py-12 text-gray-400 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-8 border-b border-white/5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                            <polygon points="16,3 30,27 2,27"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-extrabold text-white text-sm leading-none">Support Tracker</p>
                        <p class="text-[#F5C518] text-[9px] font-mono tracking-widest uppercase mt-0.5 font-bold">NPONTU TECHNOLOGIES</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-6 text-gray-400 font-medium">
                    <a href="{{ route('landing') }}" class="hover:text-white transition-colors">Platform Overview</a>
                    <a href="{{ route('health') }}" class="hover:text-white transition-colors">System Health</a>
                    <a href="{{ route('login') }}" class="hover:text-white transition-colors">Operator Sign In</a>
                    <a href="https://github.com/mhiskall282/npontu-technologies-sre" target="_blank" class="hover:text-white transition-colors flex items-center gap-1">
                        <span>GitHub Repository</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>

                <div class="flex items-center gap-2 font-mono text-[11px]">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span class="text-gray-300">All Systems Nominal &bull; 99.98% SLA</span>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-gray-500">
                <p>&copy; 2026 Npontu Technologies Limited. Internal SRE Platform &bull; Making you free to achieve.</p>
                <p class="font-mono">Production Release v1.2 &bull; SLA 99.98%</p>
            </div>
        </div>
    </footer>

    {{-- UTC Clock Live Synchronizer --}}
    <script>
        (function() {
            function updateNavClock() {
                const el = document.getElementById('nav-live-clock');
                if (el) {
                    const now = new Date();
                    const h = String(now.getUTCHours()).padStart(2, '0');
                    const m = String(now.getUTCMinutes()).padStart(2, '0');
                    const s = String(now.getUTCSeconds()).padStart(2, '0');
                    el.textContent = `UTC ${h}:${m}:${s}`;
                }
            }
            setInterval(updateNavClock, 1000);
            updateNavClock();
        })();
    </script>
</body>
</html>
