@extends('errors.layout')

@section('title', '500 — System Error')

@section('content')
<div class="space-y-6">
    {{-- Status Code Badge --}}
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-mono font-bold tracking-wider uppercase">
        <span class="w-2 h-2 rounded-full bg-[#E63946] animate-ping"></span>
        <span>500 &bull; SRE RUNTIME EXCEPTION</span>
    </div>

    {{-- Icon & Heading --}}
    <div>
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-red-500/20 to-red-600/10 border border-red-500/30 flex items-center justify-center mx-auto mb-4 shadow-inner">
            <svg class="w-8 h-8 text-[#E63946]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Internal Operational Exception</h1>
        <p class="text-sm text-gray-400 mt-2 max-w-md mx-auto leading-relaxed">
            An unhandled runtime error occurred during request execution. The incident has been recorded in the platform security logs.
        </p>
    </div>

    <div class="p-4 rounded-xl bg-white/5 border border-white/10 text-left max-w-md mx-auto font-mono text-xs text-gray-300">
        <div class="flex items-center justify-between text-gray-400 mb-1">
            <span>INCIDENT REF: INC-{{ date('Ymd') }}-{{ substr(md5((string) time()), 0, 6) }}</span>
            <span class="text-emerald-400">LOGGED</span>
        </div>
        <p class="text-[11px] text-gray-400">Our engineering leads have received automated telemetry alerts.</p>
    </div>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
        <button onclick="window.location.reload()"
                type="button"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold text-xs transition-colors shadow-lg cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Retry Operation</span>
        </button>
        <a href="{{ route('health') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white font-semibold text-xs border border-white/10 transition-colors">
            <span>Inspect Health HUD</span>
        </a>
    </div>
</div>
@endsection
