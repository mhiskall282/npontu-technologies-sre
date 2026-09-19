@extends('errors.layout')

@section('title', 'Platform Maintenance Mode — Opsora SRE')

@section('content')
<div class="space-y-4 sm:space-y-6 my-auto py-2">
    <div class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-[#F5C518]/30 text-[#F5C518] text-[10px] sm:text-xs font-mono font-bold tracking-wider uppercase">
        <span class="w-2 h-2 rounded-full bg-[#F5C518] animate-ping"></span>
        <span>PLATFORM MAINTENANCE &bull; 503 SERVICE UNAVAILABLE</span>
    </div>

    <div>
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500/20 to-amber-600/10 border border-amber-500/30 flex items-center justify-center mx-auto mb-4 shadow-inner">
            <svg class="w-8 h-8 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">
            Scheduled Platform Maintenance
        </h1>
        <p class="text-xs sm:text-sm text-gray-300 mt-2 max-w-md mx-auto leading-relaxed whitespace-pre-line">
            {{ $settings['maintenance_message'] ?? 'Opsora SRE is currently undergoing scheduled platform upgrades and routine database optimization. Services will resume shortly.' }}
        </p>
    </div>

    {{-- Estimated Completion & Support Details --}}
    <div class="max-w-md mx-auto p-4 rounded-xl bg-black/40 border border-white/10 text-left font-mono text-xs space-y-2">
        <div class="flex items-center justify-between text-gray-400">
            <span>Estimated Restoration:</span>
            <span class="text-[#F5C518] font-bold">{{ $settings['maintenance_ends_at'] ?? 'Under Active Maintenance' }}</span>
        </div>
        <div class="flex items-center justify-between text-gray-400">
            <span>Primary Support:</span>
            <a href="mailto:{{ $settings['support_email'] ?? 'hello@johnokyere.xyz' }}" class="text-emerald-400 hover:underline">
                {{ $settings['support_email'] ?? 'hello@johnokyere.xyz' }}
            </a>
        </div>
        <div class="flex items-center justify-between text-gray-400">
            <span>Platform Status:</span>
            <span class="inline-flex items-center gap-1.5 text-emerald-400">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                Control Nodes Healthy
            </span>
        </div>
    </div>

    <div class="pt-2 flex items-center justify-center gap-3">
        <button onclick="window.location.reload()"
                type="button"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-bold text-xs transition-colors shadow-lg cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Check Status &amp; Reload</span>
        </button>

        <a href="{{ route('health') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 font-semibold text-xs border border-white/10 transition-colors">
            Telemetry Probes
        </a>
    </div>
</div>
@endsection
