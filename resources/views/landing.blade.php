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
    <header class="sticky top-0 z-50 bg-[#0A140E]/90 backdrop-blur-md border-b border-[#14261B]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            {{-- Brand Logo --}}
            <a href="{{ route('landing') }}" class="flex items-center gap-2.5 sm:gap-3 group shrink-0">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#1B6B3A] to-[#0F1A14] border border-[#F5C518]/30 flex items-center justify-center shadow-lg group-hover:border-[#F5C518] transition-colors shrink-0">
                    <svg class="w-5 h-5 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                        <polygon points="16,3 30,27 2,27"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="font-extrabold text-sm sm:text-base tracking-tight text-white block leading-none truncate">Support Tracker</span>
                    <span class="block text-[#F5C518] text-[8px] sm:text-[9px] font-mono tracking-widest uppercase mt-0.5 font-bold truncate">NPONTU TECHNOLOGIES</span>
                </div>
            </a>

            {{-- Story Navigation Links (Desktop) --}}
            <nav class="hidden md:flex items-center gap-5 text-xs font-semibold text-gray-300">
                <a href="#the-problem" class="hover:text-[#F5C518] transition-colors">The Challenge</a>
                <a href="#the-solution" class="hover:text-[#F5C518] transition-colors">The Handshake</a>
                <a href="#pillars" class="hover:text-[#F5C518] transition-colors">Pillars</a>
                <a href="#audiences" class="hover:text-[#F5C518] transition-colors">For Every Role</a>
                <a href="#mobile" class="hover:text-[#F5C518] transition-colors flex items-center gap-1.5">
                    <span>Mobile App</span>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">APK</span>
                </a>
                <a href="#telemetry" class="hover:text-[#F5C518] transition-colors">Telemetry</a>
                <a href="{{ route('docs') }}" class="text-[#F5C518] hover:underline transition-colors font-bold">Docs</a>
            </nav>

            {{-- Right CTA Section --}}
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
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
                       class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 rounded-xl bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold text-xs shadow-md transition-colors whitespace-nowrap">
                        <span>Enter SRE Cockpit</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       id="cta-nav-login"
                       class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-bold text-xs shadow-md transition-colors whitespace-nowrap">
                        <span>Sign In</span>
                        <svg class="w-3.5 h-3.5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </a>
                @endauth

                {{-- Mobile Hamburger Toggle Button --}}
                <button type="button"
                        id="mobile-nav-toggle-btn"
                        onclick="toggleLandingNav()"
                        aria-expanded="false"
                        aria-label="Toggle navigation menu"
                        class="md:hidden p-2 rounded-xl bg-white/5 border border-white/10 text-gray-300 hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus:ring-2 focus:ring-[#F5C518]">
                    <svg id="mobile-hamburger-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg id="mobile-close-icon" class="w-5 h-5 hidden text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile Collapsible Navigation Menu --}}
        <div id="landing-mobile-menu"
             class="hidden md:hidden bg-[#0A140E]/98 border-b border-[#14261B] px-4 pt-3 pb-5 space-y-1 shadow-2xl backdrop-blur-xl">
            <div class="px-3 pb-2 text-[10px] font-mono font-bold uppercase tracking-wider text-gray-400">
                Navigation &amp; Operations
            </div>
            <a href="#the-problem" onclick="toggleLandingNav()" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-200 hover:text-[#F5C518] hover:bg-white/5 transition-colors">
                The Challenge
            </a>
            <a href="#the-solution" onclick="toggleLandingNav()" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-200 hover:text-[#F5C518] hover:bg-white/5 transition-colors">
                The Handshake
            </a>
            <a href="#pillars" onclick="toggleLandingNav()" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-200 hover:text-[#F5C518] hover:bg-white/5 transition-colors">
                Core Pillars
            </a>
            <a href="#audiences" onclick="toggleLandingNav()" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-200 hover:text-[#F5C518] hover:bg-white/5 transition-colors">
                Stakeholder Roles (CIO, Leads, SREs)
            </a>
            <a href="#mobile" onclick="toggleLandingNav()" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-200 hover:text-[#F5C518] hover:bg-white/5 transition-colors flex items-center justify-between">
                <span>Mobile App (Android APK)</span>
                <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300">v1.1.0</span>
            </a>
            <a href="#telemetry" onclick="toggleLandingNav()" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-200 hover:text-[#F5C518] hover:bg-white/5 transition-colors">
                Live Telemetry
            </a>
            <a href="{{ route('docs') }}" class="block px-3 py-2 rounded-lg text-sm font-bold text-[#F5C518] hover:bg-white/5 transition-colors flex items-center justify-between">
                <span>Docs &amp; Architecture Guide</span>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-[#F5C518]/20 text-[#F5C518] uppercase">Full Manual</span>
            </a>
            <a href="{{ route('health') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-emerald-300 hover:bg-white/5 transition-colors flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>System Diagnostics HUD</span>
                </span>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-950 border border-emerald-500/30 text-emerald-300">99.98% SLA</span>
            </a>

            <div class="pt-3 mt-2 border-t border-white/10 flex items-center justify-between text-xs text-gray-400 px-3 font-mono">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>ACCRA-CLUSTER-01</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('policy.privacy') }}" class="hover:text-gray-200 text-[11px]">Privacy</a>
                    <span>&bull;</span>
                    <a href="{{ route('policy.terms') }}" class="hover:text-gray-200 text-[11px]">Terms</a>
                </div>
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

        {{-- ── ACT IV: PURPOSE-BUILT FOR EVERY SRE STAKEHOLDER ─────────────────────── --}}
        <section id="audiences" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 border-t border-[#14261B]">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#F5C518]/10 border border-[#F5C518]/30 text-[#F5C518] text-xs font-mono font-bold uppercase mb-3">
                    ACT IV &bull; STAKEHOLDER ALIGNMENT
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight">
                    Engineered for Every Seat in the Operations Center
                </h2>
                <p class="text-sm sm:text-base text-gray-400 mt-3 leading-relaxed">
                    From the executive boardroom to the late-night on-call pager, Support Activity Tracker eliminates friction across the entire engineering hierarchy.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Role 1: CIO / VP Engineering --}}
                <div class="p-6 rounded-3xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#F5C518]/50 transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/20 border border-[#F5C518]/30 flex items-center justify-center text-xl mb-5">
                            👔
                        </div>
                        <span class="text-[10px] font-mono uppercase font-bold tracking-widest text-[#F5C518] block mb-1">Executive Leadership</span>
                        <h3 class="text-lg font-bold text-white">CIOs &amp; VPs of Engineering</h3>
                        <p class="text-xs text-gray-400 mt-2.5 leading-relaxed">
                            Gain boardroom-grade operational assurance. Replace anecdotal status reports with quantified handover metrics, SLA uptime tracking, and enterprise risk reduction across all payment and banking workloads.
                        </p>
                    </div>
                    <ul class="mt-6 pt-4 border-t border-white/5 space-y-2 text-[11px] font-mono text-gray-300">
                        <li class="flex items-center gap-2 text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>99.98% SLA Availability HUD</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                            <span>Zero Shift Handoff Blindspots</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                            <span>Enterprise ISO 27001 Readiness</span>
                        </li>
                    </ul>
                </div>

                {{-- Role 2: Shift Leads --}}
                <div class="p-6 rounded-3xl bg-[#0F1A14] border border-[#1A2E22] hover:border-[#1B6B3A] transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-[#1B6B3A]/20 border border-[#1B6B3A]/40 flex items-center justify-center text-xl mb-5">
                            🎖️
                        </div>
                        <span class="text-[10px] font-mono uppercase font-bold tracking-widest text-emerald-400 block mb-1">Operations Command</span>
                        <h3 class="text-lg font-bold text-white">SRE Shift Leads &amp; Commanders</h3>
                        <p class="text-xs text-gray-400 mt-2.5 leading-relaxed">
                            Maintain crisp custody boundaries. Sign off formal digital briefings with blocker counts, delegate tasks to on-duty engineers in one click, and orchestrate incident War Rooms with active log feeds.
                        </p>
                    </div>
                    <ul class="mt-6 pt-4 border-t border-white/5 space-y-2 text-[11px] font-mono text-gray-300">
                        <li class="flex items-center gap-2 text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>Two-Way Cryptographic Handshake</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                            <span>1-Click Bulk Task Delegation</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                            <span>War Room Comms &amp; Mentions</span>
                        </li>
                    </ul>
                </div>

                {{-- Role 3: On-Call Engineers --}}
                <div class="p-6 rounded-3xl bg-[#0F1A14] border border-[#1A2E22] hover:border-emerald-500 transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-xl mb-5">
                            ⚡
                        </div>
                        <span class="text-[10px] font-mono uppercase font-bold tracking-widest text-emerald-300 block mb-1">On-Duty Frontline</span>
                        <h3 class="text-lg font-bold text-white">On-Call Engineers &amp; SREs</h3>
                        <p class="text-xs text-gray-400 mt-2.5 leading-relaxed">
                            Execute checkoffs without mental friction. Use the Flutter mobile app with 0ms offline startup, auto-syncing when network reconnects, instant P1 escalation tagging, and dark mode built for night shifts.
                        </p>
                    </div>
                    <ul class="mt-6 pt-4 border-t border-white/5 space-y-2 text-[11px] font-mono text-gray-300">
                        <li class="flex items-center gap-2 text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>Offline-First Mobile Cockpit</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                            <span>P1 Push Alerts &amp; Login Security</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                            <span>Auto-Scroll Live War Room Chat</span>
                        </li>
                    </ul>
                </div>

                {{-- Role 4: Compliance & Auditors --}}
                <div class="p-6 rounded-3xl bg-[#0F1A14] border border-[#1A2E22] hover:border-purple-500 transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-purple-500/20 border border-purple-500/30 flex items-center justify-center text-xl mb-5">
                            ⚖️
                        </div>
                        <span class="text-[10px] font-mono uppercase font-bold tracking-widest text-purple-300 block mb-1">Governance &amp; Trust</span>
                        <h3 class="text-lg font-bold text-white">Infosec &amp; Compliance Auditors</h3>
                        <p class="text-xs text-gray-400 mt-2.5 leading-relaxed">
                            Zero dispute post-mortems. Every check, status change, and handover briefing is immutably logged with actor snapshots, before/after JSON diffs, client IPs, and 1-click regulatory export.
                        </p>
                    </div>
                    <ul class="mt-6 pt-4 border-t border-white/5 space-y-2 text-[11px] font-mono text-gray-300">
                        <li class="flex items-center gap-2 text-purple-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                            <span>100% Immutable Audit Trail</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                            <span>Polymorphic Before/After Diffs</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                            <span>One-Click CSV / PDF Archival</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        {{-- ── ACT V: THE NPONTU SRE MOBILE COMPANION & APK DISTRIBUTION ───────────── --}}
        <section id="mobile" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 border-t border-[#14261B]">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                {{-- Left: Narrative & Downloads --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono font-bold uppercase">
                        ACT V &bull; MOBILE SRE COMPANION &bull; FLUTTER 3.24+
                    </div>

                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                        Command 24/7 Operations from the Palm of Your Hand.
                    </h2>

                    <p class="text-sm sm:text-base text-gray-300 leading-relaxed">
                        Engineered specifically for on-call SRE engineers in the field. When high-priority P1 incidents trigger or a shift handover arrives, access your personal queue, check off critical runbooks, and coordinate in incident war rooms with zero startup latency.
                    </p>

                    {{-- Architectural Highlights Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div class="p-4 rounded-2xl bg-[#0F1A14] border border-[#1A2E22]">
                            <div class="flex items-center gap-2 text-emerald-400 font-mono text-xs font-bold">
                                <span>⚡ 0ms Offline Cache Hydration</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1.5">
                                Loads immediately on app launch from local SharedPreferences without waiting for network round-trips. Auto-syncs on reconnect.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-[#0F1A14] border border-[#1A2E22]">
                            <div class="flex items-center gap-2 text-[#F5C518] font-mono text-xs font-bold">
                                <span>🔄 Real-Time War Room Polling</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1.5">
                                Active 3-second polling sync loop keeps incident messages, attachments, and alerts in lockstep with web operators.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-[#0F1A14] border border-[#1A2E22]">
                            <div class="flex items-center gap-2 text-purple-300 font-mono text-xs font-bold">
                                <span>📱 Adaptive Responsive Layout</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1.5">
                                Seamless portrait and landscape auto-rotation. Keyboard inset avoidance ensures input boxes are never obscured while typing.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-[#0F1A14] border border-[#1A2E22]">
                            <div class="flex items-center gap-2 text-emerald-300 font-mono text-xs font-bold">
                                <span>🔒 Biometric Auth &amp; Login Alert</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1.5">
                                Fingerprint and Face ID authentication. Automatically sends security email notifications and in-app audit records on mobile login.
                            </p>
                        </div>
                    </div>

                    {{-- APK Download & Testing Hub --}}
                    <div class="p-6 rounded-3xl bg-gradient-to-br from-[#0F1A14] to-[#14261B] border border-[#1B6B3A]/60 space-y-4 shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-mono font-bold text-[#F5C518] uppercase tracking-wider">Release Distribution &bull; Version 1.1.0 (Build 2)</span>
                                <h3 class="text-base font-bold text-white mt-0.5">Download Npontu SRE Android Client</h3>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-mono font-bold border border-emerald-500/30">
                                Ready for Testing
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 pt-1">
                            {{-- Universal APK Download --}}
                            <a href="https://github.com/mhiskall282/npontu-technologies-sre/actions" target="_blank"
                               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold text-xs shadow-lg transition-transform hover:scale-[1.02]">
                                <svg class="w-4 h-4 text-[#F5C518]" viewBox="0 0 24 24" fill="currentColor"><path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9993.4482.9993.9993.0001.5511-.4483.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993 0 .5511-.4482.9997-.9993.9997m11.4045-6.02l1.9973-3.4592a.416.416 0 00-.1521-.5676.416.416 0 00-.5676.1521l-2.0223 3.503C15.5902 8.414 13.8533 8.12 12 8.12s-3.5902.294-5.1368.8307L4.8409 5.4477a.416.416 0 00-.5676-.1521.4157.4157 0 00-.1521.5676l1.9973 3.4592C2.6889 11.1867.3432 14.6589 0 18.761h24c-.3432-4.1021-2.6889-7.5743-6.1185-9.4396"/></svg>
                                <span>Universal Release APK (.apk)</span>
                            </a>

                            {{-- GitHub Actions Artifacts Link --}}
                            <a href="https://github.com/mhiskall282/npontu-technologies-sre/actions/workflows/flutter-ci.yml" target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 text-gray-200 font-mono text-xs border border-white/10 transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>GitHub CI Builds &amp; Split APKs</span>
                            </a>

                            {{-- Mobile Documentation & Emulator Guide --}}
                            <a href="{{ route('docs') }}"
                               class="inline-flex items-center gap-1.5 px-4 py-3 rounded-xl text-xs font-semibold text-[#F5C518] hover:underline">
                                <span>Emulator Setup Guide &rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Right: Sleek Modern Phone Frame Mockup --}}
                <div class="lg:col-span-5 flex justify-center">
                    <div class="relative w-72 sm:w-80 h-[560px] rounded-[42px] bg-[#07100B] p-3 shadow-2xl border-4 border-gray-800 ring-1 ring-white/10">
                        {{-- Phone Dynamic Island / Speaker Notch --}}
                        <div class="absolute top-5 left-1/2 -translate-x-1/2 w-24 h-4 bg-gray-900 rounded-full flex items-center justify-center gap-2 z-20">
                            <span class="w-2 h-2 rounded-full bg-black"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500/80"></span>
                        </div>

                        {{-- Phone Screen Content --}}
                        <div class="w-full h-full rounded-[32px] bg-[#0B150F] overflow-hidden flex flex-col pt-8 pb-3 px-3 text-white border border-[#14261B]">
                            {{-- Mock Mobile App Bar --}}
                            <div class="flex items-center justify-between pb-3 border-b border-[#14261B]">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-[#1B6B3A] flex items-center justify-center text-[10px] text-[#F5C518] font-bold">
                                        ▲
                                    </div>
                                    <span class="text-xs font-extrabold tracking-wider">NPONTU SRE</span>
                                </div>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold">ONLINE</span>
                            </div>

                            {{-- Mock Offline Banner --}}
                            <div class="mt-2.5 px-2.5 py-1.5 rounded-lg bg-emerald-950/40 border border-emerald-500/30 flex items-center justify-between text-[10px]">
                                <span class="text-emerald-300 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    <span>Cached Telemetry Hydrated</span>
                                </span>
                                <span class="font-mono text-gray-400 text-[9px]">0ms Latency</span>
                            </div>

                            {{-- Mock Shift Stats Card --}}
                            <div class="mt-2.5 p-3 rounded-xl bg-[#0F1A14] border border-[#1A2E22]">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-mono text-gray-400 font-bold uppercase">MORNING SHIFT</span>
                                    <span class="text-[9px] font-mono text-[#F5C518]">P1 CRITICAL: 0</span>
                                </div>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-xl font-black text-white font-mono">14 / 16</span>
                                    <span class="text-[11px] text-emerald-400 font-semibold">87.5% Completed</span>
                                </div>
                            </div>

                            {{-- Mock Checklist Items --}}
                            <div class="mt-2.5 space-y-2 flex-1 overflow-hidden">
                                <div class="p-2.5 rounded-xl bg-[#0F1A14] border border-emerald-500/30 flex items-center justify-between">
                                    <div class="min-w-0 pr-2">
                                        <p class="text-[11px] font-bold text-white truncate">Verify DB Replication Lag</p>
                                        <p class="text-[9px] font-mono text-emerald-400 mt-0.5">DONE &bull; 09:15 UTC &bull; Kwame A.</p>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">DONE</span>
                                </div>

                                <div class="p-2.5 rounded-xl bg-[#0F1A14] border border-amber-500/30 flex items-center justify-between">
                                    <div class="min-w-0 pr-2">
                                        <p class="text-[11px] font-bold text-white truncate">Telco SMS Gateway Latency</p>
                                        <p class="text-[9px] font-mono text-[#F5C518] mt-0.5">PENDING &bull; SLA 11:30 &bull; Pool</p>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-500/20 text-[#F5C518] border border-amber-500/40">PENDING</span>
                                </div>

                                <div class="p-2.5 rounded-xl bg-[#0F1A14] border border-red-500/30 flex items-center justify-between">
                                    <div class="min-w-0 pr-2">
                                        <p class="text-[11px] font-bold text-white truncate">Payment Webhook Worker Queue</p>
                                        <p class="text-[9px] font-mono text-red-400 mt-0.5">WAR ROOM #4 &bull; Active Polling</p>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-red-500/20 text-red-300 border border-red-500/40">P1 HIGH</span>
                                </div>
                            </div>

                            {{-- Mock Bottom Navigation Bar --}}
                            <div class="pt-2 mt-auto border-t border-[#14261B] flex items-center justify-around text-gray-400 text-[10px] font-mono">
                                <span class="text-[#F5C518] font-bold flex flex-col items-center">
                                    <span>📊</span>
                                    <span class="text-[8px] mt-0.5">Cockpit</span>
                                </span>
                                <span class="flex flex-col items-center">
                                    <span>📋</span>
                                    <span class="text-[8px] mt-0.5">Checks</span>
                                </span>
                                <span class="flex flex-col items-center">
                                    <span>🤝</span>
                                    <span class="text-[8px] mt-0.5">Handoff</span>
                                </span>
                                <span class="flex flex-col items-center">
                                    <span>💬</span>
                                    <span class="text-[8px] mt-0.5">War Room</span>
                                </span>
                                <span class="flex flex-col items-center">
                                    <span>⚙️</span>
                                    <span class="text-[8px] mt-0.5">Settings</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── ACT VI: LIVE TELEMETRY BENCHMARKS ──────────────────────────────────── --}}
        <section id="telemetry" class="bg-[#0B150F] border-t border-[#14261B] py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl bg-[#0F1A14] border border-[#1A2E22] p-8 sm:p-12">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 pb-10 border-b border-[#1A2E22]">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono font-bold uppercase mb-3">
                                ACT VI &bull; LIVE TELEMETRY & HEALTH
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

    {{-- UTC Clock Live Synchronizer & Mobile Menu Controller --}}
    <script>
        function toggleLandingNav() {
            const menu = document.getElementById('landing-mobile-menu');
            const hamburger = document.getElementById('mobile-hamburger-icon');
            const close = document.getElementById('mobile-close-icon');
            const btn = document.getElementById('mobile-nav-toggle-btn');
            if (!menu) return;
            const isHidden = menu.classList.contains('hidden');
            if (isHidden) {
                menu.classList.remove('hidden');
                hamburger?.classList.add('hidden');
                close?.classList.remove('hidden');
                btn?.setAttribute('aria-expanded', 'true');
            } else {
                menu.classList.add('hidden');
                hamburger?.classList.remove('hidden');
                close?.classList.add('hidden');
                btn?.setAttribute('aria-expanded', 'false');
            }
        }

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
