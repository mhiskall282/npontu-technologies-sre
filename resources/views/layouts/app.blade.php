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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full flex flex-col bg-[#F4F7F5] font-sans antialiased">
    {{-- Splash Screen Loader for App --}}
    <div id="app-splash-screen" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-[#0F1A14] transition-opacity duration-700">
        <div class="flex flex-col items-center gap-4 text-center">
            <div class="relative flex items-center justify-center w-16 h-16 rounded-2xl bg-white/10 border border-white/20 shadow-2xl">
                <svg class="w-10 h-10 text-[#F5C518] animate-bounce" viewBox="0 0 32 32" fill="currentColor">
                    <polygon points="16,3 30,27 2,27"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white tracking-tight">Support Tracker</h1>
                <p class="text-xs text-[#F5C518] font-mono tracking-widest uppercase mt-0.5">Npontu Technologies</p>
            </div>
            <div class="flex items-center gap-1.5 mt-2">
                <div class="w-2 h-2 rounded-full bg-[#1B6B3A] animate-ping"></div>
                <span class="text-xs text-gray-400 font-medium">Loading SRE Console...</span>
            </div>
        </div>
    </div>
    <script>
        window.addEventListener('load', () => {
            const splash = document.getElementById('app-splash-screen');
            if (splash) {
                setTimeout(() => {
                    splash.style.opacity = '0';
                    setTimeout(() => splash.remove(), 700);
                }, 7000);
            }
        });
    </script>

    {{-- ── Navigation ──────────────────────────────────────────── --}}
    <nav class="bg-[#1B6B3A] text-white shadow-lg flex-shrink-0 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo / Wordmark --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('activities.daily') }}" class="flex items-center gap-2">
                        {{-- Geometric triangle motif (Npontu brand accent) --}}
                        <svg class="w-8 h-8 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true">
                            <polygon points="16,3 30,27 2,27"/>
                        </svg>
                        <span class="font-bold text-lg tracking-tight leading-none">
                            Support Tracker
                            <span class="block text-[#F5C518] text-xs font-normal tracking-widest">NPONTU TECHNOLOGIES</span>
                        </span>
                    </a>
                </div>

                {{-- Primary Nav --}}
                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('activities.daily') }}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-150
                              {{ request()->routeIs('activities.daily') ? 'bg-[#12492A] text-white' : 'text-green-100 hover:text-white hover:bg-[#12492A]' }}">
                        Today's Board
                    </a>
                    <a href="{{ route('activities.index') }}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-150
                              {{ request()->routeIs('activities.*') && !request()->routeIs('activities.daily') ? 'bg-[#12492A] text-white' : 'text-green-100 hover:text-white hover:bg-[#12492A]' }}">
                        Activities
                    </a>
                    <a href="{{ route('reports.index') }}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-150
                              {{ request()->routeIs('reports.*') ? 'bg-[#12492A] text-white' : 'text-green-100 hover:text-white hover:bg-[#12492A]' }}">
                        Reports
                    </a>
                    @if(auth()->user()->canManageActivities())
                    <a href="{{ route('admin.activities.index') }}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-150
                              {{ request()->routeIs('admin.*') ? 'bg-[#12492A] text-white' : 'text-green-100 hover:text-white hover:bg-[#12492A]' }}">
                        Admin
                    </a>
                    <a href="{{ route('monitoring.index') }}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-150 flex items-center gap-1.5
                              {{ request()->routeIs('monitoring.*') ? 'bg-[#12492A] text-white' : 'text-green-100 hover:text-white hover:bg-[#12492A]' }}">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-300 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span>
                        </span>
                        Monitoring
                    </a>
                    @endif
                </div>

                {{-- Mobile Hamburger Toggle --}}
                <div class="flex items-center gap-2 md:hidden">
                    <a href="{{ route('settings.edit') }}" class="p-1.5 text-green-200 hover:text-white rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </a>
                    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" type="button" class="p-2 text-green-100 hover:bg-[#12492A] rounded-lg focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>

                {{-- User Menu (Desktop) --}}
                <div class="hidden md:flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-sm font-semibold leading-none">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-green-300 mt-0.5 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                    <a href="{{ route('settings.edit') }}"
                       class="px-3 py-1.5 text-xs font-medium border border-green-400 text-green-100 rounded-md hover:bg-[#12492A] hover:border-transparent transition-colors duration-150 no-print">
                        Settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline no-print">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 text-xs font-medium border border-green-400 text-green-100
                                       rounded-md hover:bg-[#12492A] hover:border-transparent transition-colors duration-150">
                            Sign Out
                        </button>
                    </form>
                </div>

            </div>
        </div>

        {{-- Mobile Dropdown Drawer --}}
        <div id="mobile-menu" class="hidden md:hidden border-t border-green-700 bg-[#12492A] px-4 py-3 space-y-2">
            <div class="pb-2 mb-2 border-b border-green-600 flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-white">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-green-300 capitalize">{{ auth()->user()->role }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-lg">Sign Out</button>
                </form>
            </div>
            <a href="{{ route('activities.daily') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-white hover:bg-[#1B6B3A]">Today's Board</a>
            <a href="{{ route('activities.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-white hover:bg-[#1B6B3A]">Activities</a>
            <a href="{{ route('reports.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-white hover:bg-[#1B6B3A]">Reports</a>
            @if(auth()->user()->canManageActivities())
            <a href="{{ route('admin.activities.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-white hover:bg-[#1B6B3A]">Admin Management</a>
            <a href="{{ route('monitoring.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-white hover:bg-[#1B6B3A]">SRE Monitoring</a>
            @endif
            <a href="{{ route('settings.edit') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-green-200 hover:bg-[#1B6B3A]">Account Settings</a>
        </div>
    </nav>

    {{-- ── Back Button Bar (User-First Navigation) ────────────────────────── --}}
    @if(!request()->routeIs('activities.daily'))
    <div class="bg-white border-b border-gray-200 no-print py-2 px-4 sm:px-6 lg:px-8 shadow-xs">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <button onclick="window.history.back()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-lg transition-colors shadow-2xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back
            </button>
            <span class="text-xs text-gray-400 font-medium">Support Activity Tracker</span>
        </div>
    </div>
    @endif

    {{-- ── Flash messages ──────────────────────────────────────── --}}
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

    {{-- ── Main content ─────────────────────────────────────────── --}}
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    {{-- ── Footer ──────────────────────────────────────────────── --}}
    <footer class="bg-[#0F1A14] text-green-400 text-xs py-4 mt-auto flex-shrink-0 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <span>&copy; {{ date('Y') }} Npontu Technologies. Internal use only.</span>
            <a href="{{ route('health') }}" class="hover:text-[#F5C518] transition-colors duration-150">
                System Health
            </a>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
