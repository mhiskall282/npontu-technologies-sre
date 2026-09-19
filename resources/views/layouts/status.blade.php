<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Opsora SRE — Live System Health, Availability Telemetry, and Software Status Dashboard">
    <title>@yield('title', 'System Health & Software Status') — Opsora SRE Status</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/opsora-icon.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full bg-[#F4F7F5] dark:bg-[#07100B] font-sans antialiased text-gray-900 dark:text-gray-100 flex flex-col selection:bg-[#F5C518] selection:text-gray-900">

    {{-- ── Standalone Status Navigation Bar (No App Sidebar, No Breadcrumbs) ───── --}}
    <header class="sticky top-0 z-50 bg-[#0A140E] backdrop-blur-md border-b border-[#14261B] text-white shadow-md" style="background-color: #0A140E; border-bottom: 1px solid #14261B;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            
            {{-- Brand Logo & Status Identification --}}
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('landing') }}" class="flex items-center gap-2.5 sm:gap-3 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-950/80 to-black/80 border border-[#F5C518]/30 flex items-center justify-center shadow-lg group-hover:border-[#F5C518] transition-all shrink-0 p-1">
                        <img src="{{ asset('images/opsora-icon.svg') }}" alt="Opsora SRE" class="w-full h-full object-contain group-hover:scale-105 transition-transform">
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-extrabold text-sm sm:text-base tracking-tight text-white block leading-none">OPSORA</span>
                            <span class="px-1.5 py-0.5 rounded bg-[#F5C518]/20 border border-[#F5C518]/50 text-[#F5C518] text-[9px] sm:text-[10px] font-bold font-mono">STATUS</span>
                        </div>
                        <span class="block text-emerald-400 text-[8px] sm:text-[9px] font-mono tracking-widest uppercase mt-0.5 font-bold">PUBLIC TELEMETRY CONSOLE</span>
                    </div>
                </a>
            </div>

            {{-- Center: Live Operational Indicator (Desktop) --}}
            <div class="hidden lg:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-950/70 border border-emerald-500/30 text-xs font-semibold text-emerald-300">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="font-mono tracking-wide text-[11px]">ALL SYSTEMS OPERATIONAL</span>
            </div>

            {{-- Right: Telemetry Actions & Access --}}
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                {{-- Live UTC Clock --}}
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-[11px] font-mono text-gray-300">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="status-utc-clock">UTC --:--:--</span>
                </div>

                {{-- Raw JSON API Probe Link --}}
                <a href="{{ route('health', ['format' => 'json']) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white text-xs font-mono border border-white/10 transition-colors"
                   title="Raw JSON Telemetry Probe Output">
                    <span>{ } JSON API</span>
                </a>

                {{-- Sign In / Dashboard CTA --}}
                @auth
                    <a href="{{ route('activities.daily') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold text-xs shadow-md transition-colors whitespace-nowrap">
                        <span>SRE Cockpit</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-bold text-xs shadow-md transition-colors whitespace-nowrap">
                        <span>Sign In</span>
                        <svg class="w-3.5 h-3.5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- ── Main Standalone Content Canvas (Full Width Responsive) ─────────────── --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 flex-1 w-full">
        @yield('content')
    </main>

    {{-- ── Standalone Status Footer ───────────────────────────────────────────── --}}
    <footer class="bg-[#0A140E] border-t border-[#14261B] text-gray-400 text-xs py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="font-bold text-white font-mono tracking-wide">OPSORA SRE</span>
                <span class="text-gray-600">&bull;</span>
                <span class="text-xs text-gray-400 font-mono">Continuous Telemetry &amp; Availability Engine</span>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-4 text-[11px] font-mono">
                <a href="{{ route('landing') }}" class="hover:text-[#F5C518] transition-colors">Platform Home</a>
                <span class="text-gray-600">&bull;</span>
                <a href="{{ route('docs') }}" class="hover:text-[#F5C518] transition-colors">Docs &amp; API</a>
                <span class="text-gray-600">&bull;</span>
                <a href="{{ route('policy.sla') }}" class="hover:text-[#F5C518] transition-colors">SLA 99.98% Commitment</a>
                <span class="text-gray-600">&bull;</span>
                <a href="{{ route('policy.privacy') }}" class="hover:text-[#F5C518] transition-colors">Privacy Policy</a>
                <span class="text-gray-600">&bull;</span>
                <a href="{{ route('policy.terms') }}" class="hover:text-[#F5C518] transition-colors">Terms</a>
            </div>
            <div class="text-[11px] text-gray-400 font-mono">
                &copy; {{ date('Y') }} Opsora SRE &bull; Npontu Technologies
            </div>
        </div>
    </footer>

    @livewireScripts
    <script>
        // Live UTC Clock updater
        function updateStatusUtcClock() {
            const clockEl = document.getElementById('status-utc-clock');
            if (clockEl) {
                const now = new Date();
                const hours = String(now.getUTCHours()).padStart(2, '0');
                const minutes = String(now.getUTCMinutes()).padStart(2, '0');
                const seconds = String(now.getUTCSeconds()).padStart(2, '0');
                clockEl.textContent = `UTC ${hours}:${minutes}:${seconds}`;
            }
        }
        setInterval(updateStatusUtcClock, 1000);
        updateStatusUtcClock();
    </script>
</body>
</html>
