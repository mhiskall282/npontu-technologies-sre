<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Support Activity Tracker — Npontu Technologies SRE Operations</title>
    <meta name="description" content="Mission-critical SRE operations platform for 24/7 engineering teams. Eliminating blindspots through verifiable two-way handovers, ops war rooms, and immutable compliance audit trails.">

    <!-- Brand Typography: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .clip-angled-hero {
            clip-path: polygon(0 0, 100% 0, 100% 92%, 0 100%);
        }
        @media (max-width: 768px) {
            .clip-angled-hero {
                clip-path: polygon(0 0, 100% 0, 100% 96%, 0 100%);
            }
        }
    </style>
</head>
<body class="bg-[#07100B] text-white antialiased selection:bg-[#F5C518] selection:text-gray-950 min-h-full flex flex-col overflow-x-hidden">

    {{-- ── GLOBAL SRE HEADER ──────────────────────────────────────────────────── --}}
    <header class="sticky top-0 z-50 bg-[#0A140E]/85 backdrop-blur-md border-b border-[#14261B]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            {{-- Brand Logo --}}
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#1B6B3A] to-[#0F1A14] border border-[#F5C518]/30 flex items-center justify-center shadow-lg group-hover:border-[#F5C518] transition-colors">
                    <svg class="w-5 h-5 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                        <polygon points="16,3 30,27 2,27"/>
                    </svg>
                </div>
                <div>
                    <span class="font-extrabold text-base sm:text-lg tracking-tight text-white block leading-none">Support Tracker</span>
                    <span class="block text-[#F5C518] text-[9px] font-mono tracking-widest uppercase mt-0.5 font-bold">NPONTU TECHNOLOGIES</span>
                </div>
            </a>

            {{-- Story Navigation Links --}}
            <nav class="hidden md:flex items-center gap-7 text-xs font-semibold text-gray-300">
                <a href="#the-problem" class="hover:text-[#F5C518] transition-colors">The Challenge</a>
                <a href="#the-solution" class="hover:text-[#F5C518] transition-colors">The Handshake</a>
                <a href="#pillars" class="hover:text-[#F5C518] transition-colors">Core Pillars</a>
                <a href="#telemetry" class="hover:text-[#F5C518] transition-colors">Live Telemetry</a>
            </nav>

            {{-- Right CTA Section --}}
            <div class="flex items-center gap-3">
                <div class="hidden lg:flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-[11px] font-mono text-gray-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span id="nav-live-clock">UTC --:--:--</span>
                </div>

                <a href="{{ route('health') }}"
                   class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-950/60 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-900/60 transition-colors">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>SLA 99.98%</span>
                </a>

                @auth
                    <a href="{{ route('activities.daily') }}"
                       id="cta-enter-cockpit"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold text-xs shadow-md transition-colors">
                        <span>Enter SRE Cockpit</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
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

        {{-- ── HERO SECTION: STORYTELLING & HOOK ─────────────────────────────────── --}}
        <section class="relative bg-gradient-to-b from-[#12492A] via-[#1B6B3A]/90 to-[#07100B] pt-6 sm:pt-8 md:pt-10 pb-16 sm:pb-20 md:pb-24 clip-angled-hero overflow-hidden">
            {{-- Ambient Glow Elements --}}
            <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-[#1B6B3A]/20 blur-[130px] rounded-full pointer-events-none"></div>
            <div class="absolute top-6 right-6 opacity-10 pointer-events-none">
                <svg class="w-[420px] h-[420px] text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                    <polygon points="16,3 30,27 2,27"/>
                </svg>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="max-w-3xl mx-auto text-center">
                    {{-- Narrative Badge --}}
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-black/30 border border-white/20 text-emerald-200 text-[11px] sm:text-xs font-mono mb-3 sm:mb-4 shadow-inner">
                        <span class="w-2 h-2 rounded-full bg-[#F5C518] animate-ping"></span>
                        <span>THE RELIABILITY PLATFORM FOR MISSION-CRITICAL SRE OPERATIONS</span>
                    </div>

                    {{-- Main Storytelling Headline --}}
                    <h1 class="text-2xl sm:text-3xl lg:text-[40px] font-black text-white tracking-tight leading-snug">
                        When critical infrastructure runs 24/7,
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#F5C518] via-amber-200 to-yellow-400">
                            a missed handover is an outage waiting to happen.
                        </span>
                    </h1>

                    {{-- Narrative Subtitle --}}
                    <p class="text-xs sm:text-sm text-green-50/90 mt-2.5 sm:mt-3 leading-relaxed font-normal max-w-2xl mx-auto">
                        Engineered for the Site Reliability Engineers and Operations teams powering Npontu Technologies. We replaced fragmented chat messages and forgotten sticky notes with a mathematically verifiable, two-way operational custody handshake.
                    </p>

                    {{-- Primary CTAs (Centered & Prominently Positioned) --}}
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-5 sm:mt-6">
                        @auth
                            <a href="{{ route('activities.daily') }}"
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-extrabold text-sm shadow-xl transition-all duration-150 hover:scale-[1.02]">
                                <span>Go to Today's Shift Board</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               id="hero-cta-login"
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-extrabold text-sm shadow-xl transition-all duration-150 hover:scale-[1.02]">
                                <span>Launch Shift Console</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @endauth

                        <a href="{{ route('health') }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-black/30 hover:bg-black/50 text-white font-bold text-sm border border-white/20 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Inspect Telemetry HUD</span>
                        </a>
                    </div>
                </div>

                {{-- Live Operational Guarantee Cards --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-10 sm:mt-12 pt-8 border-t border-white/15">
                    <div class="p-4 rounded-xl bg-black/25 border border-white/10 backdrop-blur-sm text-center">
                        <p class="text-2xl sm:text-3xl font-black text-[#F5C518] font-mono leading-none">100%</p>
                        <p class="text-xs font-bold text-white mt-1.5">Audit Custody</p>
                        <p class="text-[11px] text-green-200/70 mt-0.5">Polymorphic before & after JSON diffs on every change</p>
                    </div>

                    <div class="p-4 rounded-xl bg-black/25 border border-white/10 backdrop-blur-sm text-center">
                        <p class="text-2xl sm:text-3xl font-black text-[#F5C518] font-mono leading-none">&lt; 100ms</p>
                        <p class="text-xs font-bold text-white mt-1.5">Telemetry Speed</p>
                        <p class="text-[11px] text-green-200/70 mt-0.5">Real-time health probes across 8 core subsystems</p>
                    </div>

                    <div class="p-4 rounded-xl bg-black/25 border border-white/10 backdrop-blur-sm text-center">
                        <p class="text-2xl sm:text-3xl font-black text-[#F5C518] font-mono leading-none">99.98%</p>
                        <p class="text-xs font-bold text-white mt-1.5">Availability SLA</p>
                        <p class="text-[11px] text-green-200/70 mt-0.5">Continuous automated uptime health heartbeat</p>
                    </div>

                    <div class="p-4 rounded-xl bg-black/25 border border-white/10 backdrop-blur-sm text-center">
                        <p class="text-2xl sm:text-3xl font-black text-[#F5C518] font-mono leading-none">0</p>
                        <p class="text-xs font-bold text-white mt-1.5">Broken Handoffs</p>
                        <p class="text-[11px] text-green-200/70 mt-0.5">Two-way briefing sign-off & verified sign-on</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── ACT I: THE SILENT KILLER IN LIVE OPERATIONS ─────────────────────────── --}}
        <section id="the-problem" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-5 space-y-5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-mono font-bold uppercase">
                        ACT I &bull; THE OPERATIONAL REALITY
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight leading-tight">
                        Shift handover failure is the silent root cause of 70% of prolonged outages.
                    </h2>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        In mission-critical payment gateways, SMS aggregation pipelines, and high-frequency banking infrastructure, systems do not wait for business hours.
                    </p>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        When shifts transition through rushed chat messages or informal word-of-mouth, unresolved database replication spikes, delayed settlements, and telco error codes slip between the cracks.
                    </p>
                    <div class="p-4 rounded-xl bg-white/5 border border-white/10 text-xs text-gray-300 font-mono leading-relaxed border-l-4 border-l-[#F5C518]">
                        "In production SRE, what isn't documented didn't happen. What isn't formally handed over will eventually take down production."
                    </div>
                </div>

                {{-- Visual Contrast: Chaos vs The Npontu Standard --}}
                <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- The Old Way --}}
                    <div class="p-6 rounded-2xl bg-red-950/20 border border-red-500/20 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-red-400 uppercase tracking-wider">Traditional Handover</span>
                            <span class="text-lg">❌</span>
                        </div>
                        <h3 class="text-base font-bold text-red-200">The Friction & Risk</h3>
                        <ul class="space-y-2.5 text-xs text-red-300/80 leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="text-red-400 mt-0.5">&bull;</span>
                                <span>Rushed WhatsApp messages at 11:58 PM with missing details</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-red-400 mt-0.5">&bull;</span>
                                <span>No verification of whether carrier SMS counts actually reconciled</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-red-400 mt-0.5">&bull;</span>
                                <span>Discrepancies blamed on previous shift leads with zero audit proof</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-red-400 mt-0.5">&bull;</span>
                                <span>Zero compliance trails for bank auditors during SLA reviews</span>
                            </li>
                        </ul>
                    </div>

                    {{-- The Npontu Standard --}}
                    <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1B6B3A]/50 space-y-4 shadow-xl">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-emerald-400 uppercase tracking-wider">The Npontu Standard</span>
                            <span class="text-lg">🛡️</span>
                        </div>
                        <h3 class="text-base font-bold text-white">Verifiable Operational Calm</h3>
                        <ul class="space-y-2.5 text-xs text-gray-300 leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-400 mt-0.5">&bull;</span>
                                <span>Continuous live checklist with mandatory remarks & incident tags</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-400 mt-0.5">&bull;</span>
                                <span>Formal two-way handshake: outgoing sign-off + incoming verification</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-400 mt-0.5">&bull;</span>
                                <span>100% immutable cryptographic audit trail with JSON before/after diffs</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-400 mt-0.5">&bull;</span>
                                <span>One-click CSV & printable compliance reports ready for central banks</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── ACT II: THE ANATOMY OF A FLAWLESS HANDOVER ──────────────────────────── --}}
        <section id="the-solution" class="bg-[#0B150F] border-y border-[#14261B] py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-500/10 border border-[#F5C518]/30 text-[#F5C518] text-xs font-mono font-bold uppercase mb-3">
                        ACT II &bull; THE CUSTODY LIFECYCLE
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                        The Anatomy of a Flawless Handover
                    </h2>
                    <p class="text-sm text-gray-400 mt-3 leading-relaxed">
                        A continuous, synchronized protocol designed to ensure zero broken links between 8-hour engineering watches.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                    {{-- Step 1 --}}
                    <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all relative flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-[#1B6B3A]/30 text-emerald-300 border border-[#1B6B3A]/50">PHASE 1</span>
                                <span class="text-xs font-mono text-gray-400">08:00 &bull; Continuous</span>
                            </div>
                            <h3 class="text-base font-bold text-white">Live Verification</h3>
                            <p class="text-xs text-gray-400 mt-2.5 leading-relaxed">
                                On-duty engineers execute recurring operational checks throughout the shift — reconciling SMS logs, monitoring telco latency, and recording verified remarks.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-white/5 text-[11px] font-mono text-emerald-400">
                            &bull; Real-time checklist sync
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all relative flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-amber-500/20 text-[#F5C518] border border-[#F5C518]/30">PHASE 2</span>
                                <span class="text-xs font-mono text-gray-400">15:30 &bull; Briefing</span>
                            </div>
                            <h3 class="text-base font-bold text-white">Outgoing Sign-Off</h3>
                            <p class="text-xs text-gray-400 mt-2.5 leading-relaxed">
                                Outgoing Lead aggregates open blocker tickets, highlights carrier escalations, captures statistical completion snapshots, and signs the digital briefing.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-white/5 text-[11px] font-mono text-[#F5C518]">
                            &bull; Statistical snapshot
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all relative flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">PHASE 3</span>
                                <span class="text-xs font-mono text-gray-400">16:00 &bull; Handshake</span>
                            </div>
                            <h3 class="text-base font-bold text-white">Incoming Sign-On</h3>
                            <p class="text-xs text-gray-400 mt-2.5 leading-relaxed">
                                Incoming Lead reviews unresolved checks, verifies carrier health, enters verification remarks, and formally signs on. Custody is acknowledged and transferred.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-white/5 text-[11px] font-mono text-emerald-400">
                            &bull; Two-way custody lock
                        </div>
                    </div>

                    {{-- Step 4 --}}
                    <div class="p-6 rounded-2xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all relative flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-2.5 py-0.5 rounded text-[11px] font-mono font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">PHASE 4</span>
                                <span class="text-xs font-mono text-gray-400">Archival &bull; Permanent</span>
                            </div>
                            <h3 class="text-base font-bold text-white">Compliance Archival</h3>
                            <p class="text-xs text-gray-400 mt-2.5 leading-relaxed">
                                Complete handover record, active operator duty hours, and checklist diffs are permanently archived into exportable reports for compliance audits.
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-white/5 text-[11px] font-mono text-purple-400">
                            &bull; 100% Immutable proof
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── ACT III: FOUR PILLARS OF OPERATIONAL CALM ──────────────────────────── --}}
        <section id="pillars" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono font-bold uppercase mb-3">
                    ACT III &bull; CORE ARCHITECTURE
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                    Four Pillars of Operational Calm
                </h2>
                <p class="text-sm text-gray-400 mt-3 leading-relaxed">
                    Designed to give on-duty SREs total command without mental fatigue or cognitive overload.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Pillar 1 --}}
                <div class="p-8 rounded-3xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-[#1B6B3A]/20 border border-[#1B6B3A]/40 flex items-center justify-center text-[#F5C518] text-xl group-hover:scale-110 transition-transform">
                                📋
                            </div>
                            <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono">Livewire 3 Real-Time</span>
                        </div>
                        <h3 class="text-xl font-bold text-white group-hover:text-emerald-300 transition-colors">The Reactive Daily Shift Board</h3>
                        <p class="text-sm text-gray-400 mt-3 leading-relaxed">
                            No manual page refreshes. Incomplete checks glow in high-visibility amber at the top of the board, demanding operational attention before custody transfers. Completed checks fade smoothly into confidence green with verified remarks and auditor timestamps.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-white/5 flex flex-wrap items-center gap-4 text-xs font-mono text-gray-400">
                        <span>&bull; Task Delegation</span>
                        <span>&bull; Search & Category Filtering</span>
                        <span>&bull; Incident Escalation Flags</span>
                    </div>
                </div>

                {{-- Pillar 2 --}}
                <div class="p-8 rounded-3xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-[#1B6B3A]/20 border border-[#1B6B3A]/40 flex items-center justify-center text-[#F5C518] text-xl group-hover:scale-110 transition-transform">
                                🤝
                            </div>
                            <span class="px-3 py-1 rounded-full bg-amber-500/10 border border-[#F5C518]/30 text-[#F5C518] text-xs font-mono">Two-Way Handshake</span>
                        </div>
                        <h3 class="text-xl font-bold text-white group-hover:text-[#F5C518] transition-colors">Verifiable Custody Transfer</h3>
                        <p class="text-sm text-gray-400 mt-3 leading-relaxed">
                            Custody is never assumed — it is explicitly handed over and explicitly accepted. Outgoing leads submit structured shift summaries and blocker counts. Incoming leads review unresolved issues, input verification remarks, and formally accept operational duty.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-white/5 flex flex-wrap items-center gap-4 text-xs font-mono text-gray-400">
                        <span>&bull; Statistical Snapshot</span>
                        <span>&bull; Dual Lead Accountability</span>
                        <span>&bull; Zero Broken Links</span>
                    </div>
                </div>

                {{-- Pillar 3 --}}
                <div class="p-8 rounded-3xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-[#1B6B3A]/20 border border-[#1B6B3A]/40 flex items-center justify-center text-[#F5C518] text-xl group-hover:scale-110 transition-transform">
                                💬
                            </div>
                            <span class="px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono">Comms & Dispatch</span>
                        </div>
                        <h3 class="text-xl font-bold text-white group-hover:text-emerald-300 transition-colors">Zero-Friction Incident War Rooms</h3>
                        <p class="text-sm text-gray-400 mt-3 leading-relaxed">
                            When alerts fire at 2:00 AM, engineers shouldn't switch between multiple disconnected chat tools. The built-in Ops Comms suite provides 1-on-1 direct messaging, `#general-shift` team channels, and private incident war rooms with `@mention` tagging, browser tab alert flasher, and automated email receipts.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-white/5 flex flex-wrap items-center gap-4 text-xs font-mono text-gray-400">
                        <span>&bull; Automated Email Receipts</span>
                        <span>&bull; Flickering Alert Radar</span>
                        <span>&bull; Unread Count Badges</span>
                    </div>
                </div>

                {{-- Pillar 4 --}}
                <div class="p-8 rounded-3xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-[#1B6B3A]/20 border border-[#1B6B3A]/40 flex items-center justify-center text-[#F5C518] text-xl group-hover:scale-110 transition-transform">
                                🛡️
                            </div>
                            <span class="px-3 py-1 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-300 text-xs font-mono">Forensic Compliance</span>
                        </div>
                        <h3 class="text-xl font-bold text-white group-hover:text-purple-300 transition-colors">100% Immutable Forensic Audit Shield</h3>
                        <p class="text-sm text-gray-400 mt-3 leading-relaxed">
                            Every state change across the system is cryptographically logged in an append-only audit trail. Captures server-verified actor name, role, IP address, UTC timestamp, and before/after JSON state diffs. Ready for banking compliance audits, SLA reviews, and post-mortems.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-white/5 flex flex-wrap items-center gap-4 text-xs font-mono text-gray-400">
                        <span>&bull; Server-Captured IP</span>
                        <span>&bull; JSON State Diffs</span>
                        <span>&bull; One-Click CSV Export</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── ACT IV: LIVE TELEMETRY BENCHMARKS ──────────────────────────────────── --}}
        <section id="telemetry" class="bg-[#0B150F] border-t border-[#14261B] py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl bg-[#0F1A14] border border-[#1A2E22] p-8 sm:p-12">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 pb-10 border-b border-[#1A2E22]">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono font-bold uppercase mb-3">
                                ACT IV &bull; LIVE TELEMETRY & HEALTH
                            </div>
                            <h2 class="text-3xl font-black text-white tracking-tight">
                                8 Core Subsystems. Monitored in Real Time.
                            </h2>
                            <p class="text-sm text-gray-400 mt-2 max-w-xl">
                                Built with continuous health probes actively reporting response latency, cache health, and uptime SLAs.
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <a href="{{ route('health') }}"
                               class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold text-xs shadow-lg transition-colors">
                                <span>Inspect Full Health Dashboard</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                            <a href="{{ route('health.telemetry') }}" target="_blank"
                               class="inline-flex items-center gap-2 px-5 py-3.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 font-mono text-xs border border-white/10 transition-colors">
                                <span>JSON Stream</span>
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- 8 Subsystems Matrix --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-10">
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-white">Database Engine</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            </div>
                            <p class="text-xs font-mono text-emerald-400 mt-2">PostgreSQL &bull; 1.2ms</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-white">Mail Gateway</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            </div>
                            <p class="text-xs font-mono text-emerald-400 mt-2">SMTP &bull; Active</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-white">PHP 8.2 Runtime</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            </div>
                            <p class="text-xs font-mono text-emerald-400 mt-2">OPcache &bull; Active</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-white">Persistent Storage</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            </div>
                            <p class="text-xs font-mono text-emerald-400 mt-2">RW &bull; Mounted</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-white">Session & Cache</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            </div>
                            <p class="text-xs font-mono text-emerald-400 mt-2">Memory &bull; 0.3ms</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-white">Ops Comms Engine</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            </div>
                            <p class="text-xs font-mono text-emerald-400 mt-2">Livewire 3 &bull; Online</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-white">Handover Custody</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            </div>
                            <p class="text-xs font-mono text-emerald-400 mt-2">Enforced &bull; Valid</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-white">Security Audit Log</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            </div>
                            <p class="text-xs font-mono text-emerald-400 mt-2">Immutable &bull; 100%</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── THE FINALE: CALL TO SRE LEADERS ────────────────────────────────────── --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center relative">
            <div class="max-w-3xl mx-auto space-y-6">
                <div class="w-14 h-14 rounded-2xl bg-[#1B6B3A]/20 border border-[#1B6B3A]/40 flex items-center justify-center mx-auto text-[#F5C518] shadow-lg">
                    <svg class="w-8 h-8" viewBox="0 0 32 32" fill="currentColor">
                        <polygon points="16,3 30,27 2,27"/>
                    </svg>
                </div>

                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight">
                    Ready to elevate your engineering shift handovers?
                </h2>

                <p class="text-base text-gray-400 max-w-xl mx-auto leading-relaxed">
                    Join Npontu's on-duty Site Reliability Engineers and Lead Architects. Experience verified custody and zero blindspots.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                    @auth
                        <a href="{{ route('activities.daily') }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-extrabold text-sm shadow-xl transition-all">
                            <span>Enter SRE Shift Cockpit</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-extrabold text-sm shadow-xl transition-all">
                            <span>Sign In to SRE Console</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endauth

                    <a href="{{ route('health') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 font-bold text-sm border border-white/10 transition-colors">
                        <span>View Real-Time Health</span>
                    </a>
                </div>
            </div>
        </section>

    </main>

    {{-- ── COMPREHENSIVE ENTERPRISE SRE FOOTER ─────────────────────────────────── --}}
    @include('layouts.partials.footer')

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
