<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Operational Notice') — Support Tracker — Npontu Technologies</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="h-full bg-[#0F1A14] text-white flex flex-col antialiased selection:bg-[#F5C518] selection:text-gray-900">
    {{-- Header --}}
    <header class="border-b border-[#1A2E22] px-6 py-4 flex items-center justify-between bg-[#0A120E]/80 backdrop-blur-sm">
        <a href="{{ url('/') }}" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center shadow-sm group-hover:border-[#F5C518]/50 transition-colors">
                <svg class="w-5 h-5 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                    <polygon points="16,3 30,27 2,27"/>
                </svg>
            </div>
            <div>
                <span class="font-bold text-sm tracking-tight text-white block leading-none">Support Tracker</span>
                <span class="block text-[#F5C518] text-[9px] font-mono tracking-widest uppercase mt-0.5 font-semibold">NPONTU TECHNOLOGIES</span>
            </div>
        </a>

        <div class="flex items-center gap-3">
            <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white/5 border border-white/10 text-[11px] font-mono text-gray-400">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span id="err-clock">UTC --:--:--</span>
            </div>
            <a href="{{ route('health') }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-950/60 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-900/60 transition-colors">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>System Health</span>
            </a>
        </div>
    </header>

    {{-- Main Error Container --}}
    <main class="flex-1 flex items-center justify-center p-6 relative overflow-hidden">
        {{-- Background geometric decoration --}}
        <div class="absolute inset-0 pointer-events-none opacity-5 flex items-center justify-center">
            <svg class="w-[600px] h-[600px] text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                <polygon points="16,3 30,27 2,27"/>
            </svg>
        </div>

        <div class="max-w-xl w-full relative z-10 text-center">
            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="border-t border-[#1A2E22] px-6 py-4 text-center text-xs text-gray-500 bg-[#0A120E]/50">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>&copy; {{ date('Y') }} Npontu Technologies. Operations SRE Platform.</span>
            <div class="flex items-center gap-4 text-gray-400">
                <a href="{{ route('activities.daily') }}" class="hover:text-white transition-colors">Today's Board</a>
                <span>&bull;</span>
                <a href="{{ route('health') }}" class="hover:text-white transition-colors">Telemetry HUD</a>
                <span>&bull;</span>
                <a href="{{ route('policy.privacy') }}" class="hover:text-white transition-colors">Privacy</a>
                <span>&bull;</span>
                <a href="{{ route('policy.terms') }}" class="hover:text-white transition-colors">Terms</a>
            </div>
        </div>
    </footer>

    <script>
        (function() {
            function updateClock() {
                const el = document.getElementById('err-clock');
                if (el) {
                    const now = new Date();
                    const h = String(now.getUTCHours()).padStart(2, '0');
                    const m = String(now.getUTCMinutes()).padStart(2, '0');
                    const s = String(now.getUTCSeconds()).padStart(2, '0');
                    el.textContent = `UTC ${h}:${m}:${s}`;
                }
            }
            setInterval(updateClock, 1000);
            updateClock();
        })();
    </script>
</body>
</html>
