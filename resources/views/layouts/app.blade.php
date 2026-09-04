<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Npontu Technologies — Support Activity Tracker for operational shift management">
    <title>@yield('title', config('app.name', 'Support Tracker')) — Npontu Technologies</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full bg-[#F4F7F5] font-sans antialiased text-gray-900">
    {{-- Splash Screen Loader for App (Snappy 200ms transition) --}}
    <div id="app-splash-screen" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-[#0F1A14] transition-opacity duration-300 pointer-events-auto">
        <div class="flex flex-col items-center gap-4 text-center">
            <div class="relative flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 border border-white/20 shadow-2xl">
                <svg class="w-8 h-8 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                    <polygon points="16,3 30,27 2,27"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-white tracking-tight">Support Tracker</h1>
                <p class="text-[11px] text-[#F5C518] font-mono tracking-widest uppercase mt-0.5 font-semibold">Npontu Technologies</p>
            </div>
            <div class="flex items-center gap-1.5 mt-1">
                <div class="w-2 h-2 rounded-full bg-[#1B6B3A] animate-ping"></div>
                <span class="text-xs text-gray-400 font-medium">Loading SRE Console...</span>
            </div>
        </div>
    </div>
    <script>
        (function() {
            function dismissSplash() {
                const splash = document.getElementById('app-splash-screen');
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
            setTimeout(dismissSplash, 500);
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
                <a href="{{ route('activities.daily') }}" class="flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor"><polygon points="16,3 30,27 2,27"/></svg>
                    <span class="font-bold text-sm tracking-tight text-white">Support Tracker</span>
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
                    <svg class="w-7 h-7 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor"><polygon points="16,3 30,27 2,27"/></svg>
                    <div>
                        <span class="font-bold text-sm tracking-tight text-white block leading-none">Support Tracker</span>
                        <span class="text-[9px] text-[#F5C518] font-mono tracking-widest uppercase">NPONTU TECHNOLOGIES</span>
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
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-[#1B6B3A] border border-[#F5C518]/40 flex items-center justify-center font-bold text-xs text-white shadow-inner shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-white truncate leading-tight">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-green-300 capitalize">{{ auth()->user()->role }}</p>
                        </div>
                    </div>
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
                    <div class="relative flex items-center justify-center w-9 h-9 rounded-xl bg-white/5 border border-white/10 group-hover:border-[#F5C518]/50 transition-colors shadow-sm">
                        <svg class="w-5 h-5 text-[#F5C518] group-hover:scale-105 transition-transform" viewBox="0 0 32 32" fill="currentColor">
                            <polygon points="16,3 30,27 2,27"/>
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold text-sm tracking-tight text-white block leading-none group-hover:text-green-300 transition-colors">
                            Support Tracker
                        </span>
                        <span class="block text-[#F5C518] text-[9px] font-mono tracking-widest uppercase mt-0.5 font-semibold">
                            NPONTU TECHNOLOGIES
                        </span>
                    </div>
                </a>
            </div>

            {{-- SRE Cockpit Status Banner --}}
            <div class="px-5 py-2.5 bg-[#122218] border-b border-[#1A2E22] flex items-center justify-between text-[11px]">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                    </span>
                    <span class="text-green-300 font-medium">SRE Cockpit</span>
                </div>
                <span class="text-[10px] font-mono text-gray-400">v1.2</span>
            </div>

            {{-- Scrollable Navigation Links --}}
            <div class="flex-1 overflow-y-auto py-4 px-3 space-y-3 scrollbar-thin">
                @include('layouts.sidebar-nav')
            </div>

            {{-- User Profile & Bottom Action Footer --}}
            <div class="p-3 border-t border-[#1A2E22] bg-[#0A120E] shrink-0">
                @auth
                <div class="flex items-center gap-3 p-2 rounded-xl bg-white/5 border border-white/5 mb-2">
                    <div class="w-9 h-9 rounded-lg bg-[#1B6B3A] border border-[#F5C518]/40 flex items-center justify-center font-bold text-xs text-white shadow-inner shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-white truncate leading-tight">{{ auth()->user()->name }}</p>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-[#1B6B3A]/60 text-green-200 capitalize">
                                {{ auth()->user()->role }}
                            </span>
                            @if(auth()->user()->department)
                            <span class="text-[10px] text-gray-400 truncate">{{ auth()->user()->department }}</span>
                            @endif
                        </div>
                    </div>
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
        <div class="flex-1 min-w-0 flex flex-col min-h-screen bg-[#F4F7F5]">

            {{-- Top Context & Breadcrumb Bar --}}
            <header class="bg-white border-b border-gray-200 h-14 px-4 sm:px-6 lg:px-8 flex items-center justify-between shrink-0 shadow-2xs no-print">
                <div class="flex items-center gap-3">
                    @if(!request()->routeIs('activities.daily'))
                    <button onclick="window.history.back()"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-lg transition-colors cursor-pointer"
                            title="Return to previous screen">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Back</span>
                    </button>
                    @endif

                    {{-- Breadcrumb trail --}}
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span class="font-medium text-gray-600 hidden sm:inline">Support Tracker</span>
                        <span class="hidden sm:inline">/</span>
                        <span class="text-gray-900 font-bold capitalize">
                            @if(request()->routeIs('activities.daily'))
                                Today's Board
                            @elseif(request()->routeIs('activities.*'))
                                Activities Management
                            @elseif(request()->routeIs('reports.handovers'))
                                Shift Handover Compliance
                            @elseif(request()->routeIs('reports.timelines'))
                                Operator Work Timelines
                            @elseif(request()->routeIs('reports.*'))
                                Operational Reports
                            @elseif(request()->routeIs('messages.*'))
                                Ops Comms & Channels
                            @elseif(request()->routeIs('admin.users.*'))
                                Team & User Privileges
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
                    <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-50 border border-gray-200 text-[11px] font-mono text-gray-600">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span id="header-utc-clock">UTC --:--:--</span>
                    </div>

                    {{-- Live Telemetry Status Pill --}}
                    <a href="{{ route('health') }}"
                       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#1B6B3A] border border-emerald-200 hover:bg-emerald-100 transition-colors"
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
                @if(isset($slot) && is_string($slot))
                    {{ $slot }}
                @endif
            </main>

            {{-- Layout Footer --}}
            <footer class="bg-white border-t border-gray-200 text-gray-500 text-xs py-3 mt-auto shrink-0 no-print">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
                    <span>&copy; {{ date('Y') }} Npontu Technologies. Internal operational use only.</span>
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

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileSidebar();
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
            const alertDocTitle = '🔔 ({{ $unreadForTitle }}) New Comms Alert! — Support Tracker';
            let toggleAlert = false;
            setInterval(function() {
                document.title = toggleAlert ? alertDocTitle : originalDocTitle;
                toggleAlert = !toggleAlert;
            }, 1200);
        })();
    </script>
    @endif
    @endauth
</body>
</html>
