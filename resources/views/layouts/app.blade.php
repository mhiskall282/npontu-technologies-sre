<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Opsora SRE — Site Reliability Engineering & Shift Operations Platform">
    <title>@yield('title', config('app.name', 'Opsora SRE')) — Opsora SRE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full bg-[#F4F7F5] dark:bg-[#07100B] font-sans antialiased text-gray-900 dark:text-gray-100">
    {{-- Splash Screen Loader for App (Smooth slide-in, glowing emblem, and progress animatic) --}}
    <div id="app-splash-screen" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-[#0B1911] transition-all duration-500 pointer-events-auto select-none">
        <style>
            @keyframes opsoraSlideUp {
                0% { opacity: 0; transform: translateY(24px) scale(0.95); }
                100% { opacity: 1; transform: translateY(0) scale(1); }
            }
            @keyframes opsoraPulseGlow {
                0%, 100% { filter: drop-shadow(0 0 12px rgba(245, 197, 24, 0.45)); }
                50% { filter: drop-shadow(0 0 28px rgba(245, 197, 24, 0.85)); }
            }
            @keyframes opsoraLoadBar {
                0% { width: 0%; }
                50% { width: 72%; }
                100% { width: 100%; }
            }
            .opsora-splash-card {
                animation: opsoraSlideUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }
            .opsora-emblem-glow {
                animation: opsoraPulseGlow 2.2s infinite ease-in-out;
            }
            .opsora-progress-fill {
                animation: opsoraLoadBar 0.8s ease-in-out forwards;
            }
        </style>
        <div class="opsora-splash-card flex flex-col items-center gap-5 text-center px-6 max-w-xs">
            <div class="opsora-emblem-glow relative flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-950/80 to-black/80 border border-[#F5C518]/40 shadow-2xl p-2.5">
                <img src="{{ asset('images/opsora-icon.svg') }}" alt="Opsora SRE" class="w-full h-full object-contain">
            </div>
            <div>
                <div class="flex items-center justify-center gap-2">
                    <h1 class="text-2xl font-black text-white tracking-wider font-mono">OPSORA</h1>
                    <span class="px-1.5 py-0.5 rounded bg-[#F5C518]/20 border border-[#F5C518]/60 text-[#F5C518] text-xs font-black tracking-widest font-mono">SRE</span>
                </div>
                <p class="text-[10px] text-emerald-300/80 font-mono tracking-widest uppercase mt-1 font-semibold">Reliability Operations Platform</p>
            </div>
            <!-- Animated Loading Progress Bar -->
            <div class="w-48 h-1.5 bg-white/10 rounded-full overflow-hidden border border-white/5 mt-1">
                <div class="opsora-progress-fill h-full bg-gradient-to-r from-[#1B6B3A] via-[#F5C518] to-[#1B6B3A] rounded-full"></div>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#1B6B3A] animate-ping"></span>
                <span class="text-xs text-gray-400 font-mono" id="splash-status-text">Initializing telemetry nodes...</span>
            </div>
        </div>
    </div>
    <script>
        (function() {
            const statusEl = document.getElementById('splash-status-text');
            if (statusEl) {
                setTimeout(() => { if (statusEl) statusEl.textContent = 'Verifying security credentials...'; }, 250);
                setTimeout(() => { if (statusEl) statusEl.textContent = 'Opsora SRE Ready.'; }, 550);
            }
            function dismissSplash() {
                const splash = document.getElementById('app-splash-screen');
                if (splash && !splash.dataset.dismissed) {
                    splash.dataset.dismissed = 'true';
                    splash.style.pointerEvents = 'none';
                    splash.style.opacity = '0';
                    splash.style.transform = 'scale(1.02)';
                    setTimeout(() => splash.remove(), 350);
                }
            }
            if (document.readyState === 'complete') {
                setTimeout(dismissSplash, 600);
            } else {
                window.addEventListener('load', () => setTimeout(dismissSplash, 600));
            }
            setTimeout(dismissSplash, 1200);
        })();
    </script>

    {{-- Main App Layout Container: Left Sidebar + Right Content Area --}}
    <div class="min-h-screen flex flex-col md:flex-row">

        {{-- ── Mobile Navigation Header (< md) ────────────────────────────────── --}}
        <header class="md:hidden bg-[#0F1A14] text-white h-16 px-4 flex items-center justify-between border-b border-[#1A2E22] sticky top-0 z-40 no-print shadow-md">
            <div class="flex items-center gap-3">
                <button type="button"
                        onclick="openMobileSidebar()"
                        class="p-2 text-gray-300 hover:text-white hover:bg-[#1A2E22] rounded-lg focus:outline-none cursor-pointer"
                        aria-label="Open sidebar menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="{{ route('activities.daily') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/opsora-icon.svg') }}" alt="Opsora SRE" class="w-7 h-7">
                    <div class="flex items-center gap-1.5">
                        <span class="font-black text-sm tracking-wide text-white">OPSORA</span>
                        <span class="px-1 py-0.2 rounded bg-[#F5C518]/20 border border-[#F5C518]/50 text-[#F5C518] text-[10px] font-bold font-mono">SRE</span>
                    </div>
                </a>
            </div>
            <div class="flex items-center gap-2">
                @auth
                @php $unreadCommsMobile = auth()->user()->unreadMessagesCount(); @endphp
                <a href="{{ route('messages.index') }}" class="relative p-2 text-gray-300 hover:text-white rounded-lg" title="Ops Comms">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    @if($unreadCommsMobile > 0)
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-[#E63946] rounded-full ring-2 ring-[#0F1A14] animate-pulse"></span>
                    @endif
                </a>
                <a href="{{ route('settings.edit') }}" class="p-2 text-gray-300 hover:text-white rounded-lg" title="Settings">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </a>
                @else
                <a href="{{ route('login') }}" class="px-2.5 py-1 text-xs font-bold bg-[#F5C518] text-gray-900 rounded-md">Sign In</a>
                @endauth
            </div>
        </header>

        {{-- ── Mobile Drawer Backdrop ─────────────────────────────────────────── --}}
        <div id="mobile-drawer-backdrop"
             class="fixed inset-0 bg-black/60 z-40 md:hidden opacity-0 pointer-events-none transition-opacity duration-300 no-print"
             onclick="closeMobileSidebar()"></div>

        {{-- ── Mobile Slide-Over Drawer ───────────────────────────────────────── --}}
        <div id="mobile-drawer"
             class="fixed inset-y-0 left-0 w-72 max-w-[85vw] bg-[#0F1A14] text-white z-50 md:hidden flex flex-col -translate-x-full transition-transform duration-300 ease-in-out shadow-2xl border-r border-[#1A2E22] no-print">
            {{-- Mobile Drawer Brand Header --}}
            <div class="h-16 px-4 border-b border-[#1A2E22] flex items-center justify-between shrink-0">
                <a href="{{ route('activities.daily') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/opsora-icon.svg') }}" alt="Opsora SRE" class="w-7 h-7">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-black text-sm tracking-wide text-white block leading-none">OPSORA</span>
                            <span class="px-1 py-0.2 rounded bg-[#F5C518]/20 border border-[#F5C518]/50 text-[#F5C518] text-[9px] font-bold font-mono">SRE</span>
                        </div>
                        <span class="text-[8px] text-gray-400 font-mono tracking-widest uppercase block mt-0.5">Reliability Operations</span>
                    </div>
                </a>
                <button type="button" onclick="closeMobileSidebar()" class="p-1.5 text-gray-400 hover:text-white rounded-lg cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Mobile Drawer Nav Links --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                @include('layouts.sidebar-nav')
            </div>

            {{-- Mobile Drawer User / Actions --}}
            <div class="p-4 border-t border-[#1A2E22] bg-[#0A120E] shrink-0">
                @auth
                <div onclick="openProfileModal()"
                     class="flex items-center justify-between mb-3 p-1.5 -m-1.5 rounded-xl hover:bg-white/10 transition-colors cursor-pointer group"
                     title="View SRE Operator Profile">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-[#1B6B3A] border border-[#F5C518]/40 flex items-center justify-center font-bold text-xs text-white shadow-inner shrink-0 group-hover:border-[#F5C518] transition-colors">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-white truncate leading-tight group-hover:text-emerald-300 transition-colors">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-green-300 capitalize flex items-center gap-1">
                                <span>{{ auth()->user()->role }}</span>
                                <span class="text-white/40">•</span>
                                <span class="text-[#F5C518] font-semibold">{{ auth()->user()->grade ?? 'L1' }}</span>
                            </p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('settings.edit') }}" class="flex-1 text-center py-1.5 px-2 bg-white/5 hover:bg-white/10 text-xs text-gray-300 rounded-lg transition-colors border border-white/5">
                        Settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full text-center py-1.5 px-2 bg-red-600/20 hover:bg-red-600 text-xs text-red-200 hover:text-white font-medium rounded-lg transition-colors border border-red-500/20 cursor-pointer">
                            Sign Out
                        </button>
                    </form>
                </div>
                @else
                <a href="{{ route('login') }}" class="block w-full py-2 text-center bg-[#F5C518] hover:bg-amber-400 text-gray-900 font-bold text-xs rounded-lg transition-colors">
                    Sign In
                </a>
                @endauth
            </div>
        </div>

        {{-- ── Desktop Left Sidebar (Sticky, w-64 xl:w-72) ─────────────────────── --}}
        <aside class="hidden md:flex md:w-64 lg:w-72 shrink-0 bg-[#0F1A14] text-white flex-col sticky top-0 h-screen border-r border-[#1A2E22] z-30 shadow-xl no-print select-none">
            {{-- Brand Section --}}
            <div class="h-16 px-5 border-b border-[#1A2E22] flex items-center justify-between shrink-0">
                <a href="{{ route('activities.daily') }}" class="flex items-center gap-3 group">
                    <div class="relative flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-950/60 to-black/60 border border-white/10 group-hover:border-[#F5C518]/60 transition-all shadow-sm p-1">
                        <img src="{{ asset('images/opsora-icon.svg') }}" alt="Opsora SRE" class="w-full h-full object-contain group-hover:scale-105 transition-transform">
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-black text-sm tracking-wide text-white block leading-none group-hover:text-emerald-300 transition-colors">
                                OPSORA
                            </span>
                            <span class="px-1 py-0.5 rounded bg-[#F5C518]/20 border border-[#F5C518]/50 text-[#F5C518] text-[10px] font-black font-mono leading-none">
                                SRE
                            </span>
                        </div>
                        <span class="block text-emerald-400/80 text-[9px] font-mono tracking-wider uppercase mt-1 font-semibold">
                            Operations Platform
                        </span>
                    </div>
                </a>
            </div>

            {{-- SRE Cockpit Status Banner & Active Tenant Workspace Indicator --}}
            <div class="px-4 py-2.5 bg-[#122218] border-b border-[#1A2E22] flex items-center justify-between text-[11px]">
                @auth
                    @php
                        $sidebarActiveWs = \App\Services\TenantContext::getWorkspace() ?? auth()->user()->currentWorkspace();
                    @endphp
                    @if($sidebarActiveWs)
                        <a href="{{ route('workspaces.index') }}" class="flex items-center gap-2 min-w-0 group" title="Click to Switch Workspace">
                            <span class="relative flex h-2 w-2 shrink-0">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                            </span>
                            <span class="text-green-300 font-bold truncate max-w-[140px] group-hover:text-[#F5C518] transition-colors">
                                {{ $sidebarActiveWs->name }}
                            </span>
                        </a>
                        <a href="{{ route('workspaces.index') }}" class="text-[10px] font-mono text-[#F5C518] hover:underline shrink-0">
                            Switch
                        </a>
                    @else
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span class="text-green-300 font-medium">SRE Cockpit</span>
                        </div>
                        <span class="text-[10px] font-mono text-gray-400">v1.2</span>
                    @endif
                @else
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span class="text-green-300 font-medium">SRE Cockpit</span>
                    </div>
                    <span class="text-[10px] font-mono text-gray-400">v1.2</span>
                @endauth
            </div>

            {{-- Scrollable Navigation Links --}}
            <div class="flex-1 overflow-y-auto py-4 px-3 space-y-3 scrollbar-thin">
                @include('layouts.sidebar-nav')
            </div>

            {{-- User Profile & Bottom Action Footer --}}
            <div class="p-3 border-t border-[#1A2E22] bg-[#0A120E] shrink-0">
                @auth
                <div onclick="openProfileModal()"
                     class="flex items-center gap-3 p-2 rounded-xl bg-white/5 border border-white/5 hover:bg-white/10 hover:border-[#F5C518]/30 mb-2 cursor-pointer transition-all group"
                     title="Click to view detailed SRE profile and permissions">
                    <div class="w-9 h-9 rounded-lg bg-[#1B6B3A] border border-[#F5C518]/40 flex items-center justify-center font-bold text-xs text-white shadow-inner shrink-0 group-hover:scale-105 transition-transform">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-white truncate leading-tight group-hover:text-emerald-300 transition-colors">{{ auth()->user()->name }}</p>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-[#1B6B3A]/60 text-green-200 capitalize">
                                {{ auth()->user()->role }}
                            </span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#F5C518]/20 text-[#F5C518] border border-[#F5C518]/30">
                                {{ auth()->user()->grade ?? 'L1' }}
                            </span>
                            @if(auth()->user()->department)
                            <span class="text-[10px] text-gray-400 truncate max-w-[80px]">{{ auth()->user()->department }}</span>
                            @endif
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-[#F5C518] transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('settings.edit') }}"
                       class="flex-1 inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-gray-300 hover:text-white bg-white/5 hover:bg-white/10 rounded-lg transition-colors border border-white/5">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Settings</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center justify-center p-1.5 text-gray-300 hover:text-[#E63946] bg-white/5 hover:bg-red-500/10 rounded-lg transition-colors border border-white/5 cursor-pointer"
                                title="Sign Out">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
                @else
                <a href="{{ route('login') }}"
                   class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-bold bg-[#F5C518] hover:bg-amber-400 text-gray-900 rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Sign In</span>
                </a>
                @endauth
            </div>
        </aside>

        {{-- ── Right-Side Main Content Area ───────────────────────────────────── --}}
        <div class="flex-1 min-w-0 flex flex-col min-h-screen bg-[#F4F7F5] dark:bg-[#07100B]">

            {{-- Platform Support Impersonation Session Sticky Banner --}}
            @if(session()->has('opsora_impersonator_id'))
                <div class="bg-[#F5C518] text-gray-950 px-4 py-2.5 flex items-center justify-between text-xs font-semibold shadow-md z-40 border-b border-amber-600/30 sticky top-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600 animate-ping"></span>
                        <span>
                            <strong>Support Impersonation Mode:</strong> Currently acting as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }}). All actions are recorded to the immutable audit ledger.
                        </span>
                    </div>
                    <form method="POST" action="{{ route('admin.platform.impersonate.exit') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="px-3 py-1 bg-gray-950 hover:bg-black text-white rounded-lg text-xs font-bold transition shadow-xs cursor-pointer">
                            Exit Support Session
                        </button>
                    </form>
                </div>
            @endif

            {{-- Platform Operational Announcement / Emergency Broadcast Banner --}}
            @php
                $globalActiveAnnouncement = \Illuminate\Support\Facades\Schema::hasTable('platform_announcements')
                    ? \App\Models\PlatformAnnouncement::active()->latest('id')->first()
                    : null;
            @endphp
            @if($globalActiveAnnouncement)
                <div x-data="{ dismissed: false }"
                     x-show="!dismissed"
                     class="px-4 py-2 text-xs font-medium border-b flex items-center justify-between z-30 transition-all
                            @if($globalActiveAnnouncement->type === 'critical')
                                bg-[#E63946] text-white border-red-800 shadow-md
                            @elseif($globalActiveAnnouncement->type === 'warning')
                                bg-amber-500 text-gray-950 border-amber-600
                            @else
                                bg-[#1B6B3A] text-white border-emerald-800
                            @endif">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-2 h-2 rounded-full bg-white animate-pulse shrink-0"></span>
                        <div class="truncate">
                            <strong class="font-bold tracking-wide uppercase font-mono text-[11px] mr-1.5">
                                [{{ strtoupper($globalActiveAnnouncement->type) }}]:
                            </strong>
                            <span>{{ $globalActiveAnnouncement->title }} &mdash; {{ $globalActiveAnnouncement->message }}</span>
                        </div>
                    </div>
                    @if($globalActiveAnnouncement->dismissible)
                        <button type="button" @click="dismissed = true" class="ml-3 text-current hover:opacity-80 font-bold text-sm leading-none cursor-pointer" aria-label="Dismiss Announcement">&times;</button>
                    @endif
                </div>
            @endif

            {{-- Top Context & Breadcrumb Bar --}}
            <header class="bg-white dark:bg-[#0D1812] border-b border-gray-200 dark:border-[#1A2E22] h-14 px-4 sm:px-6 lg:px-8 flex items-center justify-between shrink-0 shadow-2xs no-print">
                <div class="flex items-center gap-3">
                    @if(!request()->routeIs('activities.daily'))
                    <button onclick="window.history.back()"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-700 dark:text-gray-200 font-semibold text-xs rounded-lg transition-colors cursor-pointer"
                            title="Return to previous screen">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Back</span>
                    </button>
                    @endif

                    {{-- Breadcrumb trail --}}
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-medium text-gray-600 dark:text-gray-400 hidden sm:inline">Opsora SRE</span>
                        <span class="hidden sm:inline">/</span>
                        <span class="text-gray-900 dark:text-white font-bold capitalize">
                            @if(request()->routeIs('activities.daily'))
                                Today's Board
                            @elseif(request()->routeIs('activities.*'))
                                Activities Management
                            @elseif(request()->routeIs('reports.handovers'))
                                Shift Handover Compliance
                            @elseif(request()->routeIs('reports.timelines'))
                                Operational Timeline History
                            @elseif(request()->routeIs('reports.*'))
                                SRE Reporting & Analytics
                            @elseif(request()->routeIs('messages.*'))
                                Shift Communications & Ops Comms
                            @elseif(request()->routeIs('workspaces.*'))
                                Workspaces & Multi-Tenant Hub
                            @elseif(request()->routeIs('organizations.*'))
                                Organizations & Enterprise Tenants
                            @elseif(request()->routeIs('admin.platform.*') || request()->routeIs('platform.*'))
                                Platform Control Plane
                            @elseif(request()->routeIs('admin.*'))
                                Admin Management
                            @elseif(request()->routeIs('monitoring.*'))
                                SRE Monitoring Dashboard
                            @elseif(request()->routeIs('health*'))
                                System Health Diagnostics
                            @elseif(request()->routeIs('settings.*'))
                                Account Settings
                            @else
                                SRE Console
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Right side telemetry and live clock --}}
                <div class="flex items-center gap-3">
                    {{-- Live UTC Clock --}}
                    <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-[11px] font-mono text-gray-600 dark:text-gray-300">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="header-utc-clock">UTC --:--:--</span>
                    </div>

                    {{-- Live Telemetry Status Pill --}}
                    <a href="{{ route('health') }}"
                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-[#1B6B3A] dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 hover:bg-emerald-100 dark:hover:bg-emerald-950 transition-colors"
                       title="View System Health & Telemetry">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="hidden md:inline">Systems Online</span>
                        <span class="md:hidden">OK</span>
                    </a>
                </div>
            </header>

            {{-- Flash Messages --}}
            @if(session('success') || session('error'))
            <div class="flex-shrink-0 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                @if(session('success'))
                <x-alert type="success" :message="session('success')" />
                @endif
                @if(session('error'))
                <x-alert type="error" :message="session('error')" />
                @endif
            </div>
            @endif

            {{-- Main Content Container --}}
            <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                @yield('content')
                @if(isset($slot) && !is_array($slot))
                    {{ $slot }}
                @endif
            </main>

            {{-- Layout Footer --}}
            <footer class="bg-white border-t border-gray-200 text-gray-500 text-xs py-3 mt-auto shrink-0 no-print">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
                    <span>&copy; {{ date('Y') }} Opsora SRE Operations. Internal operational use only.</span>
                    <div class="flex items-center gap-4">
                        <span class="text-gray-400">SRE Handover SLA: 99.98%</span>
                        <a href="{{ route('health') }}" class="text-[#1B6B3A] font-semibold hover:underline">
                            System Health Diagnostics &rarr;
                        </a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @livewireScripts

    {{-- Livewire 3 Graceful 419 Session Expiration Interceptor --}}
    <script>
        document.addEventListener('livewire:init', () => {
            if (window.Livewire) {
                window.Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status === 419) {
                            preventDefault();
                            window.location.href = '{{ route("login") }}?expired=1';
                        }
                    });
                });
            }
        });
    </script>

    {{-- Mobile Sidebar Drawer Scripts & Live UTC Clock --}}
    <script>
        function openMobileSidebar() {
            const backdrop = document.getElementById('mobile-drawer-backdrop');
            const drawer = document.getElementById('mobile-drawer');
            if (backdrop && drawer) {
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100', 'pointer-events-auto');
                drawer.classList.remove('-translate-x-full');
                drawer.classList.add('translate-x-0');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeMobileSidebar() {
            const backdrop = document.getElementById('mobile-drawer-backdrop');
            const drawer = document.getElementById('mobile-drawer');
            if (backdrop && drawer) {
                backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                drawer.classList.remove('translate-x-0');
                drawer.classList.add('-translate-x-full');
                document.body.classList.remove('overflow-hidden');
            }
        }

        function openProfileModal() {
            const backdrop = document.getElementById('operator-profile-modal-backdrop');
            const content = document.getElementById('operator-profile-modal-content');
            if (backdrop && content) {
                backdrop.classList.remove('opacity-0', 'pointer-events-none');
                backdrop.classList.add('opacity-100', 'pointer-events-auto');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }
        }

        function closeProfileModal() {
            const backdrop = document.getElementById('operator-profile-modal-backdrop');
            const content = document.getElementById('operator-profile-modal-content');
            if (backdrop && content) {
                backdrop.classList.add('opacity-0', 'pointer-events-none');
                backdrop.classList.remove('opacity-100', 'pointer-events-auto');
                content.classList.add('scale-95');
                content.classList.remove('scale-100');
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileSidebar();
                closeProfileModal();
            }
        });

        // Live UTC Clock updater
        (function() {
            function updateUtcClock() {
                const clockEl = document.getElementById('header-utc-clock');
                if (clockEl) {
                    const now = new Date();
                    const h = String(now.getUTCHours()).padStart(2, '0');
                    const m = String(now.getUTCMinutes()).padStart(2, '0');
                    const s = String(now.getUTCSeconds()).padStart(2, '0');
                    clockEl.textContent = `UTC ${h}:${m}:${s}`;
                }
            }
            setInterval(updateUtcClock, 1000);
            updateUtcClock();
        })();
    </script>

    {{-- Browser tab title flasher for urgent unread SRE operational comms --}}
    @auth
    @php $unreadForTitle = auth()->user()->unreadMessagesCount(); @endphp
    @if($unreadForTitle > 0)
    <script>
        (function() {
            const originalDocTitle = document.title;
            const alertDocTitle = '🔔 ({{ $unreadForTitle }}) New Comms Alert! — Opsora SRE';
            let toggleAlert = false;
            setInterval(function() {
                document.title = toggleAlert ? alertDocTitle : originalDocTitle;
                toggleAlert = !toggleAlert;
            }, 1200);
        })();
    </script>
    @endif

    {{-- ── SRE Operator Profile Modal ────────────────────────────────────── --}}
    @php
        $authUser = auth()->user();
        $userGrade = $authUser->grade ?? 'L1';
        $userGradeLabel = \App\Models\User::GRADES[$userGrade] ?? $userGrade;
        $userPrivileges = $authUser->privileges ?? [];
    @endphp
    <div id="operator-profile-modal-backdrop"
         class="fixed inset-0 bg-black/60 z-50 opacity-0 pointer-events-none transition-opacity duration-200 backdrop-blur-xs flex items-center justify-center p-4 no-print"
         onclick="closeProfileModal()">
        <div id="operator-profile-modal-content"
             class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-100 transform scale-95 transition-all duration-200 text-gray-800"
             onclick="event.stopPropagation()">
            {{-- Modal Top Green Banner --}}
            <div class="bg-gradient-to-r from-[#113820] to-[#1B6B3A] p-6 text-white relative">
                <button type="button" onclick="closeProfileModal()"
                        class="absolute top-4 right-4 p-1.5 text-white/70 hover:text-white rounded-lg hover:bg-white/10 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-white/10 border-2 border-[#F5C518] flex items-center justify-center font-extrabold text-2xl text-white shadow-lg shrink-0">
                        {{ strtoupper(substr($authUser->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-[#F5C518] text-gray-900 shadow-xs">
                                {{ strtoupper($authUser->role) }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-white/15 text-emerald-200 border border-white/20">
                                {{ $userGrade }}
                            </span>
                        </div>
                        <h2 class="text-xl font-bold text-white mt-1 truncate">{{ $authUser->name }}</h2>
                        <p class="text-xs text-emerald-100 truncate">{{ $authUser->email }}</p>
                    </div>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                {{-- SRE Seniority Level --}}
                <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-100 flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-[#1B6B3A] text-white shrink-0">
                        <svg class="w-5 h-5 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-mono uppercase tracking-wider text-gray-500 font-bold">Engineering Seniority Grade</div>
                        <div class="text-sm font-extrabold text-[#1B6B3A]">{{ $userGradeLabel }}</div>
                        <div class="text-xs text-gray-600 mt-0.5">
                            @if($userGrade === 'L5')
                                Principal Architect & Enterprise Lead. Directs cross-team incident resolution and system-wide reliability architecture.
                            @elseif($userGrade === 'L4')
                                Team Lead & Shift Supervisor. Oversees shift handovers, task assignment, and operational coordination.
                            @elseif($userGrade === 'L3')
                                Senior SRE Specialist. Deep infrastructure triage, P1/P2 investigations, and SLA root-cause analyses.
                            @elseif($userGrade === 'L2')
                                Core SRE Support Engineer. Executes scheduled checkoffs, war room collaboration, and status verifications.
                            @else
                                Associate Support Operator. Conducts standard baseline checks and operational shift monitoring.
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Operational Attributes --}}
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <span class="text-gray-400 font-medium block">Department / Pod</span>
                        <span class="font-bold text-gray-900 mt-0.5 block">{{ $authUser->department ?? 'Core Operations (NOC)' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <span class="text-gray-400 font-medium block">Official Designation</span>
                        <span class="font-bold text-gray-900 mt-0.5 block">{{ $authUser->designation ?? 'Site Reliability Engineer' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <span class="text-gray-400 font-medium block">Operational Hotline</span>
                        <span class="font-bold text-gray-900 mt-0.5 block font-mono">{{ $authUser->phone ?? 'Unregistered' }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                        <span class="text-gray-400 font-medium block">Account Created</span>
                        <span class="font-bold text-gray-900 mt-0.5 block font-mono">{{ $authUser->created_at?->format('d M Y') }}</span>
                    </div>
                </div>

                {{-- Granular Privileges & Clearances --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-mono uppercase tracking-wider text-gray-400 font-bold">System Privileges & Clearances</span>
                        <span class="text-[10px] font-bold text-[#1B6B3A]">
                            {{ $authUser->isAdmin() ? 'ROOT CLEARANCE' : count($userPrivileges) . ' GRANTED' }}
                        </span>
                    </div>
                    @if($authUser->isAdmin())
                    <div class="p-2.5 rounded-lg bg-red-50 border border-red-200 text-red-900 text-xs font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944z" clip-rule="evenodd"/></svg>
                        Full Administrator credentials enabled. All supervisory and audit privileges active.
                    </div>
                    @else
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(\App\Models\User::ALL_PRIVILEGES as $pKey => $pMeta)
                            @if($authUser->hasPrivilege($pKey))
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-50 text-[#1B6B3A] border border-emerald-200">
                                <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                {{ $pMeta['label'] }}
                            </span>
                            @endif
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="pt-2 flex items-center gap-2 border-t border-gray-100">
                    <a href="{{ route('settings.edit') }}"
                       class="flex-1 py-2 text-center text-xs font-bold text-white bg-[#1B6B3A] hover:bg-[#14532D] rounded-xl transition-colors shadow-sm">
                        Edit Account & Team Profile
                    </a>
                    <button type="button" onclick="closeProfileModal()"
                            class="px-4 py-2 text-center text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors cursor-pointer">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endauth
</body>
</html>
