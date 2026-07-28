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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full flex flex-col bg-[#F4F7F5] font-sans antialiased">

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
                    @endif
                </div>

                {{-- User Menu --}}
                <div class="flex items-center gap-3">
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-semibold leading-none">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-green-300 mt-0.5 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
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
    </nav>

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
