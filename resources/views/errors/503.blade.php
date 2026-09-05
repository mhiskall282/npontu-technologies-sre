@extends('errors.layout')

@section('title', '503 — Maintenance Mode')

@section('content')
<div class="space-y-3 sm:space-y-5 my-auto py-1">
    <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full bg-amber-500/10 border border-[#F5C518]/30 text-[#F5C518] text-[10px] sm:text-xs font-mono font-bold tracking-wider uppercase">
        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-[#F5C518] animate-ping"></span>
        <span>503 &bull; MAINTENANCE WINDOW</span>
    </div>

    <div>
        <div class="w-11 h-11 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-amber-500/20 to-amber-600/10 border border-amber-500/30 flex items-center justify-center mx-auto mb-2 sm:mb-4 shadow-inner">
            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        <h1 class="text-xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">Scheduled SRE Maintenance</h1>
        <p class="text-xs sm:text-sm text-gray-400 mt-1 sm:mt-2 max-w-sm sm:max-w-md mx-auto leading-relaxed">
            The Support Tracker platform is currently undergoing scheduled infrastructure upgrades. Services will resume shortly.
        </p>
    </div>

    <div class="pt-1 sm:pt-2">
        <button onclick="window.location.reload()"
                type="button"
                class="inline-flex items-center gap-2 px-5 py-2 sm:px-6 sm:py-2.5 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-bold text-xs transition-colors shadow-lg cursor-pointer">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Check Platform Status</span>
        </button>
    </div>
</div>
@endsection
