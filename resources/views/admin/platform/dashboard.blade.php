@extends('layouts.app')

@section('title', 'Platform Control Plane — Opsora SRE')

@section('content')
<div class="space-y-6">

    {{-- Control Plane Master Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <span>Platform Control Plane</span>
                <span>/</span>
                <span class="text-[#F5C518]">Global Operations Center</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>Administrative Cockpit</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-[#1B6B3A]/20 text-[#1B6B3A] dark:text-emerald-300 border border-[#1B6B3A]/30">
                    {{ auth()->user()->platformRoleEnum()?->label() ?? 'Root Admin' }}
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Centralized oversight for tenant organizations, commercial subscriptions, SIEM security telemetry, and multi-tenant infrastructure.
            </p>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
            <a href="{{ route('admin.platform.health.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-[#16241B] border border-gray-300 dark:border-white/10 text-gray-800 dark:text-gray-200 text-xs font-bold shadow-xs hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Diagnostics</span>
            </a>
            <a href="{{ route('admin.platform.audit.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white text-xs font-bold shadow-sm transition-colors">
                <svg class="w-3.5 h-3.5 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Audit Trail</span>
            </a>
        </div>
    </div>

    {{-- Platform Live Health Status Bar --}}
    <div class="p-4 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-700/60 flex items-center justify-center text-emerald-700 dark:text-emerald-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">All Platform Core Systems Operational</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                        Database ({{ $health['database']['latency_ms'] ?? 0 }}ms) &bull; Cache ({{ $health['cache']['latency_ms'] ?? 0 }}ms) &bull; {{ $health['queue']['message'] ?? 'Queue Ready' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                    99.98% Uptime Commitment
                </span>
            </div>
        </div>
    </div>

    {{-- Primary KPI Metrics Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Tenants / Orgs --}}
        <a href="{{ route('admin.platform.organizations.index') }}"
           class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm hover:border-[#1B6B3A] transition-all group">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-mono font-bold uppercase tracking-wider">Organizations</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-white/5 flex items-center justify-center text-[#1B6B3A] dark:text-emerald-400 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <div class="text-2xl font-black text-gray-900 dark:text-white font-mono">
                {{ $metrics['organizations']['total'] }}
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mt-2 font-mono">
                <span>{{ $metrics['organizations']['active'] }} Active</span>
                @if($metrics['organizations']['pending_applications'] > 0)
                    <span class="text-[#F5C518] font-bold">{{ $metrics['organizations']['pending_applications'] }} Pending Queue</span>
                @else
                    <span class="text-gray-400">0 Pending</span>
                @endif
            </div>
        </a>

        {{-- Users & Operators --}}
        <a href="{{ route('admin.platform.users.index') }}"
           class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm hover:border-[#1B6B3A] transition-all group">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-mono font-bold uppercase tracking-wider">Total Operators</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-white/5 flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-black text-gray-900 dark:text-white font-mono">
                {{ $metrics['users']['total'] }}
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mt-2 font-mono">
                <span>{{ $metrics['users']['active_30d'] }} Active (30d)</span>
                @if($metrics['users']['suspended'] > 0)
                    <span class="text-red-500 font-bold">{{ $metrics['users']['suspended'] }} Suspended</span>
                @else
                    <span class="text-emerald-500 font-bold">All Healthy</span>
                @endif
            </div>
        </a>

        {{-- Subscriptions & MRR --}}
        <a href="{{ route('admin.platform.subscriptions.index') }}"
           class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm hover:border-[#1B6B3A] transition-all group">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-mono font-bold uppercase tracking-wider">Estimated ARR</span>
                <div class="w-8 h-8 rounded-lg bg-yellow-50 dark:bg-white/5 flex items-center justify-center text-[#F5C518] group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-black text-gray-900 dark:text-white font-mono">
                {{ $metrics['subscriptions']['arr_formatted'] }}
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mt-2 font-mono">
                <span>MRR: {{ $metrics['subscriptions']['mrr_formatted'] }}</span>
                <span>{{ $metrics['subscriptions']['active'] }} Subs</span>
            </div>
        </a>

        {{-- Active Incidents & P1s --}}
        <a href="{{ route('monitoring.index') }}"
           class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm hover:border-red-500 transition-all group">
            <div class="flex items-center justify-between text-gray-500 dark:text-gray-400 mb-2">
                <span class="text-xs font-mono font-bold uppercase tracking-wider">Active Incidents</span>
                <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-white/5 flex items-center justify-center text-[#E63946] group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-black {{ $metrics['operations']['active_incidents'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }} font-mono">
                {{ $metrics['operations']['active_incidents'] }}
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mt-2 font-mono">
                <span>{{ $metrics['operations']['activities_today'] }} Checks Today</span>
                <span>{{ $metrics['operations']['total_handovers'] }} Handovers</span>
            </div>
        </a>

    </div>

    {{-- Control Center Split: SIEM Security Stream & Global Audit Trail --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Left: SIEM Security Events Feed --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white font-mono uppercase tracking-wider">
                        Security Events (SIEM)
                    </h3>
                </div>
                <a href="{{ route('admin.platform.security.index') }}" class="text-xs font-bold text-[#1B6B3A] dark:text-emerald-400 hover:underline font-mono">
                    View All &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @forelse($metrics['recent']['security_events'] as $event)
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-white/5 flex items-center justify-between gap-3 text-xs">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase
                                             {{ $event->severity === 'critical' ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300' : ($event->severity === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-[#F5C518]' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300') }}">
                                    {{ $event->severity }}
                                </span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200 font-mono">{{ $event->event_type }}</span>
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 truncate">
                                Actor: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $event->actor_name ?? 'Anonymous/System' }}</span>
                                &bull; IP: <span class="font-mono">{{ $event->ip_address ?? 'unknown' }}</span>
                            </p>
                        </div>
                        <span class="text-[10px] font-mono text-gray-400 shrink-0">
                            {{ $event->created_at?->diffForHumans(null, true) }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-gray-500 dark:text-gray-400 py-4 text-center font-mono">
                        No recent security events recorded.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- Right: Platform Audit Trail --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white font-mono uppercase tracking-wider">
                        Immutable Audit Trail
                    </h3>
                </div>
                <a href="{{ route('admin.platform.audit.index') }}" class="text-xs font-bold text-[#1B6B3A] dark:text-emerald-400 hover:underline font-mono">
                    View Full Log &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @forelse($metrics['recent']['audit_logs'] as $log)
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-white/5 flex items-center justify-between gap-3 text-xs">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-800 dark:text-gray-200 font-mono">{{ $log->event }}</span>
                                <span class="text-[10px] text-gray-400 font-mono">({{ class_basename($log->subject_type ?? 'Entity') }})</span>
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 truncate">
                                Operator: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $log->actor_name ?? 'System' }}</span>
                                &bull; IP: <span class="font-mono">{{ $log->ip_address }}</span>
                            </p>
                        </div>
                        <span class="text-[10px] font-mono text-gray-400 shrink-0">
                            {{ $log->created_at?->diffForHumans(null, true) }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-gray-500 dark:text-gray-400 py-4 text-center font-mono">
                        No audit log entries found.
                    </p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Control Center Administrative Navigation Shortcuts --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <a href="{{ route('admin.platform.organizations.index') }}"
           class="p-4 rounded-xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 text-center hover:border-emerald-600 transition-colors">
            <span class="block text-sm font-bold text-gray-900 dark:text-white">Tenants</span>
            <span class="block text-[11px] text-gray-500 dark:text-gray-400 font-mono mt-0.5">Orgs &amp; Isolation</span>
        </a>

        <a href="{{ route('admin.platform.users.index') }}"
           class="p-4 rounded-xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 text-center hover:border-emerald-600 transition-colors">
            <span class="block text-sm font-bold text-gray-900 dark:text-white">Operators</span>
            <span class="block text-[11px] text-gray-500 dark:text-gray-400 font-mono mt-0.5">Roles &amp; Access</span>
        </a>

        <a href="{{ route('admin.platform.plans.index') }}"
           class="p-4 rounded-xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 text-center hover:border-emerald-600 transition-colors">
            <span class="block text-sm font-bold text-gray-900 dark:text-white">Plans</span>
            <span class="block text-[11px] text-gray-500 dark:text-gray-400 font-mono mt-0.5">Entitlements</span>
        </a>

        <a href="{{ route('admin.platform.features.index') }}"
           class="p-4 rounded-xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 text-center hover:border-emerald-600 transition-colors">
            <span class="block text-sm font-bold text-gray-900 dark:text-white">Feature Flags</span>
            <span class="block text-[11px] text-gray-500 dark:text-gray-400 font-mono mt-0.5">Gates &amp; Toggles</span>
        </a>

        <a href="{{ route('admin.platform.reports.index') }}"
           class="p-4 rounded-xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 text-center hover:border-emerald-600 transition-colors">
            <span class="block text-sm font-bold text-gray-900 dark:text-white">Analytics</span>
            <span class="block text-[11px] text-gray-500 dark:text-gray-400 font-mono mt-0.5">SaaS Growth</span>
        </a>

        <a href="{{ route('admin.platform.settings.index') }}"
           class="p-4 rounded-xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 text-center hover:border-emerald-600 transition-colors">
            <span class="block text-sm font-bold text-gray-900 dark:text-white">Enterprise</span>
            <span class="block text-[11px] text-gray-500 dark:text-gray-400 font-mono mt-0.5">Policies &amp; Config</span>
        </a>
    </div>

</div>
@endsection
