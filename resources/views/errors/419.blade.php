@extends('errors.layout')

@section('title', '419 — Session Expired')

@section('content')
<div class="space-y-3 sm:space-y-5 my-auto py-1">
    {{-- Status Code Badge --}}
    <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full bg-amber-500/10 border border-[#F5C518]/30 text-[#F5C518] text-[10px] sm:text-xs font-mono font-bold tracking-wider uppercase">
        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-[#F5C518] animate-ping"></span>
        <span>419 &bull; SESSION TIMEOUT</span>
    </div>

    {{-- Icon & Heading --}}
    <div>
        <div class="w-11 h-11 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-amber-500/20 to-amber-600/10 border border-amber-500/30 flex items-center justify-center mx-auto mb-2 sm:mb-4 shadow-inner">
            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">Operational Session Expired</h1>
        <p class="text-xs sm:text-sm text-gray-400 mt-1 sm:mt-2 max-w-sm sm:max-w-md mx-auto leading-relaxed">
            Your active shift session or security token has timed out due to inactivity. This safeguard protects the SRE operational console against unauthorized custody takeover.
        </p>
    </div>

    {{-- Diagnostics Snapshot --}}
    <div class="p-3 sm:p-4 rounded-xl bg-white/5 border border-white/10 text-left max-w-sm sm:max-w-md mx-auto">
        <div class="flex items-center justify-between text-[10px] sm:text-[11px] font-mono text-gray-400 mb-1">
            <span>AUDIT CODE: SRE_CSRF_EXPIRATION</span>
            <span class="text-amber-400 font-bold">ACTION REQUIRED</span>
        </div>
        <p class="text-[11px] sm:text-xs text-gray-300">
            Please sign in again to refresh your session credentials and continue your shift activities.
        </p>
    </div>

    {{-- Action CTAs --}}
    <div class="flex flex-row items-center justify-center gap-2 sm:gap-3 pt-1 sm:pt-2">
        <a href="{{ route('login') }}"
           class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-4 sm:px-6 py-2 sm:py-2.5 rounded-xl bg-[#F5C518] hover:bg-amber-400 text-gray-950 font-bold text-xs transition-colors shadow-lg">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            <span>Sign In to Resume Shift</span>
        </a>
        <button onclick="window.location.reload()"
                type="button"
                class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white font-semibold text-xs border border-white/10 transition-colors cursor-pointer">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Refresh Page</span>
        </button>
    </div>
</div>
@endsection
