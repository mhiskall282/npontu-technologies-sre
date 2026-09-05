<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'Npontu Technologies Support Activity Tracker - Operational Policies, Compliance, and SRE Standards')">
    <title>@yield('title', 'Legal & Operational Standards') — Support Tracker — Npontu Technologies</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="min-h-full bg-[#09130D] text-white flex flex-col antialiased selection:bg-[#F5C518] selection:text-gray-900">

    {{-- Top Operational Status Banner --}}
    <div class="bg-[#050B07] border-b border-white/5 py-1.5 px-4 text-center text-[11px] text-gray-400 font-mono">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 truncate">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                <span class="truncate">PRODUCTION NOC: ACCRA-CLUSTER-01</span>
                <span class="hidden sm:inline text-gray-600">|</span>
                <span class="hidden sm:inline text-emerald-300">ISO 27001 &amp; SOC 2 TYPE II ALIGNED</span>
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

    {{-- Header --}}
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
                <a href="{{ route('landing') }}" class="hover:text-[#F5C518] transition-colors">The Platform</a>
                <a href="{{ route('docs') }}" class="hover:text-[#F5C518] transition-colors">Docs &amp; Guide</a>
                <a href="{{ route('health') }}" class="hover:text-[#F5C518] transition-colors">Telemetry HUD</a>
                <a href="{{ route('policy.privacy') }}" class="text-[#F5C518] font-bold">Legal &amp; SRE Policies</a>
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
                        id="policies-mobile-toggle-btn"
                        onclick="togglePoliciesNav()"
                        aria-expanded="false"
                        aria-label="Toggle navigation menu"
                        class="md:hidden p-2 rounded-xl bg-white/5 border border-white/10 text-gray-300 hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus:ring-2 focus:ring-[#F5C518]">
                    <svg id="policies-hamburger-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg id="policies-close-icon" class="w-5 h-5 hidden text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile Collapsible Navigation Menu --}}
        <div id="policies-mobile-menu"
             class="hidden md:hidden bg-[#0A1810]/98 border-b border-emerald-900/40 px-4 pt-3 pb-5 space-y-1 shadow-2xl backdrop-blur-xl">
            <div class="px-3 pb-2 text-[10px] font-mono font-bold uppercase tracking-wider text-gray-400">
                Navigation &amp; SRE Policies
            </div>
            <a href="{{ route('landing') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-200 hover:text-[#F5C518] hover:bg-white/5 transition-colors flex items-center justify-between">
                <span>&larr; Platform Overview</span>
                <span class="text-[10px] font-mono text-gray-400">Home</span>
            </a>
            <a href="{{ route('docs') }}" class="block px-3 py-2 rounded-lg text-sm font-bold text-[#F5C518] hover:bg-white/5 transition-colors flex items-center justify-between">
                <span>Docs &amp; Architecture Guide</span>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-[#F5C518]/20 text-[#F5C518] uppercase">Manual</span>
            </a>
            <a href="{{ route('policy.privacy') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('policy.privacy') ? 'text-[#F5C518] font-bold bg-white/5' : 'text-gray-200 hover:text-[#F5C518] hover:bg-white/5' }} transition-colors">
                Privacy Policy
            </a>
            <a href="{{ route('policy.terms') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('policy.terms') ? 'text-[#F5C518] font-bold bg-white/5' : 'text-gray-200 hover:text-[#F5C518] hover:bg-white/5' }} transition-colors">
                Terms of Service
            </a>
            <a href="{{ route('policy.sla') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('policy.sla') ? 'text-[#F5C518] font-bold bg-white/5' : 'text-gray-200 hover:text-[#F5C518] hover:bg-white/5' }} transition-colors">
                SLA &amp; Incident Protocols
            </a>
            <a href="{{ route('health') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-emerald-300 hover:bg-white/5 transition-colors flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Real-Time Health HUD</span>
                </span>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-950 border border-emerald-500/30 text-emerald-300">99.98% SLA</span>
            </a>
        </div>
    </header>

    {{-- Sub-header with Policy Tabs --}}
    <section class="bg-gradient-to-b from-[#122A1C] to-[#0A1810] border-b border-emerald-900/30 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-mono text-emerald-400 mb-2">
                        <a href="{{ route('landing') }}" class="hover:underline">Home</a>
                        <span>/</span>
                        <span class="text-gray-400">Governance & Compliance</span>
                        <span>/</span>
                        <span class="text-[#F5C518]">@yield('breadcrumb_current', 'Policy')</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">@yield('page_heading', 'Legal & Compliance Policy')</h1>
                    <p class="text-xs sm:text-sm text-green-100/70 mt-1 max-w-2xl">
                        Operational compliance standards, data handling procedures, cryptographic audit mandates, and uptime SLA commitments for Npontu SRE systems.
                    </p>
                </div>
                <div class="flex items-center gap-2 font-mono text-xs text-gray-400 bg-black/40 border border-white/10 rounded-xl px-3 py-2 self-start md:self-auto">
                    <span class="text-emerald-400 font-bold">Revision:</span>
                    <span>2026.1 (Active)</span>
                    <span class="text-gray-600">•</span>
                    <span class="text-gray-400">Npontu Legal</span>
                </div>
            </div>

            {{-- Policy Tabs --}}
            <div class="flex flex-wrap items-center gap-2 mt-6 pt-4 border-t border-white/10">
                <a href="{{ route('policy.privacy') }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ request()->routeIs('policy.privacy') ? 'bg-[#F5C518] text-gray-950 shadow-md' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                    Privacy & Data Policy
                </a>
                <a href="{{ route('policy.terms') }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ request()->routeIs('policy.terms') ? 'bg-[#F5C518] text-gray-950 shadow-md' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                    Acceptable Use & Terms
                </a>
                <a href="{{ route('policy.security') }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ request()->routeIs('policy.security') ? 'bg-[#F5C518] text-gray-950 shadow-md' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                    Security & SIEM Audit Standard
                </a>
                <a href="{{ route('policy.sla') }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ request()->routeIs('policy.sla') ? 'bg-[#F5C518] text-gray-950 shadow-md' : 'bg-white/5 text-gray-300 hover:bg-white/10' }}">
                    99.98% SLA & Escalation
                </a>
            </div>
        </div>
    </section>

    {{-- Policy Main Content --}}
    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-[#0C1A12] border border-emerald-900/40 rounded-2xl p-6 sm:p-10 shadow-2xl">
            @yield('content')
        </div>
    </main>

    {{-- Enterprise Comprehensive Footer --}}
    @include('layouts.partials.footer')

    {{-- Live UTC Clock & Policies Mobile Menu Controller --}}
    <script>
        function togglePoliciesNav() {
            const menu = document.getElementById('policies-mobile-menu');
            const hamburger = document.getElementById('policies-hamburger-icon');
            const close = document.getElementById('policies-close-icon');
            const btn = document.getElementById('policies-mobile-toggle-btn');
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
