<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Comprehensive Architecture, SRE Operations Manual, Evaluator Quickstart, and Interactive FAQ for Npontu Technologies Support Activity Tracker">
    <title>Documentation & Operational Manual — Support Tracker — Npontu Technologies</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="min-h-full bg-[#08120B] text-white flex flex-col antialiased selection:bg-[#F5C518] selection:text-gray-900" x-data="{ activeTab: 'evaluators', openFaq: 1, mobileNavOpen: false }">

    {{-- Top Live Operational Status Banner --}}
    <div class="bg-[#040805] border-b border-white/5 py-1.5 px-4 text-center text-[11px] text-gray-400 font-mono">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 truncate">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                <span class="truncate">PRODUCTION SRE NODE: ACCRA-CLUSTER-01</span>
                <span class="hidden sm:inline text-gray-600">|</span>
                <span class="hidden sm:inline text-emerald-300">ARCHITECTURE MANUAL &bull; RELEASE v1.4.0</span>
            </div>
            <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                <span id="nav-live-clock">UTC --:--:--</span>
                <a href="{{ route('health') }}" class="text-[#F5C518] hover:underline font-semibold flex items-center gap-1">
                    <span>99.98% SLA</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Main Header --}}
    <header class="sticky top-0 z-40 bg-[#0A1810]/95 backdrop-blur-md border-b border-emerald-900/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center shadow-sm group-hover:border-[#F5C518]/50 transition-colors shrink-0">
                        <svg class="w-5 h-5 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                            <polygon points="16,3 30,27 2,27"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <span class="font-extrabold text-sm tracking-tight text-white block leading-none truncate">Support Tracker</span>
                        <span class="block text-[#F5C518] text-[8px] sm:text-[9px] font-mono tracking-widest uppercase mt-0.5 font-bold truncate">NPONTU TECHNOLOGIES</span>
                    </div>
                </a>
            </div>

            {{-- Navigation Links (Desktop) --}}
            <nav class="hidden md:flex items-center gap-6 text-xs font-semibold text-gray-300">
                <a href="{{ route('landing') }}" class="hover:text-[#F5C518] transition-colors">Platform</a>
                <a href="#quickstart" class="hover:text-[#F5C518] transition-colors">Quickstart</a>
                <a href="#architecture" class="hover:text-[#F5C518] transition-colors">Architecture</a>
                <a href="#handover-flow" class="hover:text-[#F5C518] transition-colors">Handover Protocol</a>
                <a href="#faq" class="hover:text-[#F5C518] transition-colors">Comprehensive FAQ</a>
                <a href="{{ route('health') }}" class="hover:text-[#F5C518] transition-colors">Telemetry HUD</a>
            </nav>

            {{-- Right CTA Section --}}
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @auth
                    <a href="{{ route('activities.daily') }}"
                       class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-3.5 py-1.5 rounded-xl bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold text-xs shadow-md transition-colors whitespace-nowrap">
                        <span><span class="hidden sm:inline">Enter SRE </span>Cockpit</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-3.5 py-1.5 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-bold text-xs shadow-md transition-colors whitespace-nowrap">
                        <span><span class="hidden sm:inline">Operator </span>Sign In</span>
                        <svg class="w-3.5 h-3.5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </a>
                @endauth

                {{-- Mobile Hamburger Toggle Button --}}
                <button type="button"
                        @click="mobileNavOpen = !mobileNavOpen"
                        aria-expanded="false"
                        aria-label="Toggle navigation menu"
                        class="md:hidden p-2 rounded-xl bg-white/5 border border-white/10 text-gray-300 hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus:ring-2 focus:ring-[#F5C518]">
                    <svg x-show="!mobileNavOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileNavOpen" x-cloak class="w-5 h-5 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile Collapsible Navigation Menu --}}
        <div x-show="mobileNavOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden bg-[#0A1810]/98 border-b border-emerald-900/40 px-4 pt-3 pb-5 space-y-1 shadow-2xl backdrop-blur-xl">
            <div class="px-3 pb-2 text-[10px] font-mono font-bold uppercase tracking-wider text-gray-400">
                Documentation Manual Sections
            </div>
            <a @click="mobileNavOpen = false" href="{{ route('landing') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-200 hover:text-[#F5C518] hover:bg-white/5 transition-colors flex items-center justify-between">
                <span>&larr; Platform Overview</span>
                <span class="text-[10px] font-mono text-gray-400">Home</span>
            </a>
            <a @click="mobileNavOpen = false" href="#quickstart" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-200 hover:text-[#F5C518] hover:bg-white/5 transition-colors">
                Role Quickstarts &amp; Personas
            </a>
            <a @click="mobileNavOpen = false" href="#architecture" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-200 hover:text-[#F5C518] hover:bg-white/5 transition-colors">
                Technical Architecture (Laravel 11 / Pest / MySQL)
            </a>
            <a @click="mobileNavOpen = false" href="#handover-flow" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-200 hover:text-[#F5C518] hover:bg-white/5 transition-colors">
                4-Phase Shift Handover Protocol
            </a>
            <a @click="mobileNavOpen = false" href="#faq" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-200 hover:text-[#F5C518] hover:bg-white/5 transition-colors">
                Interactive Engineering FAQ
            </a>
            <a @click="mobileNavOpen = false" href="{{ route('health') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-emerald-300 hover:bg-white/5 transition-colors flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Real-Time Health HUD</span>
                </span>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-950 border border-emerald-500/30 text-emerald-300">99.98% SLA</span>
            </a>
        </div>
    </header>

    {{-- Docs Hero Header --}}
    <section class="relative bg-gradient-to-b from-[#123620] via-[#0C2215] to-[#08120B] border-b border-emerald-900/30 pt-12 pb-14 px-4 sm:px-6 lg:px-8 overflow-hidden">
        <div class="max-w-7xl mx-auto relative z-10">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-black/40 border border-emerald-500/30 text-emerald-300 text-xs font-mono mb-4">
                    <span class="w-2 h-2 rounded-full bg-[#F5C518] animate-ping"></span>
                    <span>SRE COMPREHENSIVE ARCHITECTURE &amp; OPERATIONAL HANDBOOK</span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                    Engineered for zero broken handovers. Documented for everyone.
                </h1>
                <p class="text-sm sm:text-base text-green-100/80 mt-4 leading-relaxed font-normal">
                    Welcome to the central knowledge hub of the Npontu Support Activity Tracker. Whether you are an evaluator inspecting technical architecture, an on-call SRE executing a live shift handover, or executive leadership reviewing SLA compliance, this portal answers every operational question.
                </p>

                {{-- Key Telemetry Metrics Pill Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-8">
                    <div class="p-3 rounded-xl bg-black/40 border border-white/10 text-center">
                        <p class="text-xl sm:text-2xl font-black text-[#F5C518] font-mono">60 Tests</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">314 Verified Assertions</p>
                    </div>
                    <div class="p-3 rounded-xl bg-black/40 border border-white/10 text-center">
                        <p class="text-xl sm:text-2xl font-black text-emerald-400 font-mono">&lt; 100ms</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Telemetry Response Target</p>
                    </div>
                    <div class="p-3 rounded-xl bg-black/40 border border-white/10 text-center">
                        <p class="text-xl sm:text-2xl font-black text-white font-mono">99.98%</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Guaranteed SLA Uptime</p>
                    </div>
                    <div class="p-3 rounded-xl bg-black/40 border border-white/10 text-center">
                        <p class="text-xl sm:text-2xl font-black text-[#F5C518] font-mono">100%</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Immutable Audit Custody</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Documentation Layout with Persona Navigation --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        {{-- Persona Selector Bar --}}
        <div class="flex flex-wrap items-center gap-2 p-1.5 rounded-2xl bg-[#050D07] border border-emerald-900/40 mb-10 shadow-lg">
            <button type="button"
                    @click="activeTab = 'evaluators'"
                    :class="activeTab === 'evaluators' ? 'bg-[#F5C518] text-gray-950 font-extrabold shadow-md' : 'text-gray-300 hover:text-white hover:bg-white/5 font-semibold'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>For Evaluators &amp; Technical Leads</span>
            </button>

            <button type="button"
                    @click="activeTab = 'operators'"
                    :class="activeTab === 'operators' ? 'bg-[#F5C518] text-gray-950 font-extrabold shadow-md' : 'text-gray-300 hover:text-white hover:bg-white/5 font-semibold'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>For SRE Shift Operators &amp; NOC Teams</span>
            </button>

            <button type="button"
                    @click="activeTab = 'stakeholders'"
                    :class="activeTab === 'stakeholders' ? 'bg-[#F5C518] text-gray-950 font-extrabold shadow-md' : 'text-gray-300 hover:text-white hover:bg-white/5 font-semibold'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>For Leadership &amp; Non-Technical Stakeholders</span>
            </button>
        </div>

        {{-- ── TAB 1: FOR EVALUATORS & TECHNICAL LEADS ───────────────────────────── --}}
        <div x-show="activeTab === 'evaluators'" x-cloak class="space-y-10">

            {{-- Evaluator Quick Access Cards --}}
            <div id="quickstart" class="bg-[#0C1A12] border border-emerald-800/40 rounded-2xl p-6 sm:p-8 shadow-xl">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-6 mb-6">
                    <div>
                        <span class="text-xs font-mono uppercase tracking-widest text-[#F5C518] font-bold">EVALUATOR FAST-TRACK</span>
                        <h2 class="text-2xl font-black text-white mt-1">Pre-Seeded Operational Test Personas</h2>
                        <p class="text-xs text-gray-400 mt-1">Experience the console from each perspective with pre-configured grades, departments, and granular privileges.</p>
                    </div>
                    <a href="{{ route('login') }}" class="self-start sm:self-auto px-4 py-2 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-bold text-xs shadow-md transition-colors">
                        Launch Login Screen &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Admin Kwame --}}
                    <div class="p-5 rounded-xl bg-black/40 border border-white/10 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-purple-950/80 text-purple-300 border border-purple-800/60 font-bold">L4 Principal Lead</span>
                            <span class="text-xs font-mono text-gray-400">admin@npontu.local</span>
                        </div>
                        <h3 class="text-base font-black text-white mt-3">Kwame Mensah</h3>
                        <p class="text-xs text-emerald-400 font-mono mt-0.5">Cloud Infrastructure &amp; SRE</p>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            Full administrative command, user provisioning, checklists definition, forensic audit log inspection, and system settings.
                        </p>
                        <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs font-mono text-gray-300">
                            <span>Password: <strong class="text-white">password</strong></span>
                            <a href="{{ route('login') }}" class="text-[#F5C518] hover:underline font-bold">Sign In &rarr;</a>
                        </div>
                    </div>

                    {{-- Lead Abena --}}
                    <div class="p-5 rounded-xl bg-black/40 border border-white/10 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-950/80 text-emerald-300 border border-emerald-800/60 font-bold">L3 Senior SRE</span>
                            <span class="text-xs font-mono text-gray-400">lead@npontu.local</span>
                        </div>
                        <h3 class="text-base font-black text-white mt-3">Abena Owusu</h3>
                        <p class="text-xs text-emerald-400 font-mono mt-0.5">Payment Gateway Operations</p>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            Shift handover sign-off, oncoming briefing acceptance, task delegation, live telemetry HUD, and compliance reports.
                        </p>
                        <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs font-mono text-gray-300">
                            <span>Password: <strong class="text-white">password</strong></span>
                            <a href="{{ route('login') }}" class="text-[#F5C518] hover:underline font-bold">Sign In &rarr;</a>
                        </div>
                    </div>

                    {{-- Agent Kofi --}}
                    <div class="p-5 rounded-xl bg-black/40 border border-white/10 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-blue-950/80 text-blue-300 border border-blue-800/60 font-bold">L2 Support Engineer</span>
                            <span class="text-xs font-mono text-gray-400">agent@npontu.local</span>
                        </div>
                        <h3 class="text-base font-black text-white mt-3">Kofi Asante</h3>
                        <p class="text-xs text-emerald-400 font-mono mt-0.5">Database Operations &amp; DBA</p>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            Shift checklist status updates, blocker remarks, incident ticket tagging, and real-time team chat communications.
                        </p>
                        <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs font-mono text-gray-300">
                            <span>Password: <strong class="text-white">password</strong></span>
                            <a href="{{ route('login') }}" class="text-[#F5C518] hover:underline font-bold">Sign In &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Architectural Tech Stack & Code Quality --}}
            <div id="architecture" class="bg-[#0C1A12] border border-emerald-800/40 rounded-2xl p-6 sm:p-8 shadow-xl">
                <span class="text-xs font-mono uppercase tracking-widest text-emerald-400 font-bold">TECHNICAL FOUNDATION</span>
                <h2 class="text-2xl font-black text-white mt-1">Architectural Stack &amp; Standards</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div class="space-y-3">
                        <div class="p-4 rounded-xl bg-black/30 border border-white/10">
                            <p class="text-xs font-bold text-white flex items-center gap-2">
                                <span class="text-[#F5C518] font-mono">1.</span>
                                <span>Laravel 11 LTS + Strict Types (declare(strict_types=1))</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">PHP 8.2+ with PSR-12 strict formatting enforced by Laravel Pint. Thin controllers, Form Requests, Policies, and Action domain patterns.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-black/30 border border-white/10">
                            <p class="text-xs font-bold text-white flex items-center gap-2">
                                <span class="text-[#F5C518] font-mono">2.</span>
                                <span>Livewire 3 Real-Time Reactive Engine</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Instant DOM differential updates without client SPA overhead. Automated 419 session interception and continuous wire:poll heartbeats.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-black/30 border border-white/10">
                            <p class="text-xs font-bold text-white flex items-center gap-2">
                                <span class="text-[#F5C518] font-mono">3.</span>
                                <span>MySQL 8.0+ InnoDB with Strict FK Integrity</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Indexed composite columns on activity_logs(date, activity_id), immutable polymorphic audit_logs, and reversible down() migrations.</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="p-4 rounded-xl bg-black/30 border border-white/10">
                            <p class="text-xs font-bold text-white flex items-center gap-2">
                                <span class="text-[#F5C518] font-mono">4.</span>
                                <span>Pest 3 Test Suite (60 Feature Tests / 314 Assertions)</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Full coverage of Authentication, Activity CRUD, Handover Sign-Off &amp; Sign-On, Chat @Mentions, Reporting date-range queries, and Error handling.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-black/30 border border-white/10">
                            <p class="text-xs font-bold text-white flex items-center gap-2">
                                <span class="text-[#F5C518] font-mono">5.</span>
                                <span>Automated SRE Reports Scheduler</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Console command `reports:send-automated` with scheduled cron jobs in routes/console.php dispatching daily, weekly, and monthly digests.</p>
                        </div>

                        <div class="p-4 rounded-xl bg-black/30 border border-white/10">
                            <p class="text-xs font-bold text-white flex items-center gap-2">
                                <span class="text-[#F5C518] font-mono">6.</span>
                                <span>Subsystem Probes &amp; Telemetry HUD</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Real-time health probes pinging 8 core subsystems (Database, Cache, Queue, Storage, API Latency) with JSON API support (<code class="text-emerald-300 font-mono">/health/telemetry</code>).</p>
                        </div>
                    </div>
                </div>

                {{-- Command Line Cheatsheet --}}
                <div class="mt-6 pt-6 border-t border-white/10">
                    <p class="text-xs font-bold text-gray-300 font-mono uppercase mb-3">Evaluator Verification Commands</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-xs">
                        <div class="p-3 rounded-lg bg-black/60 border border-white/10">
                            <p class="text-gray-500">// Run Full Pest Test Suite</p>
                            <p class="text-[#F5C518] mt-1">php artisan test</p>
                        </div>
                        <div class="p-3 rounded-lg bg-black/60 border border-white/10">
                            <p class="text-gray-500">// Verify PSR-12 Code Style</p>
                            <p class="text-[#F5C518] mt-1">./vendor/bin/pint --test</p>
                        </div>
                        <div class="p-3 rounded-lg bg-black/60 border border-white/10">
                            <p class="text-gray-500">// Dispatch Automated Daily Report</p>
                            <p class="text-[#F5C518] mt-1">php artisan reports:send-automated</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── TAB 2: FOR SRE SHIFT OPERATORS & NOC TEAMS ────────────────────────── --}}
        <div x-show="activeTab === 'operators'" x-cloak class="space-y-10">

            {{-- Handover Protocol Deep-Dive --}}
            <div id="handover-flow" class="bg-[#0C1A12] border border-emerald-800/40 rounded-2xl p-6 sm:p-8 shadow-xl">
                <span class="text-xs font-mono uppercase tracking-widest text-[#F5C518] font-bold">OPERATIONAL CUSTODY TRANSFER</span>
                <h2 class="text-2xl font-black text-white mt-1">The 4-Phase Two-Way Handover Protocol</h2>
                <p class="text-xs text-gray-400 mt-1 max-w-2xl">
                    Traditional shift handoffs rely on informal Slack messages or post-it notes. Npontu enforces a legally binding, mathematically verifiable two-way custody transfer.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                    <div class="p-4 rounded-xl bg-black/30 border border-white/10">
                        <div class="w-7 h-7 rounded-lg bg-emerald-900/50 text-[#F5C518] flex items-center justify-center font-mono font-bold text-xs mb-3">01</div>
                        <h4 class="text-sm font-bold text-white">Live Verification</h4>
                        <p class="text-xs text-gray-400 mt-1 leading-relaxed">
                            On-duty engineers mark shift checks (done/pending). Any pending item requires an explanatory remark and incident ticket reference.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-black/30 border border-white/10">
                        <div class="w-7 h-7 rounded-lg bg-emerald-900/50 text-[#F5C518] flex items-center justify-center font-mono font-bold text-xs mb-3">02</div>
                        <h4 class="text-sm font-bold text-white">Outgoing Sign-Off</h4>
                        <p class="text-xs text-gray-400 mt-1 leading-relaxed">
                            Shift Lead records official briefing summary, outstanding blockers, and signs off. Handover state changes to "Awaiting Incoming Acceptance".
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-black/30 border border-white/10">
                        <div class="w-7 h-7 rounded-lg bg-emerald-900/50 text-[#F5C518] flex items-center justify-center font-mono font-bold text-xs mb-3">03</div>
                        <h4 class="text-sm font-bold text-white">Incoming Sign-On</h4>
                        <p class="text-xs text-gray-400 mt-1 leading-relaxed">
                            Incoming Lead reviews checklist status, confirms telemetry health, checks the verification box, and records signed-on acceptance remarks.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-black/30 border border-white/10">
                        <div class="w-7 h-7 rounded-lg bg-emerald-900/50 text-[#F5C518] flex items-center justify-center font-mono font-bold text-xs mb-3">04</div>
                        <h4 class="text-sm font-bold text-white">Forensic Seal</h4>
                        <p class="text-xs text-gray-400 mt-1 leading-relaxed">
                            Both sign-off and sign-on timestamps, actor IDs, and IP addresses are sealed into immutable audit logs. Automated reports dispatch to stakeholders.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Operational Comms & Incident War Rooms --}}
            <div class="bg-[#0C1A12] border border-emerald-800/40 rounded-2xl p-6 sm:p-8 shadow-xl">
                <span class="text-xs font-mono uppercase tracking-widest text-emerald-400 font-bold">REAL-TIME COMMUNICATIONS</span>
                <h2 class="text-2xl font-black text-white mt-1">Operational Chat, War Rooms &amp; Alerts</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                    <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                        <p class="text-xs font-bold text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#F5C518]"></span>
                            <span>Direct &amp; Shift Channels</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            Auto-provisioned <code class="text-emerald-300">#general-shift</code> team channel paired with 1-on-1 direct messaging and per-user unread tracking counters.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                        <p class="text-xs font-bold text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span>@Mention Email Receipts</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            Tagging an engineer with <code class="text-emerald-300">@Name</code> immediately generates an automated transactional email dispatch with direct context.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                        <p class="text-xs font-bold text-white flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                            <span>@All Emergency Broadcast</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            Tagging <code class="text-rose-400">@all</code> immediately notifies every channel participant via high-priority email, creating an instant incident response bridge.
                        </p>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── TAB 3: FOR LEADERSHIP & NON-TECHNICAL STAKEHOLDERS ────────────────── --}}
        <div x-show="activeTab === 'stakeholders'" x-cloak class="space-y-10">

            <div class="bg-[#0C1A12] border border-emerald-800/40 rounded-2xl p-6 sm:p-8 shadow-xl">
                <span class="text-xs font-mono uppercase tracking-widest text-[#F5C518] font-bold">BUSINESS VALUE &amp; GOVERNANCE</span>
                <h2 class="text-2xl font-black text-white mt-1">Why Support Tracker Exists: The Cost of Silent Outages</h2>
                <p class="text-sm text-gray-300 mt-3 leading-relaxed">
                    In high-throughput telecommunications and payment processing environments, <strong>unacknowledged shift handovers represent the single largest vector for catastrophic downtime</strong>. When an outgoing team forgets to mention a degraded database replica or an ongoing upstream telco failover, the incoming shift assumes all is nominal until client transactions begin failing.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div class="p-5 rounded-xl bg-rose-950/20 border border-rose-800/30">
                        <p class="text-2xl font-black text-rose-400 font-mono">$100,000 / hr</p>
                        <p class="text-xs font-bold text-white mt-1">Cost of Payment Outage</p>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            Unreported gateway degradations lead to customer charge failures, regulatory scrutiny, and brand attrition.
                        </p>
                    </div>

                    <div class="p-5 rounded-xl bg-emerald-950/20 border border-emerald-800/30">
                        <p class="text-2xl font-black text-emerald-400 font-mono">0 Missed Handovers</p>
                        <p class="text-xs font-bold text-white mt-1">The Npontu Standard</p>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            Cryptographic sign-on requires the oncoming supervisor to actively review and confirm readiness before custody transfers.
                        </p>
                    </div>

                    <div class="p-5 rounded-xl bg-amber-950/20 border border-amber-800/30">
                        <p class="text-2xl font-black text-[#F5C518] font-mono">7-Year Audit Trail</p>
                        <p class="text-xs font-bold text-white mt-1">Bank-Grade Compliance</p>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            Every button press, task reassignment, and status toggle is preserved in append-only storage under Ghana Act 843.
                        </p>
                    </div>
                </div>

                {{-- Automated Reporting Overview --}}
                <div class="mt-8 pt-6 border-t border-white/10">
                    <h3 class="text-base font-bold text-white">Automated Stakeholder Reporting Pipeline</h3>
                    <p class="text-xs text-gray-400 mt-1">Leadership and department heads receive hands-free operational intelligence:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 text-xs">
                        <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                            <p class="font-bold text-white font-mono text-[#F5C518]">Daily EOD Digest</p>
                            <p class="text-gray-400 mt-1">Dispatched at 23:59 UTC detailing daily checklist completion %, active blockers, and handover status.</p>
                        </div>
                        <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                            <p class="font-bold text-white font-mono text-emerald-400">Weekly Performance Report</p>
                            <p class="text-gray-400 mt-1">Dispatched Sunday evenings summarizing weekly resolution trends, duty hours, and incident ticket references.</p>
                        </div>
                        <div class="p-3.5 rounded-xl bg-white/5 border border-white/10">
                            <p class="font-bold text-white font-mono text-blue-400">Monthly SLA Executive Audit</p>
                            <p class="text-gray-400 mt-1">Dispatched on the 28th providing formal 99.98% uptime SLA compliance stamps and audit trail volumes.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── COMPREHENSIVE INTERACTIVE FAQ ACCORDION ───────────────────────────── --}}
        <div id="faq" class="mt-16 pt-10 border-t border-white/10">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-mono uppercase tracking-widest text-[#F5C518] font-bold">KNOWLEDGE BASE</span>
                <h2 class="text-3xl font-black text-white mt-1">Frequently Asked Questions</h2>
                <p class="text-xs sm:text-sm text-gray-400 mt-2">
                    Direct, authoritative answers addressing architecture, SRE protocols, security guarantees, and platform governance.
                </p>
            </div>

            <div class="max-w-4xl mx-auto space-y-3">
                
                {{-- FAQ 1 --}}
                <div class="rounded-xl bg-[#0C1A12] border border-emerald-900/40 overflow-hidden">
                    <button type="button"
                            @click="openFaq = openFaq === 1 ? null : 1"
                            class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 cursor-pointer hover:bg-white/5 transition-colors">
                        <span class="font-bold text-sm text-white flex items-center gap-3">
                            <span class="w-6 h-6 rounded-md bg-white/5 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">Q1</span>
                            <span>How does the two-way handover custody transfer work mathematically?</span>
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="openFaq === 1 ? 'rotate-180 text-[#F5C518]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openFaq === 1" x-cloak class="p-5 pt-0 text-xs sm:text-sm text-gray-300 leading-relaxed border-t border-white/5 bg-black/20">
                        The shift custody handover is a two-state operational cryptographic contract:
                        <ul class="list-disc list-inside mt-2 space-y-1 text-gray-400">
                            <li><strong>State 1 (Outgoing Sign-Off)</strong>: The outgoing shift lead confirms all checklist items, logs blocker remarks, and triggers `sign-off`. This stamps `user_id`, `shift_date`, `shift_type`, and an immutable timestamp. The handover status is set to "Awaiting Incoming Sign-On".</li>
                            <li><strong>State 2 (Incoming Acceptance)</strong>: The oncoming lead cannot simply click "accept" silently; they must review the briefing, tick the checklist verification confirmation, and enter acceptance remarks. This stamps `accepted_by_id`, `accepted_at`, and creates an immutable SIEM audit log entry. Operational responsibility is officially transferred only after State 2 completes.</li>
                        </ul>
                    </div>
                </div>

                {{-- FAQ 2 --}}
                <div class="rounded-xl bg-[#0C1A12] border border-emerald-900/40 overflow-hidden">
                    <button type="button"
                            @click="openFaq = openFaq === 2 ? null : 2"
                            class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 cursor-pointer hover:bg-white/5 transition-colors">
                        <span class="font-bold text-sm text-white flex items-center gap-3">
                            <span class="w-6 h-6 rounded-md bg-white/5 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">Q2</span>
                            <span>What happens when an engineer's session times out after 120 minutes of inactivity?</span>
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="openFaq === 2 ? 'rotate-180 text-[#F5C518]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openFaq === 2" x-cloak class="p-5 pt-0 text-xs sm:text-sm text-gray-300 leading-relaxed border-t border-white/5 bg-black/20">
                        To prevent unauthorized physical access to an unattended cockpit terminal, sessions automatically expire after 120 minutes. Rather than displaying an ugly default Laravel "419 Page Expired" modal, Livewire hooks intercept the 419 token response and smoothly redirect the operator to <code class="text-emerald-300 font-mono">/login?expired=1</code>, which displays a branded security alert explaining the inactivity timeout and provides 1-click test credential population to resume the shift immediately.
                    </div>
                </div>

                {{-- FAQ 3 --}}
                <div class="rounded-xl bg-[#0C1A12] border border-emerald-900/40 overflow-hidden">
                    <button type="button"
                            @click="openFaq = openFaq === 3 ? null : 3"
                            class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 cursor-pointer hover:bg-white/5 transition-colors">
                        <span class="font-bold text-sm text-white flex items-center gap-3">
                            <span class="w-6 h-6 rounded-md bg-white/5 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">Q3</span>
                            <span>Can audit log records ever be deleted or edited by administrators?</span>
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="openFaq === 3 ? 'rotate-180 text-[#F5C518]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openFaq === 3" x-cloak class="p-5 pt-0 text-xs sm:text-sm text-gray-300 leading-relaxed border-t border-white/5 bg-black/20">
                        <strong>No.</strong> The <code class="text-emerald-300 font-mono">audit_logs</code> table is architecturally append-only. No controller, model, or administrative screen provides an update or delete action for audit logs. Furthermore, the database grants assigned in production restrict the application user to <code class="text-emerald-300 font-mono">INSERT</code> and <code class="text-emerald-300 font-mono">SELECT</code> statements on audit tables, rendering them immutable even under administrative compromise.
                    </div>
                </div>

                {{-- FAQ 4 --}}
                <div class="rounded-xl bg-[#0C1A12] border border-emerald-900/40 overflow-hidden">
                    <button type="button"
                            @click="openFaq = openFaq === 4 ? null : 4"
                            class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 cursor-pointer hover:bg-white/5 transition-colors">
                        <span class="font-bold text-sm text-white flex items-center gap-3">
                            <span class="w-6 h-6 rounded-md bg-white/5 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">Q4</span>
                            <span>How do automated daily, weekly, and monthly reports work?</span>
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="openFaq === 4 ? 'rotate-180 text-[#F5C518]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openFaq === 4" x-cloak class="p-5 pt-0 text-xs sm:text-sm text-gray-300 leading-relaxed border-t border-white/5 bg-black/20">
                        Automated reports are powered by the Laravel Artisan command <code class="text-emerald-300 font-mono">php artisan reports:send-automated</code>. In <code class="text-emerald-300 font-mono">routes/console.php</code>, automated schedules are registered:
                        <ul class="list-disc list-inside mt-2 space-y-1 text-gray-400">
                            <li><strong>Daily EOD Report</strong>: Runs automatically at 23:59 UTC every day, aggregating the shift's resolution rate, blocker remarks, and signed handovers.</li>
                            <li><strong>Weekly Digest</strong>: Runs on Sundays at 23:59 UTC, compiling 7-day operational performance and compliance trends.</li>
                            <li><strong>Monthly Executive Summary</strong>: Runs on the 28th of each month, presenting formal SLA uptime achievement and audit log volume.</li>
                            <li><strong>On-Demand Triggering</strong>: Administrators can dispatch reports at any time via CLI (e.g. <code class="text-emerald-300 font-mono">php artisan reports:send-automated --period=weekly --email=ops@npontu.com</code>).</li>
                        </ul>
                    </div>
                </div>

                {{-- FAQ 5 --}}
                <div class="rounded-xl bg-[#0C1A12] border border-emerald-900/40 overflow-hidden">
                    <button type="button"
                            @click="openFaq = openFaq === 5 ? null : 5"
                            class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 cursor-pointer hover:bg-white/5 transition-colors">
                        <span class="font-bold text-sm text-white flex items-center gap-3">
                            <span class="w-6 h-6 rounded-md bg-white/5 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">Q5</span>
                            <span>What are the 5 SRE grades and 9 granular privileges?</span>
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="openFaq === 5 ? 'rotate-180 text-[#F5C518]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openFaq === 5" x-cloak class="p-5 pt-0 text-xs sm:text-sm text-gray-300 leading-relaxed border-t border-white/5 bg-black/20">
                        Npontu defines 5 technical engineering tiers: <strong>L1 Support Operator</strong>, <strong>L2 Support Engineer</strong>, <strong>L3 Senior SRE / Shift Lead</strong>, <strong>L4 Principal Lead</strong>, and <strong>L5 Director / Systems Architect</strong>.
                        <br><br>
                        Beyond base roles, permissions are governed by 9 fine-grained privilege toggles:
                        <code class="text-emerald-300 font-mono">manage_activities</code>, <code class="text-emerald-300 font-mono">assign_tasks</code>, <code class="text-emerald-300 font-mono">sign_handovers</code>, <code class="text-emerald-300 font-mono">accept_handovers</code>, <code class="text-emerald-300 font-mono">escalate_incidents</code>, <code class="text-emerald-300 font-mono">export_reports</code>, <code class="text-emerald-300 font-mono">manage_users</code>, <code class="text-emerald-300 font-mono">view_audit_logs</code>, and <code class="text-emerald-300 font-mono">create_channels</code>.
                    </div>
                </div>

                {{-- FAQ 6 --}}
                <div class="rounded-xl bg-[#0C1A12] border border-emerald-900/40 overflow-hidden">
                    <button type="button"
                            @click="openFaq = openFaq === 6 ? null : 6"
                            class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 cursor-pointer hover:bg-white/5 transition-colors">
                        <span class="font-bold text-sm text-white flex items-center gap-3">
                            <span class="w-6 h-6 rounded-md bg-white/5 text-[#F5C518] flex items-center justify-center text-xs font-mono font-bold">Q6</span>
                            <span>Can automated external monitors ping the platform telemetry endpoint?</span>
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="openFaq === 6 ? 'rotate-180 text-[#F5C518]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openFaq === 6" x-cloak class="p-5 pt-0 text-xs sm:text-sm text-gray-300 leading-relaxed border-t border-white/5 bg-black/20">
                        <strong>Yes.</strong> The telemetry endpoint <code class="text-emerald-300 font-mono">GET /health?format=json</code> or <code class="text-emerald-300 font-mono">GET /health/telemetry</code> returns a machine-readable JSON payload containing active status (<code class="text-emerald-300 font-mono">ok</code>), database connectivity latency, memory usage, and uptime percentage for integration with Prometheus, DataDog, or UptimeRobot.
                    </div>
                </div>

            </div>
        </div>

    </div>

    {{-- Comprehensive Enterprise Footer --}}
    @include('layouts.partials.footer')

    {{-- Live UTC Clock Synchronizer --}}
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
    @livewireScripts
</body>
</html>
