<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Opsora SRE') }} — Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-[#0F1A14]">
    {{-- Splash Screen Loader for Guest/Auth (Smooth Opsora Slide & Pulse) --}}
    <div id="guest-splash-screen" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-[#07100B] transition-opacity duration-300 pointer-events-auto">
        <div class="flex flex-col items-center gap-4 text-center max-w-xs px-4">
            <div class="relative flex items-center justify-center w-16 h-16 rounded-2xl bg-black/40 border border-emerald-500/30 shadow-2xl p-2.5">
                <img src="{{ asset('images/opsora-icon.svg') }}" alt="Opsora SRE" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-white tracking-tight">Opsora SRE</h1>
                <p class="text-[10px] text-[#F5C518] font-mono tracking-widest uppercase mt-0.5 font-semibold">OPSORA SRE OPERATIONS</p>
            </div>
            <div class="w-40 h-1 bg-white/10 rounded-full overflow-hidden mt-2">
                <div class="h-full bg-gradient-to-r from-[#1B6B3A] via-[#F5C518] to-emerald-400 w-full animate-pulse rounded-full"></div>
            </div>
            <div class="flex items-center gap-2 mt-1 text-xs text-gray-400 font-mono">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Initializing Opsora SRE...</span>
            </div>
        </div>
    </div>
    <script>
        (function() {
            function dismissSplash() {
                const splash = document.getElementById('guest-splash-screen');
                if (splash && !splash.dataset.dismissed) {
                    splash.dataset.dismissed = 'true';
                    splash.style.pointerEvents = 'none';
                    splash.style.opacity = '0';
                    setTimeout(() => splash.remove(), 300);
                }
            }
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                setTimeout(dismissSplash, 200);
            } else {
                document.addEventListener('DOMContentLoaded', () => setTimeout(dismissSplash, 200));
                window.addEventListener('load', () => setTimeout(dismissSplash, 100));
            }
            // Absolute fallback timeout
            setTimeout(dismissSplash, 500);
        })();
    </script>
    {{ $slot }}
</body>
</html>
