<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Opsora SRE Operations Manual, Architecture Reference, REST API Docs, and Mobile Companion Setup">
    <title>Documentation &amp; API Reference — Opsora SRE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full bg-[#08120B] text-white flex flex-col antialiased selection:bg-[#F5C518] selection:text-gray-900"
      x-data="{
          mobileNavOpen: false,
          activeApiTab: 'auth',
          openFaq: 1
      }">

    {{-- ── TOP OPERATIONAL STATUS TICKER ────────────────────────────────────── --}}
    <div class="bg-[#040805] border-b border-white/5 py-1.5 px-4 text-center text-[11px] text-gray-400 font-mono">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 truncate">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                <span class="truncate">PRODUCTION SRE NODE: ACCRA-CLUSTER-01</span>
                <span class="hidden sm:inline text-gray-600">|</span>
                <span class="hidden sm:inline text-emerald-300">OPSORA SAAS CORE &bull; RELEASE v1.5.0</span>
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

    {{-- ── GLOBAL CONCISE HEADER ────────────────────────────────────────────── --}}
    <header class="sticky top-0 z-50 bg-[#0A1810]/95 backdrop-blur-md border-b border-emerald-900/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            
            {{-- Brand Logo & Docs Identity --}}
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-950/80 to-black/80 border border-white/10 flex items-center justify-center shadow-sm group-hover:border-[#F5C518]/50 transition-colors shrink-0 p-1">
                        <img src="{{ asset('images/opsora-icon.svg') }}" alt="Opsora SRE" class="w-full h-full object-contain">
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="font-extrabold text-sm tracking-tight text-white block leading-none">OPSORA</span>
                            <span class="px-1 py-0.2 rounded bg-[#F5C518]/20 border border-[#F5C518]/50 text-[#F5C518] text-[9px] font-bold font-mono">SRE</span>
                        </div>
                        <span class="block text-emerald-400 text-[8px] sm:text-[9px] font-mono tracking-widest uppercase mt-0.5 font-bold">OPERATIONS MANUAL</span>
                    </div>
                </a>
            </div>

            {{-- Concise Structured Desktop Navigation --}}
            <nav class="hidden lg:flex items-center gap-1 text-xs font-semibold text-gray-300">
                <a href="#quickstart" class="px-2.5 py-1.5 rounded-lg hover:text-[#F5C518] hover:bg-white/5 transition-colors">Quickstart</a>
                <a href="#architecture" class="px-2.5 py-1.5 rounded-lg hover:text-[#F5C518] hover:bg-white/5 transition-colors">Architecture</a>
                <a href="#api-reference" class="px-2.5 py-1.5 rounded-lg hover:text-[#F5C518] hover:bg-white/5 transition-colors flex items-center gap-1.5">
                    <span>API Reference</span>
                    <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">REST v1</span>
                </a>
                <a href="#mobile-setup" class="px-2.5 py-1.5 rounded-lg hover:text-[#F5C518] hover:bg-white/5 transition-colors">Mobile &amp; Emulator</a>
                <a href="#handover-flow" class="px-2.5 py-1.5 rounded-lg hover:text-[#F5C518] hover:bg-white/5 transition-colors">Handover Protocol</a>
                <a href="#governance" class="px-2.5 py-1.5 rounded-lg hover:text-[#F5C518] hover:bg-white/5 transition-colors">Governance &amp; SLA</a>
                <a href="#faq" class="px-2.5 py-1.5 rounded-lg hover:text-[#F5C518] hover:bg-white/5 transition-colors">FAQ</a>
            </nav>

            {{-- Right Actions: Telemetry + Cockpit CTA --}}
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('health') }}"
                   class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 text-emerald-300 text-xs font-mono border border-emerald-800/40 transition-colors">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Live Telemetry</span>
                </a>

                @auth
                    <a href="{{ route('activities.daily') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white font-bold text-xs shadow-md transition-colors whitespace-nowrap">
                        <span>Enter SRE Cockpit</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-bold text-xs shadow-md transition-colors whitespace-nowrap">
                        <span>Operator Sign In</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </a>
                @endauth

                {{-- Mobile Hamburger --}}
                <button type="button"
                        @click="mobileNavOpen = !mobileNavOpen"
                        aria-label="Toggle navigation menu"
                        class="lg:hidden p-2 rounded-xl bg-white/5 border border-white/10 text-gray-300 hover:text-white transition-colors">
                    <svg x-show="!mobileNavOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileNavOpen" x-cloak class="w-5 h-5 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Collapsible Mobile Navigation --}}
        <div x-show="mobileNavOpen" x-cloak
             class="lg:hidden bg-[#0A1810] border-b border-emerald-900/40 px-4 py-3 space-y-1 text-xs">
            <a @click="mobileNavOpen = false" href="#quickstart" class="block px-3 py-2 rounded-lg text-gray-200 hover:text-[#F5C518] hover:bg-white/5">Quickstart &amp; Personas</a>
            <a @click="mobileNavOpen = false" href="#architecture" class="block px-3 py-2 rounded-lg text-gray-200 hover:text-[#F5C518] hover:bg-white/5">Architecture &amp; Stack</a>
            <a @click="mobileNavOpen = false" href="#api-reference" class="block px-3 py-2 rounded-lg text-emerald-300 hover:text-[#F5C518] hover:bg-white/5 font-semibold">REST API Reference (v1)</a>
            <a @click="mobileNavOpen = false" href="#mobile-setup" class="block px-3 py-2 rounded-lg text-gray-200 hover:text-[#F5C518] hover:bg-white/5">Mobile Companion &amp; Emulator</a>
            <a @click="mobileNavOpen = false" href="#handover-flow" class="block px-3 py-2 rounded-lg text-gray-200 hover:text-[#F5C518] hover:bg-white/5">Two-Way Handover Protocol</a>
            <a @click="mobileNavOpen = false" href="#governance" class="block px-3 py-2 rounded-lg text-gray-200 hover:text-[#F5C518] hover:bg-white/5">Governance &amp; SLA</a>
            <a @click="mobileNavOpen = false" href="#faq" class="block px-3 py-2 rounded-lg text-gray-200 hover:text-[#F5C518] hover:bg-white/5">Frequently Asked Questions</a>
        </div>
    </header>

    {{-- ── HERO SECTION ─────────────────────────────────────────────────────── --}}
    <section class="relative bg-gradient-to-b from-[#123620] via-[#0C2215] to-[#08120B] border-b border-emerald-900/30 pt-10 pb-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-300 mb-3">
                <span class="w-2 h-2 rounded-full bg-[#F5C518] animate-ping"></span>
                <span>SRE COMPREHENSIVE ARCHITECTURE &bull; MULTI-TENANT OPERATIONS</span>
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight max-w-4xl">
                Engineered for zero broken handovers. Documented for everyone.
            </h1>
            <p class="text-xs sm:text-sm text-gray-300 mt-3 max-w-3xl leading-relaxed">
                Authoritative platform reference for Site Reliability Engineers, technical leads, and evaluators. Explore technical architecture, operational personas, interactive REST APIs, and mobile companion setup.
            </p>

            {{-- Fast Metric Bar --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 max-w-4xl">
                <div class="p-3 rounded-xl bg-black/40 border border-white/10 text-center">
                    <p class="text-lg sm:text-xl font-black text-[#F5C518] font-mono">139 Tests</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">655 Assertions Passing</p>
                </div>
                <div class="p-3 rounded-xl bg-black/40 border border-white/10 text-center">
                    <p class="text-lg sm:text-xl font-black text-emerald-400 font-mono">&lt; 42ms</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Telemetry Response Target</p>
                </div>
                <div class="p-3 rounded-xl bg-black/40 border border-white/10 text-center">
                    <p class="text-lg sm:text-xl font-black text-white font-mono">99.98%</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Guaranteed SLA Uptime</p>
                </div>
                <div class="p-3 rounded-xl bg-black/40 border border-white/10 text-center">
                    <p class="text-lg sm:text-xl font-black text-[#F5C518] font-mono">100%</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Immutable Audit Custody</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ── MAIN CONTENT CONTAINER ───────────────────────────────────────────── --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">

        {{-- ── SECTION 1: QUICKSTART & PERSONAS ─────────────────────────────── --}}
        <section id="quickstart" class="scroll-mt-24 space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <div>
                    <span class="text-[10px] font-mono uppercase tracking-widest text-[#F5C518] font-bold">CHAPTER 01 &bull; ACCESS GOVERNANCE</span>
                    <h2 class="text-xl sm:text-2xl font-black text-white mt-0.5">Pre-Seeded Operational Test Personas</h2>
                </div>
                <span class="hidden sm:inline-block px-2.5 py-1 rounded-lg text-xs font-mono bg-white/5 border border-white/10 text-gray-400">3 Tiers &bull; Default Password: <code class="text-emerald-300">password</code></span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Admin John --}}
                <div class="p-4 rounded-xl bg-[#0F1E14] border border-emerald-800/40 relative">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-purple-950 text-purple-300 border border-purple-800/60 font-bold">L5 Principal Lead</span>
                        <span class="text-xs font-mono text-emerald-300">hello@johnokyere.xyz</span>
                    </div>
                    <h3 class="text-base font-black text-white mt-2">John Okyere</h3>
                    <p class="text-xs text-emerald-400 font-mono">Cloud Infrastructure &amp; Principal SRE</p>
                    <p class="text-xs text-gray-300 mt-1 leading-relaxed">
                        Full administrative command, checklists definition, forensic audit log inspection, and system settings.
                    </p>
                    <div class="mt-3 pt-2 border-t border-white/10 flex items-center justify-between text-xs font-mono text-gray-400">
                        <span class="text-emerald-400 font-bold">Super Administrator</span>
                        <span>Role: admin</span>
                    </div>
                </div>

                {{-- Lead Abena --}}
                <div class="p-4 rounded-xl bg-[#0F1E14] border border-emerald-800/40 relative">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-950 text-emerald-300 border border-emerald-800/60 font-bold">L3 Shift Lead</span>
                        <span class="text-xs font-mono text-emerald-300">abena.owusu@example.com</span>
                    </div>
                    <h3 class="text-base font-black text-white mt-2">Abena Owusu</h3>
                    <p class="text-xs text-emerald-400 font-mono">Shift Supervisor &amp; Incident Commander</p>
                    <p class="text-xs text-gray-300 mt-1 leading-relaxed">
                        Task delegation, activity creation, incident flagging, and two-way shift handover sign-offs.
                    </p>
                    <div class="mt-3 pt-2 border-t border-white/10 flex items-center justify-between text-xs font-mono text-gray-400">
                        <span class="text-[#F5C518] font-bold">Shift Lead</span>
                        <span>Role: lead</span>
                    </div>
                </div>

                {{-- Engineer Kofi --}}
                <div class="p-4 rounded-xl bg-[#0F1E14] border border-emerald-800/40 relative">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-blue-950 text-blue-300 border border-blue-800/60 font-bold">L1 Support Operator</span>
                        <span class="text-xs font-mono text-emerald-300">kofi.asante@example.com</span>
                    </div>
                    <h3 class="text-base font-black text-white mt-2">Kofi Asante</h3>
                    <p class="text-xs text-emerald-400 font-mono">Operations Engineer (NOC)</p>
                    <p class="text-xs text-gray-300 mt-1 leading-relaxed">
                        Executes daily recurrence checks, provides resolution remarks, and participates in shift channels.
                    </p>
                    <div class="mt-3 pt-2 border-t border-white/10 flex items-center justify-between text-xs font-mono text-gray-400">
                        <span class="text-blue-300 font-bold">Support Engineer</span>
                        <span>Role: engineer</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── SECTION 2: ARCHITECTURE & STACK ──────────────────────────────── --}}
        <section id="architecture" class="scroll-mt-24 space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <div>
                    <span class="text-[10px] font-mono uppercase tracking-widest text-[#F5C518] font-bold">CHAPTER 02 &bull; TECHNICAL SPECIFICATION</span>
                    <h2 class="text-xl sm:text-2xl font-black text-white mt-0.5">SRE COMPREHENSIVE ARCHITECTURE</h2>
                </div>
                <span class="text-xs font-mono text-emerald-400">Modular Monolith + Multi-Tenant</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3.5 rounded-xl bg-black/40 border border-white/10">
                    <span class="text-[10px] font-mono text-gray-400 uppercase">Framework</span>
                    <h4 class="text-sm font-bold text-white mt-0.5">Laravel 11 LTS</h4>
                    <p class="text-[11px] text-gray-400 mt-1">Strict types, PSR-12, Form Requests</p>
                </div>
                <div class="p-3.5 rounded-xl bg-black/40 border border-white/10">
                    <span class="text-[10px] font-mono text-gray-400 uppercase">Real-Time</span>
                    <h4 class="text-sm font-bold text-white mt-0.5">Livewire 3 Real-Time Reactive Engine</h4>
                    <p class="text-[11px] text-gray-400 mt-1">Zero JS build overhead, reactive polling</p>
                </div>
                <div class="p-3.5 rounded-xl bg-black/40 border border-white/10">
                    <span class="text-[10px] font-mono text-gray-400 uppercase">Database</span>
                    <h4 class="text-sm font-bold text-white mt-0.5">MySQL 8.0+ InnoDB</h4>
                    <p class="text-[11px] text-gray-400 mt-1">Foreign keys, compound tenant indexes</p>
                </div>
                <div class="p-3.5 rounded-xl bg-black/40 border border-white/10">
                    <span class="text-[10px] font-mono text-gray-400 uppercase">Testing</span>
                    <h4 class="text-sm font-bold text-white mt-0.5">Pest 3 Test Suite</h4>
                    <p class="text-[11px] text-gray-400 mt-1">139 Feature &amp; isolation tests</p>
                </div>
            </div>

            {{-- Verification Commands --}}
            <div class="p-4 rounded-xl bg-black/30 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="text-xs font-bold text-white">Automated Verification Commands</span>
                    <p class="text-[11px] text-gray-400 mt-0.5">Run tests and trigger automated SRE compliance reports directly from the CLI:</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 font-mono text-xs">
                    <code class="px-2.5 py-1 rounded bg-black/70 border border-emerald-500/30 text-emerald-300">php artisan test</code>
                    <code class="px-2.5 py-1 rounded bg-black/70 border border-yellow-500/30 text-[#F5C518]">php artisan reports:send-automated</code>
                </div>
            </div>
        </section>

        {{-- ── SECTION 3: INTERACTIVE REST API REFERENCE ────────────────────── --}}
        <section id="api-reference" class="scroll-mt-24 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-white/10 pb-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-mono uppercase tracking-widest text-[#F5C518] font-bold">CHAPTER 03 &bull; REST API SPECIFICATION</span>
                        <span class="px-2 py-0.2 rounded-full text-[9px] font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">OpenAPI 3.0 Compatible</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-white mt-0.5">Opsora Developer REST API Reference</h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] text-gray-400 font-mono">Base URL: <code class="text-emerald-300">https://npontu-support-tracker.onrender.com/api/v1</code></span>
                </div>
            </div>

            {{-- Interactive API Endpoint Tabs --}}
            <div class="rounded-2xl bg-[#0B170F] border border-emerald-800/40 overflow-hidden shadow-xl">
                {{-- Tabs Bar --}}
                <div class="flex items-center overflow-x-auto border-b border-white/10 bg-black/40 p-1.5 gap-1 text-xs font-mono">
                    <button type="button"
                            @click="activeApiTab = 'auth'"
                            :class="activeApiTab === 'auth' ? 'bg-[#1B6B3A] text-white font-bold' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                            class="px-3 py-1.5 rounded-lg transition-colors cursor-pointer shrink-0">
                        POST /auth/login
                    </button>
                    <button type="button"
                            @click="activeApiTab = 'workspaces'"
                            :class="activeApiTab === 'workspaces' ? 'bg-[#1B6B3A] text-white font-bold' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                            class="px-3 py-1.5 rounded-lg transition-colors cursor-pointer shrink-0">
                        GET /workspaces
                    </button>
                    <button type="button"
                            @click="activeApiTab = 'join'"
                            :class="activeApiTab === 'join' ? 'bg-[#1B6B3A] text-white font-bold' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                            class="px-3 py-1.5 rounded-lg transition-colors cursor-pointer shrink-0">
                        POST /workspaces/join
                    </button>
                    <button type="button"
                            @click="activeApiTab = 'activities'"
                            :class="activeApiTab === 'activities' ? 'bg-[#1B6B3A] text-white font-bold' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                            class="px-3 py-1.5 rounded-lg transition-colors cursor-pointer shrink-0">
                        GET /activities
                    </button>
                    <button type="button"
                            @click="activeApiTab = 'handovers'"
                            :class="activeApiTab === 'handovers' ? 'bg-[#1B6B3A] text-white font-bold' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                            class="px-3 py-1.5 rounded-lg transition-colors cursor-pointer shrink-0">
                        POST /handovers
                    </button>
                    <button type="button"
                            @click="activeApiTab = 'health'"
                            :class="activeApiTab === 'health' ? 'bg-[#1B6B3A] text-white font-bold' : 'text-gray-400 hover:text-white hover:bg-white/5'"
                            class="px-3 py-1.5 rounded-lg transition-colors cursor-pointer shrink-0">
                        GET /health
                    </button>
                </div>

                {{-- Tab 1: Auth Login --}}
                <div x-show="activeApiTab === 'auth'" class="p-5 space-y-4">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2 font-mono">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-600 text-white">POST</span>
                            <span class="text-sm font-bold text-white">/api/v1/auth/login</span>
                        </div>
                        <span class="text-xs text-gray-400">Public &bull; Authenticates operator and issues Sanctum token</span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-xs font-mono">
                        <div>
                            <span class="text-gray-400 block mb-1.5">Request Body (JSON)</span>
                            <pre class="p-3.5 rounded-xl bg-black/60 border border-white/10 text-emerald-300 overflow-x-auto">{
  "email": "hello@johnokyere.xyz",
  "password": "password",
  "device_name": "Mobile Pixel 8"
}</pre>
                        </div>
                        <div>
                            <span class="text-gray-400 block mb-1.5">Response (200 OK)</span>
                            <pre class="p-3.5 rounded-xl bg-black/60 border border-white/10 text-gray-200 overflow-x-auto">{
  "token": "1|qP9xLz...sanctum_token...",
  "user": {
    "id": 1,
    "name": "John Okyere",
    "role": "admin"
  }
}</pre>
                        </div>
                    </div>
                </div>

                {{-- Tab 2: Workspaces Discovery --}}
                <div x-show="activeApiTab === 'workspaces'" x-cloak class="p-5 space-y-4">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2 font-mono">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white">GET</span>
                            <span class="text-sm font-bold text-white">/api/v1/workspaces</span>
                        </div>
                        <span class="text-xs text-gray-400">Requires Bearer Token &bull; Lists accessible workspaces &amp; roles</span>
                    </div>

                    <div class="text-xs font-mono">
                        <span class="text-gray-400 block mb-1.5">Response (200 OK)</span>
                        <pre class="p-3.5 rounded-xl bg-black/60 border border-white/10 text-gray-200 overflow-x-auto">{
  "data": [
    {
      "id": 1,
      "uuid": "8f2b1c4e-5a6b-4c3d-9e8f-1a2b3c4d5e6f",
      "name": "Primary SRE Operations",
      "slug": "primary-sre",
      "role": "admin",
      "is_personal": false,
      "organization": {
        "name": "Opsora SRE",
        "company_code": "OPS-DEFAULT"
      }
    }
  ]
}</pre>
                    </div>
                </div>

                {{-- Tab 3: Join by Code --}}
                <div x-show="activeApiTab === 'join'" x-cloak class="p-5 space-y-4">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2 font-mono">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-600 text-white">POST</span>
                            <span class="text-sm font-bold text-white">/api/v1/workspaces/join</span>
                        </div>
                        <span class="text-xs text-gray-400">Requires Bearer Token &bull; Fast Onboarding via Company Code</span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-xs font-mono">
                        <div>
                            <span class="text-gray-400 block mb-1.5">Request Body (JSON)</span>
                            <pre class="p-3.5 rounded-xl bg-black/60 border border-white/10 text-emerald-300 overflow-x-auto">{
  "company_code": "OPS-STARK9"
}</pre>
                        </div>
                        <div>
                            <span class="text-gray-400 block mb-1.5">Response (200 OK)</span>
                            <pre class="p-3.5 rounded-xl bg-black/60 border border-white/10 text-gray-200 overflow-x-auto">{
  "message": "Joined Stark Cloud Operations successfully.",
  "workspace": {
    "id": 4,
    "name": "Stark Primary Ops"
  }
}</pre>
                        </div>
                    </div>
                </div>

                {{-- Tab 4: Activities Feed --}}
                <div x-show="activeApiTab === 'activities'" x-cloak class="p-5 space-y-4">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2 font-mono">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white">GET</span>
                            <span class="text-sm font-bold text-white">/api/v1/activities</span>
                        </div>
                        <span class="text-xs text-gray-400">Header: <code class="text-emerald-300 font-mono">X-Workspace-Id: 1</code> &bull; Tenant-scoped operational feed</span>
                    </div>

                    <div class="text-xs font-mono">
                        <span class="text-gray-400 block mb-1.5">Response (200 OK)</span>
                        <pre class="p-3.5 rounded-xl bg-black/60 border border-white/10 text-gray-200 overflow-x-auto">{
  "data": [
    {
      "id": 14,
      "title": "USSD Gateway Latency Probe",
      "recurrence": "daily",
      "priority": "critical",
      "status": "pending",
      "assigned_to": "Kofi Asante"
    }
  ]
}</pre>
                    </div>
                </div>

                {{-- Tab 5: Shift Handovers --}}
                <div x-show="activeApiTab === 'handovers'" x-cloak class="p-5 space-y-4">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2 font-mono">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-600 text-white">POST</span>
                            <span class="text-sm font-bold text-white">/api/v1/handovers</span>
                        </div>
                        <span class="text-xs text-gray-400">Shift custody sign-off &bull; Triggers notification &amp; forensic seal</span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-xs font-mono">
                        <div>
                            <span class="text-gray-400 block mb-1.5">Request Body (JSON)</span>
                            <pre class="p-3.5 rounded-xl bg-black/60 border border-white/10 text-emerald-300 overflow-x-auto">{
  "shift_date": "2026-09-18",
  "shift_type": "evening",
  "summary": "Core database replication lag verified normal. Gateway queues green.",
  "open_issues": "None. Standby engineer notified."
}</pre>
                        </div>
                        <div>
                            <span class="text-gray-400 block mb-1.5">Response (201 Created)</span>
                            <pre class="p-3.5 rounded-xl bg-black/60 border border-white/10 text-gray-200 overflow-x-auto">{
  "status": "success",
  "message": "Shift handover briefing recorded. Awaiting oncoming lead signature.",
  "handover_id": 92
}</pre>
                        </div>
                    </div>
                </div>

                {{-- Tab 6: Health Check --}}
                <div x-show="activeApiTab === 'health'" x-cloak class="p-5 space-y-4">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2 font-mono">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white">GET</span>
                            <span class="text-sm font-bold text-white">/api/health</span>
                        </div>
                        <span class="text-xs text-gray-400">Public Probe &bull; JSON performance &amp; DB connectivity telemetry</span>
                    </div>

                    <div class="text-xs font-mono">
                        <span class="text-gray-400 block mb-1.5">Response (200 OK)</span>
                        <pre class="p-3.5 rounded-xl bg-black/60 border border-white/10 text-gray-200 overflow-x-auto">{
  "status": "ok",
  "database": "connected",
  "database_latency_ms": 1.2,
  "telemetry_stream": "nominal",
  "uptime": "99.98%"
}</pre>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── SECTION 4: MOBILE & EMULATOR SETUP GUIDE ─────────────────────── --}}
        <section id="mobile-setup" class="scroll-mt-24 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-white/10 pb-3">
                <div>
                    <span class="text-[10px] font-mono uppercase tracking-widest text-[#F5C518] font-bold">CHAPTER 04 &bull; CLIENT RUNTIME</span>
                    <h2 class="text-xl sm:text-2xl font-black text-white mt-0.5">Mobile Companion &amp; Local Emulator Setup Guide</h2>
                </div>
                <span class="text-xs font-mono text-emerald-300">Flutter 3.24+ &bull; Dart 3.5+</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Target 1: Windows Desktop --}}
                <div class="p-4 rounded-xl bg-[#0F1E14] border border-emerald-800/40 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-mono font-bold text-[#F5C518] uppercase">Target 1: Windows Desktop</span>
                        <p class="text-xs text-white font-semibold mt-1">Native PC Desktop Execution</p>
                        <p class="text-xs text-gray-400 mt-1">Fastest local testing without an emulator. Launches directly in Windows.</p>
                    </div>
                    <pre class="mt-3 p-2 rounded bg-black/60 border border-white/10 text-[11px] font-mono text-emerald-300 overflow-x-auto">cd npontu_sre_mobile
flutter run -d windows</pre>
                </div>

                {{-- Target 2: Android Emulator --}}
                <div class="p-4 rounded-xl bg-[#0F1E14] border border-emerald-800/40 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-mono font-bold text-emerald-400 uppercase">Target 2: Android Emulator (10.0.2.2)</span>
                        <p class="text-xs text-white font-semibold mt-1">Pixel 8 / API 34 Virtual Device</p>
                        <p class="text-xs text-gray-400 mt-1">Uses <code class="text-[#F5C518]">10.0.2.2:8000</code> to reach host PC backend from Android sandbox.</p>
                    </div>
                    <pre class="mt-3 p-2 rounded bg-black/60 border border-white/10 text-[11px] font-mono text-emerald-300 overflow-x-auto">emulator -avd Pixel_8_API_34
flutter run -d emulator-5554</pre>
                </div>

                {{-- Target 3: Quality Gate --}}
                <div class="p-4 rounded-xl bg-[#0F1E14] border border-emerald-800/40 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-mono font-bold text-blue-400 uppercase">Target 3: Verification Suite</span>
                        <p class="text-xs text-white font-semibold mt-1">Automated Quality Gate</p>
                        <p class="text-xs text-gray-400 mt-1">Executes 25 mobile tests covering models, offline sync, and widgets.</p>
                    </div>
                    <pre class="mt-3 p-2 rounded bg-black/60 border border-white/10 text-[11px] font-mono text-emerald-300 overflow-x-auto">flutter test
flutter analyze</pre>
                </div>
            </div>
        </section>

        {{-- ── SECTION 5: TWO-WAY SHIFT HANDOVER PROTOCOL ───────────────────── --}}
        <section id="handover-flow" class="scroll-mt-24 space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <div>
                    <span class="text-[10px] font-mono uppercase tracking-widest text-[#F5C518] font-bold">CHAPTER 05 &bull; OPERATIONAL DISCIPLINE</span>
                    <h2 class="text-xl sm:text-2xl font-black text-white mt-0.5">The 4-Phase Two-Way Handover Protocol</h2>
                </div>
                <span class="text-xs font-mono text-yellow-400">Zero Unacknowledged Handovers</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="p-3.5 rounded-xl bg-black/40 border border-white/10">
                    <span class="text-[10px] font-mono text-emerald-400 font-bold">PHASE 01</span>
                    <h4 class="text-sm font-bold text-white mt-0.5">Live Verification</h4>
                    <p class="text-xs text-gray-400 mt-1">Review active checklist status and log blocker remarks.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-black/40 border border-white/10">
                    <span class="text-[10px] font-mono text-[#F5C518] font-bold">PHASE 02</span>
                    <h4 class="text-sm font-bold text-white mt-0.5">Outgoing Sign-Off</h4>
                    <p class="text-xs text-gray-400 mt-1">Lead generates briefing and signs off with cryptographic timestamp.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-black/40 border border-white/10">
                    <span class="text-[10px] font-mono text-blue-400 font-bold">PHASE 03</span>
                    <h4 class="text-sm font-bold text-white mt-0.5">Incoming Sign-On</h4>
                    <p class="text-xs text-gray-400 mt-1">Oncoming lead accepts operational custody and acknowledges open tickets.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-black/40 border border-white/10">
                    <span class="text-[10px] font-mono text-purple-400 font-bold">PHASE 04</span>
                    <h4 class="text-sm font-bold text-white mt-0.5">Forensic Seal</h4>
                    <p class="text-xs text-gray-400 mt-1">Immutable SIEM audit log seal generated. Dispatches @Mention Email Receipts.</p>
                </div>
            </div>
        </section>

        {{-- ── SECTION 6: GOVERNANCE & SLA ──────────────────────────────────── --}}
        <section id="governance" class="scroll-mt-24 space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <div>
                    <span class="text-[10px] font-mono uppercase tracking-widest text-[#F5C518] font-bold">CHAPTER 06 &bull; COMPLIANCE &amp; SLA</span>
                    <h2 class="text-xl sm:text-2xl font-black text-white mt-0.5">Governance, SLA &amp; Automated Reports</h2>
                </div>
                <span class="text-xs font-mono text-emerald-300">99.98% SLA Guaranteed</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-black/30 border border-white/10 space-y-2">
                    <h4 class="font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>Security &amp; SIEM Audit Standard</span>
                    </h4>
                    <p class="text-gray-300 leading-relaxed">
                        Every state mutation produces an immutable audit record containing actor snapshots, IP address, before/after JSON diffs, and timestamp.
                    </p>
                    <p class="text-gray-400">
                        Inactivity Guard: Sessions automatically terminate after <strong>120 minutes of inactivity</strong> to prevent cockpit terminal exposure.
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-black/30 border border-white/10 space-y-2">
                    <h4 class="font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#F5C518]"></span>
                        <span>Automated Scheduled Reporting</span>
                    </h4>
                    <p class="text-gray-300 leading-relaxed">
                        Daily EOD summaries (23:59 UTC), weekly digests, and monthly executive compliance reports are scheduled automatically via Laravel console crons.
                    </p>
                    <p class="text-gray-400 font-mono">
                        CLI Trigger: <code class="text-emerald-300">php artisan reports:send-automated</code>
                    </p>
                </div>
            </div>
        </section>

        {{-- ── SECTION 7: INTERACTIVE FAQ ACCORDION ─────────────────────────── --}}
        <section id="faq" class="scroll-mt-24 space-y-4 pt-4">
            <div class="text-center max-w-xl mx-auto mb-6">
                <span class="text-[10px] font-mono uppercase tracking-widest text-[#F5C518] font-bold">KNOWLEDGE BASE</span>
                <h2 class="text-2xl sm:text-3xl font-black text-white mt-1">Frequently Asked Questions</h2>
                <p class="text-xs text-gray-400 mt-1">Authoritative answers on custody transfers, session timeouts, and audit immutability.</p>
            </div>

            <div class="max-w-3xl mx-auto space-y-2.5 text-xs sm:text-sm">
                {{-- FAQ 1 --}}
                <div class="rounded-xl bg-[#0C1A12] border border-emerald-900/40 overflow-hidden">
                    <button type="button"
                            @click="openFaq = openFaq === 1 ? null : 1"
                            class="w-full p-3.5 sm:p-4 text-left flex items-center justify-between gap-3 cursor-pointer hover:bg-white/5 transition-colors">
                        <span class="font-bold text-white flex items-center gap-2.5">
                            <span class="text-[#F5C518] font-mono font-bold">Q1</span>
                            <span>How does the two-way handover custody transfer work mathematically?</span>
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="openFaq === 1 ? 'rotate-180 text-[#F5C518]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openFaq === 1" class="p-4 pt-0 text-gray-300 leading-relaxed border-t border-white/5 bg-black/20 text-xs">
                        The handover is a 2-state operational contract: (1) Outgoing Sign-Off timestamps outgoing lead custody and sets status to awaiting incoming review. (2) Incoming Sign-On requires oncoming lead verification remarks before shifting operational custody and creating an immutable SIEM audit log.
                    </div>
                </div>

                {{-- FAQ 2 --}}
                <div class="rounded-xl bg-[#0C1A12] border border-emerald-900/40 overflow-hidden">
                    <button type="button"
                            @click="openFaq = openFaq === 2 ? null : 2"
                            class="w-full p-3.5 sm:p-4 text-left flex items-center justify-between gap-3 cursor-pointer hover:bg-white/5 transition-colors">
                        <span class="font-bold text-white flex items-center gap-2.5">
                            <span class="text-[#F5C518] font-mono font-bold">Q2</span>
                            <span>What happens when an engineer's session times out after 120 minutes of inactivity?</span>
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="openFaq === 2 ? 'rotate-180 text-[#F5C518]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openFaq === 2" class="p-4 pt-0 text-gray-300 leading-relaxed border-t border-white/5 bg-black/20 text-xs">
                        To protect unattended terminals, sessions expire after 120 minutes of inactivity. Livewire intercepts the expired token and smoothly routes to <code class="text-emerald-300 font-mono">/login?expired=1</code> with security notifications.
                    </div>
                </div>

                {{-- FAQ 3 --}}
                <div class="rounded-xl bg-[#0C1A12] border border-emerald-900/40 overflow-hidden">
                    <button type="button"
                            @click="openFaq = openFaq === 3 ? null : 3"
                            class="w-full p-3.5 sm:p-4 text-left flex items-center justify-between gap-3 cursor-pointer hover:bg-white/5 transition-colors">
                        <span class="font-bold text-white flex items-center gap-2.5">
                            <span class="text-[#F5C518] font-mono font-bold">Q3</span>
                            <span>Can audit log records ever be deleted or edited by administrators?</span>
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="openFaq === 3 ? 'rotate-180 text-[#F5C518]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openFaq === 3" class="p-4 pt-0 text-gray-300 leading-relaxed border-t border-white/5 bg-black/20 text-xs">
                        <strong>No.</strong> The <code class="text-emerald-300 font-mono">audit_logs</code> table is architecturally append-only. Database permissions restrict application users to INSERT and SELECT only.
                    </div>
                </div>

                {{-- FAQ 4 --}}
                <div class="rounded-xl bg-[#0C1A12] border border-emerald-900/40 overflow-hidden">
                    <button type="button"
                            @click="openFaq = openFaq === 4 ? null : 4"
                            class="w-full p-3.5 sm:p-4 text-left flex items-center justify-between gap-3 cursor-pointer hover:bg-white/5 transition-colors">
                        <span class="font-bold text-white flex items-center gap-2.5">
                            <span class="text-[#F5C518] font-mono font-bold">Q4</span>
                            <span>How do automated daily, weekly, and monthly reports work?</span>
                        </span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="openFaq === 4 ? 'rotate-180 text-[#F5C518]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openFaq === 4" class="p-4 pt-0 text-gray-300 leading-relaxed border-t border-white/5 bg-black/20 text-xs">
                        Automated reports are powered by <code class="text-emerald-300 font-mono">php artisan reports:send-automated</code> scheduled in <code class="text-emerald-300 font-mono">routes/console.php</code> for Daily (23:59 UTC), Weekly (Sundays), and Monthly (28th).
                    </div>
                </div>
            </div>
        </section>

    </main>

    {{-- ── GLOBAL ENTERPRISE FOOTER ─────────────────────────────────────────── --}}
    @include('layouts.partials.footer')

    {{-- Live UTC Clock --}}
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
