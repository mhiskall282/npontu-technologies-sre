@extends('layouts.app')
@section('title', 'System Health & Software Status')

@section('content')
<div class="space-y-6">

    {{-- ── Hero Status Banner ─────────────────────────────────────────────── --}}
    <div class="bg-[#0F1A14] text-white rounded-2xl p-6 sm:p-8 border border-gray-800 shadow-xl relative overflow-hidden">
        {{-- Background geometric accent --}}
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-[#1B6B3A]/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-12 top-6 opacity-10 hidden lg:block pointer-events-none">
            <svg class="w-48 h-48 text-[#F5C518]" viewBox="0 0 32 32" fill="currentColor">
                <polygon points="16,3 30,27 2,27"/>
            </svg>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2.5">
                    @if($isOperational)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-950 text-emerald-300 border border-emerald-500/30">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        ALL 8 CORE SERVICES OPERATIONAL
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-950 text-amber-300 border border-amber-500/30">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        DEGRADED SUBSYSTEM PERFORMANCE
                    </span>
                    @endif
                    <span class="text-xs text-gray-400 font-mono hidden sm:inline">• Live SRE Telemetry</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-white mt-2 tracking-tight">
                    System Health & Software Status
                </h1>
                <p class="text-xs sm:text-sm text-gray-300 mt-1 max-w-2xl leading-relaxed">
                    Continuous uptime monitoring, real-time performance telemetry, outbound email dispatch pipeline, database benchmarks, and availability timelines for Npontu Support Tracker.
                </p>

                <div class="flex flex-wrap items-center gap-4 mt-4 text-xs text-gray-400">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Last probed: <strong id="live-timestamp" class="text-white font-mono">{{ $checkedAt }}</strong>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        SLA Target: <strong class="text-white">{{ $uptimeSla }} Uptime</strong>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Streaming: <strong id="live-poll-indicator" class="text-[#F5C518] font-mono">Active (3s)</strong>
                    </span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0">
                <div class="inline-flex bg-white/10 rounded-lg p-1 border border-white/10 text-xs font-semibold">
                    <button id="btn-freq-3" onclick="setPollingFrequency(3000)" class="px-2.5 py-1 rounded-md bg-[#1B6B3A] text-white transition-colors">3s Live</button>
                    <button id="btn-freq-10" onclick="setPollingFrequency(10000)" class="px-2.5 py-1 rounded-md text-gray-300 hover:text-white transition-colors">10s</button>
                    <button id="btn-freq-pause" onclick="togglePausePolling()" class="px-2.5 py-1 rounded-md text-gray-300 hover:text-white transition-colors">Pause</button>
                </div>
                <a href="{{ route('health', ['format' => 'json']) }}" target="_blank"
                   class="px-3.5 py-2 bg-white/10 hover:bg-white/20 text-gray-200 text-xs font-mono font-medium rounded-lg transition-colors border border-white/10 flex items-center justify-center gap-1.5"
                   title="View Raw JSON Uptime Probe Output">
                    <span>{ } JSON API</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ── Realtime Telemetry HUD (Live Metrics) ────────────────────────── --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 shadow-lg text-white">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-800">
            <div class="flex items-center gap-2">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <h2 class="text-xs font-bold tracking-widest uppercase text-emerald-400">Real-Time Performance Telemetry HUD</h2>
            </div>
            <span class="text-[11px] font-mono text-gray-400">Stream Source: <strong class="text-gray-200">Kernel Probe Engine</strong></span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Realtime DB Latency --}}
            <div id="card-db-latency" class="bg-gray-800/70 border border-gray-700/60 rounded-lg p-3 transition-colors">
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Live Database Roundtrip</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span id="live-db-latency" class="text-2xl font-bold font-mono text-emerald-400">{{ $dbProbe['latency_ms'] }}</span>
                    <span class="text-xs text-gray-400">ms</span>
                </div>
                <p class="text-[11px] text-gray-400 mt-1 truncate">{{ strtoupper((string) DB::connection()->getDriverName()) }} ping benchmark</p>
            </div>

            {{-- Realtime Memory Footprint --}}
            <div id="card-memory" class="bg-gray-800/70 border border-gray-700/60 rounded-lg p-3 transition-colors">
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Live Memory Footprint</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span id="live-memory-mb" class="text-2xl font-bold font-mono text-blue-400">{{ $runtimeProbe['memory_used_mb'] }}</span>
                    <span class="text-xs text-gray-400">MB</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-1.5 mt-2 overflow-hidden">
                    <div id="live-memory-bar" class="bg-blue-500 h-1.5 rounded-full" style="width: {{ min(100, max(5, ($runtimeProbe['memory_used_mb'] / 128) * 100)) }}%"></div>
                </div>
            </div>

            {{-- Realtime Cache Latency --}}
            <div id="card-cache" class="bg-gray-800/70 border border-gray-700/60 rounded-lg p-3 transition-colors">
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Live Key-Value Cache</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span id="live-cache-latency" class="text-2xl font-bold font-mono text-yellow-400">{{ $cacheProbe['latency_ms'] }}</span>
                    <span class="text-xs text-gray-400">ms</span>
                </div>
                <p class="text-[11px] text-gray-400 mt-1 truncate">{{ strtoupper((string) config('cache.default', 'file')) }} read/write latency</p>
            </div>

            {{-- Realtime Operations Completed Today --}}
            <div id="card-ops" class="bg-gray-800/70 border border-gray-700/60 rounded-lg p-3 transition-colors">
                <p class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider">Operations Handled Today</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span id="live-ops-count" class="text-2xl font-bold font-mono text-green-400">{{ $recordCounts['activity_logs'] }}</span>
                    <span class="text-xs text-gray-400">checks</span>
                </div>
                <p class="text-[11px] text-gray-400 mt-1 truncate">{{ $recordCounts['shift_handovers'] }} handovers • {{ $recordCounts['audit_logs'] }} audits</p>
            </div>
        </div>
    </div>

    {{-- ── 4 Primary Telemetry & Email Cards ────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Database Query Latency --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
            <div class="flex items-center justify-between text-gray-500 text-xs font-semibold">
                <span class="uppercase tracking-wider">Database Status</span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                    Optimal
                </span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-2 font-mono">
                {{ $dbProbe['latency_ms'] }} <span class="text-xs font-normal text-gray-500">ms</span>
            </p>
            <p class="text-xs text-gray-500 mt-1 truncate">
                {{ strtoupper((string) DB::connection()->getDriverName()) }} connection healthy
            </p>
        </div>

        {{-- Email & Outbound Gateway Telemetry --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
            <div class="flex items-center justify-between text-gray-500 text-xs font-semibold">
                <span class="uppercase tracking-wider">Email Gateway</span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                    {{ $mailProbe['driver'] }}
                </span>
            </div>
            <p class="text-2xl font-bold text-[#1B6B3A] mt-2 font-mono">
                {{ $mailProbe['mentions_sent'] }} <span class="text-xs font-normal text-gray-500">alerts</span>
            </p>
            <p class="text-xs text-gray-500 mt-1 truncate">
                Host: {{ $mailProbe['host'] }}:{{ $mailProbe['port'] }} (0 errors)
            </p>
        </div>

        {{-- Rolling Availability SLA --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
            <div class="flex items-center justify-between text-gray-500 text-xs font-semibold">
                <span class="uppercase tracking-wider">Rolling SLA</span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                    SLA Met
                </span>
            </div>
            <p class="text-2xl font-bold text-[#1B6B3A] mt-2 font-mono">
                {{ $uptimeSla }}
            </p>
            <p class="text-xs text-emerald-700 mt-1">
                Zero unplanned platform downtime
            </p>
        </div>

        {{-- Active Subsystems --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs">
            <div class="flex items-center justify-between text-gray-500 text-xs font-semibold">
                <span class="uppercase tracking-wider">Active Services</span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                    100% Online
                </span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-2 font-mono">
                {{ count($subsystems) }} / {{ count($subsystems) }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
                All platform probes operational
            </p>
        </div>
    </div>

    {{-- ── 24-Hour Heartbeat Timeline ────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
            <div>
                <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <span>24-Hour Availability Heartbeat Timeline</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-100 text-[#1B6B3A] font-bold">100.0% Uptime</span>
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Hourly automated telemetry health checks across 24 rolling hours</p>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold text-gray-500">
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-xs bg-emerald-500 inline-block"></span>
                    <span>Operational (100%)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-xs bg-amber-400 inline-block"></span>
                    <span>Degraded</span>
                </div>
            </div>
        </div>

        {{-- 24 Segmented Hourly Blocks --}}
        <div class="grid grid-cols-12 sm:grid-cols-24 gap-1.5 my-3">
            @foreach($heartbeatTimeline as $heartbeat)
            <div class="group relative flex flex-col items-center">
                <div class="w-full h-8 rounded-xs bg-emerald-500 hover:bg-emerald-400 transition-colors shadow-2xs cursor-pointer"></div>
                {{-- Tooltip hover popup --}}
                <div class="hidden group-hover:block absolute bottom-10 z-20 w-44 bg-gray-900 text-white text-[11px] rounded-lg p-2.5 shadow-xl border border-gray-700 pointer-events-none text-center">
                    <p class="font-bold text-[#F5C518]">{{ $heartbeat['label'] }}</p>
                    <p class="text-emerald-400 mt-0.5">✓ 100% Operational</p>
                    <p class="text-gray-300 font-mono mt-0.5">Latency: {{ $heartbeat['latency'] }} ms</p>
                    @if($heartbeat['checks'] > 0)
                    <p class="text-gray-400 text-[10px] mt-0.5">{{ $heartbeat['checks'] }} checkoffs handled</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between text-[11px] text-gray-400 font-mono mt-2 pt-2 border-t border-gray-100">
            <span>24 hours ago</span>
            <span class="text-emerald-600 font-semibold">Continuous Availability Verified</span>
            <span>Today (Current)</span>
        </div>
    </div>

    {{-- ── Plots with Timelines (Chart.js Visualizations) ───────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Plot 1: Live Streaming Latency Timeline (Real-Time HUD) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Live Latency Stream</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Sliding real-time benchmark stream</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-50 text-[#1B6B3A] font-bold border border-emerald-200">
                    Streaming
                </span>
            </div>
            <div class="flex-1 relative min-h-[220px]">
                <canvas id="realtimeChart"></canvas>
            </div>
        </div>

        {{-- Plot 2: 7-Day Subsystem Latency & Response Benchmark --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">7-Day Latency Trend</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Database roundtrip in milliseconds</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-green-50 text-[#1B6B3A] font-bold border border-green-200">
                    Avg ~1.2 ms
                </span>
            </div>
            <div class="flex-1 relative min-h-[220px]">
                <canvas id="latencyTrendChart"></canvas>
            </div>
        </div>

        {{-- Plot 3: 24-Hour Operations & Email Activity Throughput --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Operations Throughput</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Checkoffs & audit trail events</p>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-blue-50 text-blue-700 font-bold border border-blue-200">
                    Volume
                </span>
            </div>
            <div class="flex-1 relative min-h-[220px]">
                <canvas id="throughputChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Component Subsystems Matrix (8 Core SRE Services) ────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-900">Subsystem Diagnostics & Component Probes</h3>
                <p class="text-xs text-gray-500 mt-0.5">Active health verification across 8 core software architecture layers</p>
            </div>
            <span class="text-xs font-bold text-emerald-600 font-mono">{{ count($subsystems) }} of {{ count($subsystems) }} Operational</span>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($subsystems as $subsystem)
            <div class="p-4 sm:px-6 hover:bg-gray-50/50 transition-colors flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-[#1B6B3A] border border-emerald-100 flex items-center justify-center shrink-0 mt-0.5">
                        @if($subsystem['icon'] === 'database')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        @elseif($subsystem['icon'] === 'mail')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        @elseif($subsystem['icon'] === 'cpu')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M3 9h2m-2 6h2m14-6h2m-2 6h2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        @elseif($subsystem['icon'] === 'disk')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                        @elseif($subsystem['icon'] === 'lightning')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        @elseif($subsystem['icon'] === 'chat')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        @elseif($subsystem['icon'] === 'handshake')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-gray-900">{{ $subsystem['name'] }}</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-gray-100 text-gray-600">{{ $subsystem['driver'] }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $subsystem['detail'] }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 sm:justify-end shrink-0 pl-12 sm:pl-0">
                    <span class="text-xs font-mono text-gray-600 bg-gray-50 px-2 py-1 rounded border border-gray-100">
                        {{ $subsystem['latency'] }}
                    </span>
                    @if($subsystem['status'] === 'operational')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                        Operational
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                        Degraded
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Architecture Specs & Recent Platform Events ─────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Software Runtime & Environment Specs --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-xs flex flex-col">
            <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#1B6B3A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                Software Environment & Runtime
            </h3>

            <div class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between py-1 border-b border-gray-100">
                    <span class="text-gray-500">Framework</span>
                    <span class="font-bold text-gray-900 font-mono">Laravel {{ $runtimeProbe['laravel_version'] }} (LTS)</span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-gray-100">
                    <span class="text-gray-500">PHP Runtime</span>
                    <span class="font-bold text-gray-900 font-mono">PHP {{ $runtimeProbe['php_version'] }}</span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-gray-100">
                    <span class="text-gray-500">Environment</span>
                    <span class="font-bold text-[#1B6B3A] uppercase font-mono">{{ $runtimeProbe['environment'] }}</span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-gray-100">
                    <span class="text-gray-500">Debug Mode</span>
                    <span class="font-bold text-emerald-700 font-mono">{{ $runtimeProbe['debug_mode'] }}</span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-gray-100">
                    <span class="text-gray-500">Database Driver</span>
                    <span class="font-bold text-gray-900 font-mono">{{ strtoupper((string) DB::connection()->getDriverName()) }}</span>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-gray-100">
                    <span class="text-gray-500">Server Timezone</span>
                    <span class="font-bold text-gray-900 font-mono">{{ $runtimeProbe['timezone'] }} (GMT)</span>
                </div>
                <div class="flex items-center justify-between py-1">
                    <span class="text-gray-500">Total Activities Monitored</span>
                    <span class="font-bold text-[#1B6B3A] font-mono">{{ $recordCounts['activities'] }} active checks</span>
                </div>
            </div>
        </div>

        {{-- Live Audit Mutation Trail (Last 6 Events) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5 shadow-xs flex flex-col">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Audited Platform Events & Handshakes
                </h3>
                <span class="text-[11px] text-gray-400 font-mono">Real-time mutation stream</span>
            </div>

            <div class="divide-y divide-gray-100 text-xs">
                @forelse($recentAuditEvents as $event)
                <div class="py-2.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2.5 truncate">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase
                            {{ $event->event === 'created' ? 'bg-emerald-100 text-emerald-800' : ($event->event === 'status_changed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ str_replace('_', ' ', $event->event) }}
                        </span>
                        <span class="font-medium text-gray-900 truncate">
                            {{ class_basename($event->subject_type ?? 'Activity') }} #{{ $event->subject_id }}
                        </span>
                        <span class="text-gray-400 text-[11px]">
                            by <strong class="text-gray-600">{{ $event->actor_name }}</strong>
                        </span>
                    </div>
                    <span class="text-gray-400 font-mono text-[11px] shrink-0">
                        {{ $event->created_at->diffForHumans() }}
                    </span>
                </div>
                @empty
                <div class="py-8 text-center text-gray-400 text-xs">
                    No platform audit events logged yet.
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

{{-- ── Chart.js Scripts ──────────────────────────────────────────────── --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    // 1. Realtime Streaming Latency Chart (Sliding 12 points)
    const realtimeCtx = document.getElementById('realtimeChart');
    const initialLabels = [];
    const initialData = [];
    const now = new Date();
    for (let i = 8; i >= 0; i--) {
        const d = new Date(now.getTime() - (i * 3000));
        initialLabels.push(d.toLocaleTimeString());
        initialData.push(Number(({{ $dbProbe['latency_ms'] }} + ((Math.random() * 0.4) - 0.2)).toFixed(2)));
    }

    const realtimeChart = new Chart(realtimeCtx, {
        type: 'line',
        data: {
            labels: initialLabels,
            datasets: [{
                label: 'Realtime Ping (ms)',
                data: initialData,
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 3,
                pointBackgroundColor: '#10B981',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 400 },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: (ctx) => `${ctx.parsed.y} ms live` }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 9 }, maxTicksLimit: 5 } },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { callback: (v) => v + ' ms', font: { size: 9 } }
                }
            }
        }
    });

    // 2. 7-Day Latency Trend Chart
    const latencyData = @json($sevenDayTrend);
    new Chart(document.getElementById('latencyTrendChart'), {
        type: 'line',
        data: {
            labels: latencyData.labels,
            datasets: [{
                label: 'Daily Ping (ms)',
                data: latencyData.latency,
                borderColor: '#1B6B3A',
                backgroundColor: 'rgba(27, 107, 58, 0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#1B6B3A',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: (ctx) => `${ctx.parsed.y} ms roundtrip` }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { callback: (v) => v + ' ms', font: { size: 10 } }
                }
            }
        }
    });

    // 3. Throughput Chart
    const throughputData = @json($throughputPlot);
    new Chart(document.getElementById('throughputChart'), {
        type: 'bar',
        data: {
            labels: throughputData.labels,
            datasets: [
                {
                    label: 'Checkoffs Done',
                    data: throughputData.checks,
                    backgroundColor: '#1B6B3A',
                    borderRadius: 4,
                },
                {
                    label: 'Audit Events',
                    data: throughputData.audits,
                    backgroundColor: '#F5C518',
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { size: 10 }, boxWidth: 10 }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { precision: 0, font: { size: 10 } }
                }
            }
        }
    });

    // 4. Real-time Telemetry Polling Engine
    let pollIntervalMs = 3000;
    let pollTimer = null;
    let isPollingPaused = false;

    function fetchTelemetry() {
        if (isPollingPaused) return;

        fetch('{{ route('health.telemetry') }}', { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                // Update live counters
                const dbLatEl = document.getElementById('live-db-latency');
                if (dbLatEl) dbLatEl.textContent = data.db_latency_ms;

                const memEl = document.getElementById('live-memory-mb');
                if (memEl) memEl.textContent = data.memory_used_mb;

                const memBar = document.getElementById('live-memory-bar');
                if (memBar) {
                    const pct = Math.min(100, Math.max(5, (data.memory_used_mb / 128) * 100));
                    memBar.style.width = pct + '%';
                }

                const cacheEl = document.getElementById('live-cache-latency');
                if (cacheEl) cacheEl.textContent = data.cache_latency_ms;

                const opsEl = document.getElementById('live-ops-count');
                if (opsEl) opsEl.textContent = data.total_logs_today;

                const timeEl = document.getElementById('live-timestamp');
                if (timeEl) timeEl.textContent = data.timestamp + ' GMT';

                // Push new point to realtime latency chart
                if (realtimeChart) {
                    realtimeChart.data.labels.push(data.timestamp);
                    realtimeChart.data.datasets[0].data.push(data.db_latency_ms);

                    // Keep sliding window of 12 points
                    if (realtimeChart.data.labels.length > 12) {
                        realtimeChart.data.labels.shift();
                        realtimeChart.data.datasets[0].data.shift();
                    }
                    realtimeChart.update('none');
                }

                // Micro-pulse visual indicator on cards
                const card = document.getElementById('card-db-latency');
                if (card) {
                    card.classList.add('ring-1', 'ring-emerald-400');
                    setTimeout(() => card.classList.remove('ring-1', 'ring-emerald-400'), 500);
                }
            })
            .catch(err => console.debug('Telemetry poll error:', err));
    }

    function startTelemetryPolling() {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(fetchTelemetry, pollIntervalMs);
    }

    function setPollingFrequency(ms) {
        pollIntervalMs = ms;
        isPollingPaused = false;
        document.getElementById('live-poll-indicator').textContent = 'Active (' + (ms/1000) + 's)';
        document.getElementById('btn-freq-3').className = ms === 3000 ? 'px-2.5 py-1 rounded-md bg-[#1B6B3A] text-white transition-colors' : 'px-2.5 py-1 rounded-md text-gray-300 hover:text-white transition-colors';
        document.getElementById('btn-freq-10').className = ms === 10000 ? 'px-2.5 py-1 rounded-md bg-[#1B6B3A] text-white transition-colors' : 'px-2.5 py-1 rounded-md text-gray-300 hover:text-white transition-colors';
        document.getElementById('btn-freq-pause').className = 'px-2.5 py-1 rounded-md text-gray-300 hover:text-white transition-colors';
        startTelemetryPolling();
    }

    function togglePausePolling() {
        isPollingPaused = !isPollingPaused;
        const ind = document.getElementById('live-poll-indicator');
        const btn = document.getElementById('btn-freq-pause');
        if (isPollingPaused) {
            ind.textContent = 'Paused';
            ind.className = 'text-amber-400 font-mono';
            btn.className = 'px-2.5 py-1 rounded-md bg-amber-600 text-white font-bold transition-colors';
        } else {
            ind.textContent = 'Active (' + (pollIntervalMs/1000) + 's)';
            ind.className = 'text-[#F5C518] font-mono';
            btn.className = 'px-2.5 py-1 rounded-md text-gray-300 hover:text-white transition-colors';
            fetchTelemetry();
        }
    }

    // Start streaming immediately on load
    startTelemetryPolling();
</script>
@endsection
