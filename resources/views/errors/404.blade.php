@extends('errors.layout')

@section('title', '404 — Page Not Found')

@section('content')
<div class="space-y-3 sm:space-y-5 my-auto py-1">
    {{-- Status Code Badge --}}
    <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[10px] sm:text-xs font-mono font-bold tracking-wider uppercase">
        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-400"></span>
        <span>404 &bull; ROUTE NOT FOUND</span>
    </div>

    {{-- Icon & Heading --}}
    <div>
        <div class="w-11 h-11 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-gradient-to-br from-emerald-500/20 to-emerald-600/10 border border-emerald-500/30 flex items-center justify-center mx-auto mb-2 sm:mb-4 shadow-inner">
            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">Operational Resource Not Found</h1>
        <p class="text-xs sm:text-sm text-gray-400 mt-1 sm:mt-2 max-w-sm sm:max-w-md mx-auto leading-relaxed">
            The requested checklist item, report URL, or operational endpoint could not be located in the active SRE registry.
        </p>
    </div>

    {{-- Quick Destinations --}}
    <div class="grid grid-cols-3 gap-2 text-center max-w-md mx-auto">
        <a href="{{ route('activities.daily') }}" class="p-2 sm:p-3 rounded-xl bg-white/5 border border-white/10 hover:border-emerald-500/40 hover:bg-white/10 transition-colors group">
            <p class="text-[11px] sm:text-xs font-bold text-white group-hover:text-emerald-400 truncate">Today's Board</p>
            <p class="hidden sm:block text-[10px] text-gray-400 mt-0.5">Active shift checklist</p>
        </a>
        <a href="{{ route('messages.index') }}" class="p-2 sm:p-3 rounded-xl bg-white/5 border border-white/10 hover:border-emerald-500/40 hover:bg-white/10 transition-colors group">
            <p class="text-[11px] sm:text-xs font-bold text-white group-hover:text-emerald-400 truncate">Ops Comms</p>
            <p class="hidden sm:block text-[10px] text-gray-400 mt-0.5">Team shift chat</p>
        </a>
        <a href="{{ route('health') }}" class="p-2 sm:p-3 rounded-xl bg-white/5 border border-white/10 hover:border-emerald-500/40 hover:bg-white/10 transition-colors group">
            <p class="text-[11px] sm:text-xs font-bold text-white group-hover:text-emerald-400 truncate">System Health</p>
            <p class="hidden sm:block text-[10px] text-gray-400 mt-0.5">Telemetry & status</p>
        </a>
    </div>

    {{-- Primary CTA --}}
    <div class="pt-1 sm:pt-2">
        <a href="{{ route('activities.daily') }}"
           class="inline-flex items-center gap-2 px-5 py-2 sm:px-6 sm:py-2.5 rounded-xl bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold text-xs transition-colors shadow-lg">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Return to Safety (Today's Board)</span>
        </a>
    </div>
</div>
@endsection
